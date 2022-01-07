<?php

declare(strict_types=1);

namespace pathfinder\pathresult;

use pathfinder\pathpoint\PathPoint;
use pocketmine\math\Vector3;
use pocketmine\world\World;

class PathResult {
    private World $world;

    private Vector3 $startVector3;
    private Vector3 $targetVector3;

    /** @var PathPoint[]  */
    private array $pathPoints = [];

    private float $time = 0.0;

    public function __construct(World $world, Vector3 $startVector3, Vector3 $targetVector3){
        $this->world = $world;
        $this->startVector3 = $startVector3;
        $this->targetVector3 = $targetVector3;
    }

    public function getWorld(): World{
        return $this->world;
    }

    public function getStartVector3(): Vector3{
        return $this->startVector3;
    }

    public function getTargetVector3(): Vector3{
        return $this->targetVector3;
    }

    public function getTime(): float{
        return $this->time;
    }

    public function setTime(float $time): void{
        $this->time = $time;
    }

    public function getPathPoints(): array{
        return $this->pathPoints;
    }

    public function addPathPoint(PathPoint $pathPoint): void {
        $this->pathPoints[] = $pathPoint;
    }

    public function getPathPoint(int $key): ?PathPoint {
        return $this->pathPoints[$key] ?? null;
    }
}