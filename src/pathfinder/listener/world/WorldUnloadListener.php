<?php

declare(strict_types=1);

namespace pathfinder\listener\world;

use pathfinder\pathpoint\PathPointManager;
use pocketmine\event\Listener;
use pocketmine\event\world\WorldUnloadEvent;

class WorldUnloadListener implements Listener {
    public function onWorldUnload(WorldUnloadEvent $event): void {
        PathPointManager::removePathPointsByWorld($event->getWorld());
    }
}