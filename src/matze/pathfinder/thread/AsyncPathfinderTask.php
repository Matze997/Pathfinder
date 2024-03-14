<?php

declare(strict_types=1);

namespace matze\pathfinder\thread;

use Closure;
use matze\pathfinder\BasePathfinder;
use matze\pathfinder\world\AsyncFictionalWorld;
use pmmp\thread\ThreadSafeArray;
use pocketmine\math\Vector3;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\world\format\io\FastChunkSerializer;
use pocketmine\world\World;

class AsyncPathfinderTask extends AsyncTask {
    public ?string $missingChunkResult = null;

    public function __construct(
        private int $start,
        private int $target,
        private string $world,
        private float $timeout,
        private string $settings,
        private ThreadSafeArray $rules,
        private int $chunkCacheLimit,
        Closure $onCompletion,
    ){
        $this->storeLocal("onCompletion", $onCompletion);
    }

    public function onRun(): void{
        $rules = [];
        foreach($this->rules as $value) {
            $rules[] = igbinary_unserialize($value);
        }
        $pathfinder = new BasePathfinder(new AsyncFictionalWorld($this->world, $this, $this->chunkCacheLimit), igbinary_unserialize($this->settings), $this->timeout, $rules);
        World::getBlockXYZ($this->start, $startX, $startY, $startZ);
        World::getBlockXYZ($this->target, $targetX, $targetY, $targetZ);
        $this->setResult($pathfinder->findPath(new Vector3($startX, $startY, $startZ), new Vector3($targetX, $targetY, $targetZ)));
    }

    public function onProgressUpdate($progress): void{
        $world = Server::getInstance()->getWorldManager()->getWorldByName($this->world);
        if($world === null) {
            $this->missingChunkResult = "";
        } else {
            World::getXZ($progress, $chunkX, $chunkZ);
            $chunk = $world->getChunk($chunkX, $chunkZ);
            if($chunk === null) {
                $this->missingChunkResult = "";
            } else {
                $this->missingChunkResult = FastChunkSerializer::serializeTerrain($chunk);
            }
        }
    }

    public function onCompletion(): void{
        $closure = $this->fetchLocal("onCompletion");
        ($closure)($this->getResult());
    }
}