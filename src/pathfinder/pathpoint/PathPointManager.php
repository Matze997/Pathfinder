<?php

declare(strict_types=1);

namespace pathfinder\pathpoint;

use pocketmine\world\Position;
use pocketmine\world\World;
use function floor;

class PathPointManager {
    /** @var PathPoint[][][][]  */
    private static array $pathPoints = [];

    public static function insertPathPoint(World $world, PathPoint $pathPoint): void {
        self::$pathPoints[$world->getFolderName()][World::chunkHash($pathPoint->getFloorX() >> 4, $pathPoint->getFloorZ() >> 4)][$pathPoint->getFloorX().":".$pathPoint->getFloorZ()][$pathPoint->getFloorY()] = $pathPoint;
    }

    public static function getPathPointByPosition(Position $position): ?PathPoint {
        return self::$pathPoints[$position->getWorld()->getFolderName()][World::chunkHash($position->getFloorX() >> 4, $position->getFloorZ() >> 4)][$position->getFloorX().":".$position->getFloorZ()][$position->getFloorY()] ?? null;
    }

    public static function removePathPointsByXZ(World $world, int $chunkX, int $chunkZ, int $x, int $z): void {
        unset(self::$pathPoints[$world->getFolderName()][World::chunkHash($chunkX, $chunkZ)][floor($x).":".floor($z)]);
    }

    public static function removePathPointsByChunk(World $world, int $chunkX, int $chunkZ): void {
        unset(self::$pathPoints[$world->getFolderName()][World::chunkHash($chunkX, $chunkZ)]);
    }

    public static function removePathPointsByWorld(World $world): void {
        unset(self::$pathPoints[$world->getFolderName()]);
    }
}