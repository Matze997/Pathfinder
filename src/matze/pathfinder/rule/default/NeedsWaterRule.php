<?php

declare(strict_types=1);

namespace matze\pathfinder\rule\default;

use matze\pathfinder\rule\Rule;
use matze\pathfinder\world\FictionalWorld;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Vector3;

class NeedsWaterRule extends Rule {
    public function couldStandAt(Vector3 $node, FictionalWorld $world, int &$cost): bool{
        return $world->getBlock($node)->isSameState(VanillaBlocks::WATER());
    }
}