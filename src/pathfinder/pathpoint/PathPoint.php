<?php

declare(strict_types=1);

namespace pathfinder\pathpoint;

use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\world\World;
use function ceil;
use function count;

class PathPoint extends Vector3 {
    private int $height;
    private int $cost;

    public function __construct(float|int $x, float|int $y, float|int $z, int $height = 1, int $cost = 0){
        $this->height = $height;
        $this->cost = $cost;
        parent::__construct($x, $y, $z);
    }

    public function getHeight(): int{
        return $this->height;
    }

    public function setHeight(int $height): void{
        $this->height = $height;
    }

    public function addHeight(int $height = 1): void {
        $this->height += $height;
    }

    public function getCost(): int{
        return $this->cost;
    }

    public function isCollisionFreeToStand(World $world, AxisAlignedBB $axisAlignedBB): bool {
        if($this->getHeight() < ceil($axisAlignedBB->maxY - $axisAlignedBB->minY)) return false;
        return count($world->getCollisionBlocks($axisAlignedBB)) <= 0;
    }
}