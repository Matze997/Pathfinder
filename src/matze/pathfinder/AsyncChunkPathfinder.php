<?php

declare(strict_types=1);

namespace matze\pathfinder;

use matze\pathfinder\setting\Settings;
use matze\pathfinder\task\AsyncPathfinderTask;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\io\FastChunkSerializer;
use pocketmine\world\World;

class AsyncChunkPathfinder extends ChunkPathfinder{
    /** @var Chunk[]  */
    protected array $chunks = [];

    public function __construct(
        Settings $settings,
        protected AsyncPathfinderTask $task,
        protected int $chunkCacheLimit
    ){
        parent::__construct($settings);
    }

    public function addChunk(int $chunkX, int $chunkZ, Chunk $chunk): void {
        $this->chunks[World::chunkHash($chunkX, $chunkZ)] = $chunk;
    }

    protected function getChunk(int $x, int $z): ?Chunk{
        $hash = World::chunkHash($x, $z);
        if(!isset($this->chunks[$hash])) {
            $this->task->publishProgress($hash);
            while (!isset($this->task->chunk)) {
                if($this->task->isTerminated()) {
                    return null;
                }
            }
            if($this->task->chunk === "") {
                return null;
            }
            $this->chunks[$hash] = FastChunkSerializer::deserializeTerrain($this->task->chunk);
            if(count($this->chunks) > $this->chunkCacheLimit) {
                array_shift($this->chunks);
            }
            unset($this->task->chunk);
        }
        return $this->chunks[$hash];
    }
}