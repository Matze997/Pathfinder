<?php

declare(strict_types=1);

namespace pathfinder\validator;

use pathfinder\cost\CostCalculator;
use pathfinder\pathpoint\PathPoint;
use pocketmine\block\BaseRail;
use pocketmine\block\BlockFactory;
use pocketmine\block\Lava;
use pocketmine\world\format\Chunk;
use function array_merge;

class ChunkValidator {
    /**
     * @return PathPoint[]
     */
    public static function validateChunk(Chunk $chunk, int $chunkX, int $chunkZ): array {
        $pathPoints = [];
        for($x = 0; $x <= 15; $x++) {
            for($z = 0; $z <= 15; $z++) {
                $pathPoints = array_merge($pathPoints, self::validateChunkXZ($chunk, $chunkX, $chunkZ, $x, $z));
            }
        }
        return $pathPoints;
    }

    /**
     * @return PathPoint[]
     */
    public static function validateChunkXZ(Chunk $chunk, int $chunkX, int $chunkZ, int $x, int $z): array {
        /** @var BlockFactory $blockFactory */
        $blockFactory = BlockFactory::getInstance();
        $pathPoints = [];
        $pathPoint = null;
        $waitUntilReset = false;
        foreach($chunk->getSubChunks() as $subChunkY => $subChunk) {
            for($y = Chunk::MIN_SUBCHUNK_INDEX; $y <= Chunk::MAX_SUBCHUNK_INDEX; $y++) {
                $block = $blockFactory->fromFullBlock($subChunk->getFullBlock($x, $y, $z));
                if(
                    $block->isSolid() ||
                    $block instanceof BaseRail ||
                    $block instanceof Lava
                ) {
                    if($pathPoint !== null) {
                        $pathPoints[] = $pathPoint;
                        $pathPoint = null;
                        $waitUntilReset = false;
                    }
                    continue;
                }
                if($waitUntilReset) continue;
                $pathPoint?->addHeight();
                if($pathPoint === null) {
                    if(($y + ($subChunkY * 16)) - 1 < 0){
                        $waitUntilReset = true;
                        continue;
                    }
                    $fullBlockUnder = $chunk->getFullBlock($x, ($y + ($subChunkY * 16)) - 1, $z);
                    $blockUnder = $blockFactory->fromFullBlock($fullBlockUnder);
                    $cost = CostCalculator::getCost($fullBlockUnder);
                    if($cost === -1 || !$blockUnder->isSolid()){
                        $waitUntilReset = true;
                        continue;
                    }
                    $pathPoint = new PathPoint($x + ($chunkX * 16) + 0.5, $y + ($subChunkY * 16), $z + ($chunkZ * 16) + 0.5, 0, $cost);
                }
            }
        }

        if($pathPoint !== null) {
            $pathPoints[] = $pathPoint;
            $pathPoint = null;
        }
        return $pathPoints;
    }
}