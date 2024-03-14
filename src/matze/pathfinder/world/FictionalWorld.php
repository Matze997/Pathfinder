<?php

declare(strict_types=1);

namespace matze\pathfinder\world;

use pocketmine\block\Block;
use pocketmine\math\Vector3;

abstract class FictionalWorld {

    public function __construct(
        protected string $world,
    ){}

    abstract public function getBlockAt(int $x, int $y, int $z): Block;

    public function getBlock(Vector3 $vector3): Block {
        return $this->getBlockAt($vector3->getFloorX(), $vector3->getFloorY(), $vector3->getFloorZ());
    }
}