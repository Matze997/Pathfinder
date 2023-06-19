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
    private bool $running = false;

    public function __construct(
        protected Settings $settings,
        protected World $world,
        protected int $chunkCacheLimit = 40
    ){}

    public function findPath(Vector3 $startVector, Vector3 $targetVector, Closure $onCompletion): void {
        if($startVector->floor()->equals($targetVector->floor())) {
            ($onCompletion)(null);
            return;
        }
        $this->running = true;
        Server::getInstance()->getAsyncPool()->submitTask(new AsyncPathfinderTask($startVector->asVector3(), $targetVector->asVector3(), $this->settings, $this->world, function (mixed $result) use ($onCompletion): void {
            ($onCompletion)($result);
            $this->running = false;
        }, $this->chunkCacheLimit));
    }

    public function isRunning(): bool {
        return $this->running;
    }
}