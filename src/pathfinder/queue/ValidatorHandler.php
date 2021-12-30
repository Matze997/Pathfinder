<?php

declare(strict_types=1);

namespace pathfinder\queue;

use pathfinder\pathpoint\PathPointManager;
use pathfinder\validator\ChunkValidator;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\world\format\io\FastChunkSerializer;
use pocketmine\world\World;

class ValidatorHandler {
    public static function handleChunkEntry(array $entry): void {
        $world = Server::getInstance()->getWorldManager()->getWorldByName($entry["world"]);
        if($world === null) return;
        World::getXZ($entry["chunk"], $chunkX, $chunkZ);
        $chunk = $world->getChunk($chunkX, $chunkZ);
        if($chunk === null) return;

        $chunk = FastChunkSerializer::serializeTerrain($chunk);
        Server::getInstance()->getAsyncPool()->submitTask(
            new class($chunk, $chunkX, $chunkZ, $world->getFolderName()) extends AsyncTask {
                private string $chunk;
                private int $chunkX;
                private int $chunkZ;
                private string $world;

                public function __construct(string $chunk, int $chunkX, int $chunkZ, string $world){
                    $this->chunk = $chunk;
                    $this->chunkX = $chunkX;
                    $this->chunkZ = $chunkZ;
                    $this->world = $world;
                }

                public function onRun(): void{
                    $this->setResult(ChunkValidator::validateChunk(FastChunkSerializer::deserializeTerrain($this->chunk), $this->chunkX, $this->chunkZ));
                }

                public function onCompletion(): void{
                    $world = Server::getInstance()->getWorldManager()->getWorldByName($this->world);
                    if($world === null) return;
                    $pathPoints = $this->getResult();
                    PathPointManager::removePathPointsByChunk($world, $this->chunkX, $this->chunkZ);
                    foreach($pathPoints as $pathPoint) PathPointManager::insertPathPoint($world, $pathPoint);
                }
            }
        );
    }

    public static function handleXZEntry(array $entry): void {
        $world = Server::getInstance()->getWorldManager()->getWorldByName($entry["world"]);
        if($world === null) return;
        World::getXZ($entry["chunk"], $chunkX, $chunkZ);
        $chunk = $world->getChunk($chunkX, $chunkZ);
        if($chunk === null) return;

        $pathPoints = ChunkValidator::validateChunkXZ($chunk, $chunkX, $chunkZ, $entry["x"], $entry["z"]);
        PathPointManager::removePathPointsByXZ($world, $chunkX, $chunkZ, $entry["x"], $entry["z"]);
        foreach($pathPoints as $pathPoint){
            PathPointManager::insertPathPoint($world, $pathPoint);
        }
    }
}