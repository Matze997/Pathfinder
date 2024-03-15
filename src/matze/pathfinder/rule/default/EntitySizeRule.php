<?php

declare(strict_types=1);

namespace matze\pathfinder\rule\default;

use matze\pathfinder\rule\Rule;
use matze\pathfinder\setting\Settings;
use matze\pathfinder\world\FictionalWorld;
use pocketmine\block\Block;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\math\Vector3;

class EntitySizeRule extends Rule {
    protected int $halfWidth;
    protected int $height;

    public function __construct(
        EntitySizeInfo $size,
        int $priority = self::PRIORITY_HIGHEST
    ){
        parent::__construct($priority);
        $this->halfWidth = (int)round($size->getWidth() / 2, PHP_ROUND_HALF_DOWN);
        $this->height = (int)ceil($size->getHeight());
    }

    public function couldWalkTo(Vector3 $currentNode, Vector3 $targetNode, FictionalWorld $world, Settings $settings, int &$cost): bool{
        if($this->couldStandAt($targetNode, $world, $cost)) {
            return true;
        }

        for($yy = 0; $yy <= $settings->getMaxTravelDistanceDown(); $yy++) {
            $down = $targetNode->down($yy);
            if(!$this->isAreaClear($down, $world)) {
                break;
            }
            if($this->couldStandAt($down, $world, $cost)) {
                $targetNode->y -= $yy;
                return true;
            }
        }

        for($yy = 1; $yy <= $settings->getMaxTravelDistanceUp(); $yy++) {
            if(!$this->isAreaClear($currentNode->up($yy), $world)) {
                break;
            }
            if($this->couldStandAt($targetNode->up($yy), $world, $cost)) {
                $targetNode->y += $yy;
                return true;
            }
        }

        return false;
    }

    public function couldStandAt(Vector3 $node, FictionalWorld $world, int &$cost): bool{
        return $this->hasBlockBelow($node, $world) && $this->isAreaClear($node, $world);
    }

    protected function hasBlockBelow(Vector3 $center, FictionalWorld $world): bool {
        for($xx = -$this->halfWidth; $xx <= $this->halfWidth; $xx++) {
            for($zz = -$this->halfWidth; $zz <= $this->halfWidth; $zz++) {
                if($this->isSolid($world->getBlock($center->add($xx, -1, $zz)))) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function isAreaClear(Vector3 $center, FictionalWorld $world): bool {
        for($xx = -$this->halfWidth; $xx <= $this->halfWidth; $xx++) {
            for($zz = -$this->halfWidth; $zz <= $this->halfWidth; $zz++) {
                for($yy = 0; $yy <= $this->height; $yy++) {
                    if(!$this->isPassable($world->getBlock($center->add($xx, $yy, $zz)))) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    protected function isSolid(Block $block): bool {
        return $block->isFullCube();
    }

    protected function isPassable(Block $block): bool {
        return $block->canBeReplaced();
    }
}