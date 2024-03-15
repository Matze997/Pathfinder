<?php

declare(strict_types=1);

namespace matze\pathfinder\world;

use matze\pathfinder\thread\AsyncPathfinderTask;
use pocketmine\block\Block;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\io\FastChunkSerializer;
use pocketmine\world\World;

class AsyncFictionalWorld extends FictionalWorld {
    /** @var Chunk[]  */
    private array $chunks = [];

    public function __construct(
        string $world,
        protected AsyncPathfinderTask $task,
        protected int $chunkCacheLimit,
    ){
        parent::__construct($world);
    }

    public function getBlockAt(int $x, int $y, int $z): Block{
        $chunk = $this->getChunkAt($x, $z);
        if($chunk === null) {
            return VanillaBlocks::AIR();
        }
        return RuntimeBlockStateRegistry::getInstance()->fromStateId($chunk->getBlockStateId($x % 16, $y, $z % 16));
    }

    public function setChunk(int $chunkX, int $chunkZ, Chunk $chunk): void {
        $this->chunks[World::chunkHash($chunkX, $chunkZ)] = $chunk;
    }

    protected function getChunkAt(int $x, int $z): ?Chunk {
        $chunkX = $x >> 4;
        $chunkZ = $z >> 4;
        $hash = World::chunkHash($chunkX, $chunkZ);
        if(!array_key_exists($hash, $this->chunks)) {
            $this->task->publishProgress($hash);
            while(!isset($this->task->missingChunkResult)) {
                if($this->task->isTerminated()) {
                    return null;
                }
            }
            $chunk = $this->task->missingChunkResult;
            if($chunk === null) {
                $this->chunks[$hash] = null;
            } else {
                $this->chunks[$hash] = FastChunkSerializer::deserializeTerrain($chunk);
            }
            if(count($this->chunks) > $this->chunkCacheLimit) {
                array_shift($this->chunks);
            }
            unset($this->task->missingChunkResult);
        }
        return $this->chunks[$hash] ?? null;
    }
}