<?php

declare(strict_types=1);

namespace matze\pathfinder\task;

use matze\pathfinder\AsyncChunkPathfinder;
use matze\pathfinder\setting\Settings;
use pmmp\thread\ThreadSafeArray;
use pocketmine\math\Vector3;
use pocketmine\math\VoxelRayTrace;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\world\format\io\FastChunkSerializer;
use pocketmine\world\World;

class AsyncPathfinderTask extends AsyncTask {
    public string $world;

    private int $start;
    private int $target;

    private string $settings;

    public ?string $chunk;

    private ThreadSafeArray $chunks;

    public function __construct(
        Vector3 $startVector,
        Vector3 $targetVector,
        Settings $settings,
        World $world,
        $onCompletion,
        protected int $chunkCacheLimit
    ){
        $this->world = $world->getFolderName();
        $this->settings = igbinary_serialize($settings);
        $this->start = World::blockHash($startVector->getFloorX(), $startVector->getFloorY(), $startVector->getFloorZ());
        $this->target = World::blockHash($targetVector->getFloorX(), $targetVector->getFloorY(), $targetVector->getFloorZ());
        $this->storeLocal("onCompletion", $onCompletion);

        $this->chunks = new ThreadSafeArray();
        foreach (VoxelRayTrace::betweenPoints($startVector, $targetVector) as $point) {
            $chunkX = $point->getFloorX() >> 4;
            $chunkZ = $point->getFloorZ() >> 4;
            $hash = World::chunkHash($chunkX, $chunkZ);
            if(isset($this->chunks[$hash])) {
                continue;
            }
            $chunk = $world->getChunk($chunkX, $chunkZ);
            if($chunk === null) {
                continue;
            }
            $this->chunks[$hash] = FastChunkSerializer::serializeTerrain($chunk);
            if(count($this->chunks) >= $this->chunkCacheLimit) {
                break;
            }
        }
    }

    public function onRun(): void{
        $settings = igbinary_unserialize($this->settings);

        World::getBlockXYZ($this->start, $startX, $startY, $startZ);
        World::getBlockXYZ($this->target, $targetX, $targetY, $targetZ);

        $pathfinder = new AsyncChunkPathfinder($settings, $this, $this->chunkCacheLimit);
        foreach ($this->chunks as $hash => $chunk) {
            World::getXZ($hash, $chunkX, $chunkZ);
            $pathfinder->addChunk($chunkX, $chunkZ, FastChunkSerializer::deserializeTerrain($chunk));
        }
        $this->setResult($pathfinder->findPath(new Vector3($startX, $startY, $startZ), new Vector3($targetX, $targetY, $targetZ)));
    }

    public function onCompletion() : void{
        $onCompletion = $this->fetchLocal("onCompletion");
        ($onCompletion)($this->getResult());
    }

    public function onProgressUpdate($progress): void{
        $world = Server::getInstance()->getWorldManager()->getWorldByName($this->world);
        if($world === null) {
            $this->chunk = "";
            return;
        }
        World::getXZ($progress, $chunkX, $chunkZ);
        $chunk = $world->getChunk($chunkX, $chunkZ);
        if($chunk === null) {
            $this->chunk = "";
            return;
        }
        $this->chunk = FastChunkSerializer::serializeTerrain($chunk);
    }
}