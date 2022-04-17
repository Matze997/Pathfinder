<?php

declare(strict_types=1);

namespace pathfinder\algorithm\validator;

use pathfinder\algorithm\Algorithm;
use pocketmine\math\Vector3;

abstract class Validator {
    abstract public function isSafeToStandAt(Algorithm $algorithm, Vector3 $vector3): bool;
}