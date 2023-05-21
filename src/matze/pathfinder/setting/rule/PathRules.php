<?php

declare(strict_types=1);

namespace matze\pathfinder\setting\rule;

use pocketmine\block\Block;

abstract class PathRules{
    /**
     * Returns if the block is safe to stand on
     */
    abstract public function isBlockSolid(Block $block): bool;

    /**
     * Returns if entities can walk through this block
     */
    abstract public function isBlockPassable(Block $block): bool;

    /**
     * Returns the cost of the block the entity will stand inside
     * The higher the value, the pathfinder will more likely avoid this block
     */
    abstract public function getCostInside(Block $block): int;

    /**
     * Returns the cost of the block the entity will stand on top
     * The higher the value, the pathfinder will more likely avoid this block
     */
    abstract public function getCostStanding(Block $block): int;
}