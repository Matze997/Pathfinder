<?php

declare(strict_types=1);

namespace matze\pathfinder;

use pocketmine\block\Block;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\format\Chunk;

abstract class ChunkPathfinder extends BasePathfinder{
    abstract protected function getChunk(int $x, int $z): ?Chunk;

    protected function getBlockAt(int $x, int $y, int $z): Block{
        return RuntimeBlockStateRegistry::getInstance()->fromStateId($this->getChunk($x >> 4, $z >> 4)?->getBlockStateId($x % 16, $y, $z % 16) ?? VanillaBlocks::AIR()->getStateId());
    }
}