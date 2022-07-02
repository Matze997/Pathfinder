<?php

declare(strict_types=1);

namespace pathfinder\algorithm\validator;

use pathfinder\algorithm\Algorithm;
use pocketmine\block\BaseRail;
use pocketmine\block\Block;
use pocketmine\block\Liquid;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\math\Vector3;
use function ceil;

class DefaultValidator extends Validator {
    public function isSafeToStandAt(Algorithm $algorithm, Vector3 $vector3): bool{
        $world = $algorithm->getWorld();
        $block = $world->getBlock($vector3->subtract(0, 1, 0));
        if(!$block->isSolid() && !$block instanceof Slab && !$block instanceof Stair) return false;
        $axisAlignedBB = $algorithm->getAxisAlignedBB();
        $height = ceil($axisAlignedBB->maxY - $axisAlignedBB->minY);
        for($y = 0; $y <= $height; $y++) {
            if(!$this->isBlockEmpty($world->getBlock($vector3->add(0, $y, 0)))) return false;
        }
        return true;
    }

    protected function isBlockEmpty(Block $block): bool {
        return !$block->isSolid() && !$block instanceof BaseRail && !$block instanceof Liquid;
    }
}