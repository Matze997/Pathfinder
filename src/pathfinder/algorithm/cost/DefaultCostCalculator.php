<?php

declare(strict_types=1);

namespace pathfinder\algorithm\cost;

use pocketmine\block\VanillaBlocks;

class DefaultCostCalculator extends CostCalculator {
    public function registerBlocks(): void{
        //Register all blocks
        $this->register(VanillaBlocks::WATER(), 10000);
        $this->register(VanillaBlocks::OAK_LEAVES(), 20);
        $this->register(VanillaBlocks::ENCHANTING_TABLE(), 10, false);
        $this->register(VanillaBlocks::BARREL(), 5);
        $this->register(VanillaBlocks::BOOKSHELF(), 3, false);
        $this->register(VanillaBlocks::ANVIL(), 20);
        $this->register(VanillaBlocks::CRAFTING_TABLE(), 5);
    }
}