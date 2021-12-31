<?php

declare(strict_types=1);

namespace pathfinder\cost;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\VanillaBlocks;
use function array_map;

class CostCalculator {
    private array $knownFullIds;

    public function __construct(){
        $this->knownFullIds = array_map(function(Block $block): int {
            return $block->getFullId();
        }, BlockFactory::getInstance()->getAllKnownStates());

        //Register all blocks
        $this->register(VanillaBlocks::WATER(), 1000);
        $this->register(VanillaBlocks::OAK_LEAVES(), 20);
        $this->register(VanillaBlocks::ENCHANTING_TABLE(), 10, false);
        $this->register(VanillaBlocks::BARREL(), 5);
        $this->register(VanillaBlocks::BOOKSHELF(), 3, false);
        $this->register(VanillaBlocks::ANVIL(), 20);
        $this->register(VanillaBlocks::CRAFTING_TABLE(), 5);

        //Safe disc space
        $this->knownFullIds = [];
    }

    private function register(Block $block, int $cost, bool $allStates = true): void {
        if($allStates) {
            for($meta = 0; $meta <= 15; $meta++) {
                $fullId = ($block->getId() << Block::INTERNAL_METADATA_BITS) | $meta;
                if(!isset($this->knownFullIds[$fullId])) continue;
                self::$BLOCK_COSTS[$fullId] = $cost;
            }
            return;
        }
        self::$BLOCK_COSTS[$block->getFullId()] = $cost;
    }

    private static array $BLOCK_COSTS = [];

    public static function getCost(Block $block): int {
        return self::$BLOCK_COSTS[$block->getFullId()] ?? 1;
    }
}