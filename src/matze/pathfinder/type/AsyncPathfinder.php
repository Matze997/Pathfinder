<?php

declare(strict_types=1);

namespace matze\pathfinder\type;

use Closure;
use matze\pathfinder\IPathfinder;
use matze\pathfinder\setting\Settings;
use matze\pathfinder\task\AsyncPathfinderTask;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\world\World;

class AsyncPathfinder implements IPathfinder {
    public function __construct(
        protected Settings $settings,
        protected World $world,
        protected int $chunkCacheLimit = 40
    ){}

    public function findPath(Vector3 $startVector, Vector3 $targetVector, Closure $onCompletion): void {
        Server::getInstance()->getAsyncPool()->submitTask(new AsyncPathfinderTask($startVector->asVector3(), $targetVector->asVector3(), $this->settings, $this->world, $onCompletion, $this->chunkCacheLimit));
    }
}