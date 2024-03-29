<?php

declare(strict_types=1);

namespace matze\pathfinder\rule\default;

use matze\pathfinder\world\FictionalWorld;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Vector3;

/**
 * Useful for mobs like fish
 */
class NeedsWaterRule extends EntitySizeRule {
    protected function isPassable(Block $block): bool{
        return $block->isSameState(VanillaBlocks::WATER());
    }

    protected function isSolid(Block $block): bool{
        return !$this->isPassable($block);
    }

    protected function hasBlockBelow(Vector3 $center, FictionalWorld $world): bool{
        return true;
    }
}