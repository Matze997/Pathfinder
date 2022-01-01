<?php

declare(strict_types=1);

namespace pathfinder\navigator\handler;

use pathfinder\navigator\Navigator;
use pathfinder\pathpoint\PathPoint;

abstract class MovementHandler {
    protected float $gravity = 0.08;

    public function getGravity(): float{
        return $this->gravity;
    }

    public function setGravity(float $gravity): void{
        $this->gravity = $gravity;
    }

    abstract public function handle(Navigator $navigator, PathPoint $pathPoint): void;
}