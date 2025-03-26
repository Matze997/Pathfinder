<?php

namespace matze\pathfinder\setting\distance;

use pocketmine\math\Vector3;

class HeuristicDistanceCalculator extends DistanceCalculator {
    /**
     * Credits to https://github.com/inxomnyaa
     */
    public function calculateDistance(Vector3 $pos1, Vector3 $pos2): float {
        $dx = abs($pos1->getX() - $pos2->getX());
        // If your entities can move vertically (e.g., climb blocks, jump, or fly), include dy in the heuristic to account for vertical movement costs.
        $dy = abs($pos1->getY() - $pos2->getY());
        $dz = abs($pos1->getZ() - $pos2->getZ());

        // Octile distance for X and Z axes
        $minD = min($dx, $dz);
        $maxD = max($dx, $dz);
        $octileDistance = ($minD * sqrt(2)) + ($maxD - $minD);

        // Add vertical distance (Y-axis) as a separate cost
        return $octileDistance + $dy;
    }
}