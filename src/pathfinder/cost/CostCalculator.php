<?php

declare(strict_types=1);

namespace pathfinder\cost;

class CostCalculator {
    private const BLOCK_COSTS = [
        288 => 20,//Oak Leaves
        2580 => 20,//Acacia Leaves
        293 => 20,//Spruce Leaves
        294 => 20,//Birch Leaves
        295 => 20,//Jungle Leaves
        2581 => 20,//Dark Oak Leaves

        1856 => 10,//Enchantment Table
        7331 => 5,//Barrel
        752 => 5,//Bookshelf
        2323 => 10,//Anvil
        928 => 5,//Workbench

        144 => 100,//Water
    ];

    public static function getCost(int $fullId): int {
        return self::BLOCK_COSTS[$fullId] ?? 1;
    }
}