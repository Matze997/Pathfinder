<?php

declare(strict_types=1);

namespace matze\pathfinder\util;

use pocketmine\math\Vector2;
use pocketmine\math\Vector3;

class Utils {
    /**
     * @return Vector2[]
     */
    public static function getChunksBetween(Vector3 $v1, Vector3 $v2): array {
        $minX = min($v1->getFloorX() >> 4, $v2->getFloorX() >> 4) - 1;
        $maxX = max($v1->getFloorX() >> 4, $v2->getFloorX() >> 4) + 1;
        $minZ = min($v1->getFloorZ() >> 4, $v2->getFloorZ() >> 4) - 1;
        $maxZ = max($v1->getFloorZ() >> 4, $v2->getFloorZ() >> 4) + 1;

        $positions = [];
        for($xx = $minX; $xx <= $maxX; $xx++) {
            for($zz = $minZ; $zz <= $maxZ; $zz++) {
                $positions[] = new Vector2($xx, $zz);
            }
        }
        return $positions;
    }
}