<?php

namespace matze\pathfinder\setting\distance;

use pocketmine\math\Vector3;

abstract class DistanceCalculator {
    abstract public function calculateDistance(Vector3 $pos1, Vector3 $pos2): float;
}