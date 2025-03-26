<?php

namespace matze\pathfinder\setting\distance;

use pocketmine\math\Vector3;

class ManhattenDistanceCalculator extends DistanceCalculator {
    public function calculateDistance(Vector3 $pos1, Vector3 $pos2): float {
        return abs($pos1->x - $pos2->x) + abs($pos1->y - $pos2->y) + abs($pos1->z - $pos2->z);
    }
}