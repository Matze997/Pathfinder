<?php

declare(strict_types=1);

namespace matze\pathfinder\rule\default;

use matze\pathfinder\rule\Rule;
use matze\pathfinder\setting\Settings;
use matze\pathfinder\world\FictionalWorld;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Vector3;

class ChickenRule extends Rule {
    public function couldWalkTo(Vector3 $currentNode, Vector3 $targetNode, FictionalWorld $world, Settings $settings, int &$cost): bool{
        if($this->couldStandAt($targetNode, $world, $cost)) {
            return true;
        }

        for($yy = 0; $yy <= $settings->getMaxTravelDistanceDown(); $yy++) {
            $down = $targetNode->down($yy);
            if(!$world->getBlock($down)->isSameState(VanillaBlocks::AIR())) {
                break;
            }
            if($this->couldStandAt($down, $world, $cost)) {
                $targetNode->y -= $yy;// We need to adjust the y coordinate here!
                return true;
            }
        }

        for($yy = 1; $yy <= $settings->getMaxTravelDistanceUp(); $yy++) {
            if(!$world->getBlock($currentNode->up($yy))->isSameState(VanillaBlocks::AIR())) {
                break;
            }
            if($this->couldStandAt($targetNode->up($yy), $world, $cost)) {
                $targetNode->y += $yy;// We need to adjust the y coordinate here!
                return true;
            }
        }

        return false;
    }

    public function couldStandAt(Vector3 $node, FictionalWorld $world, int &$cost): bool{
        return $world->getBlock($node)->isSameState(VanillaBlocks::AIR()) && $world->getBlock($node->down())->isFullCube();
    }
}