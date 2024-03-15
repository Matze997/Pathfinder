<?php

declare(strict_types=1);

namespace matze\pathfinder\rule\default;

use matze\pathfinder\world\FictionalWorld;
use pocketmine\block\Block;
use pocketmine\math\Vector3;

/**
 * Useful for mobs like bees
 */
class NeedsAirRule extends EntitySizeRule {
    protected function isSolid(Block $block): bool{
        return !$block->canBeReplaced();
    }

    protected function hasBlockBelow(Vector3 $center, FictionalWorld $world): bool{
        return true;
    }
}