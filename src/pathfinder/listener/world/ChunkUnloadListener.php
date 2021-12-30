<?php

declare(strict_types=1);

namespace pathfinder\listener\world;

use pathfinder\pathpoint\PathPointManager;
use pocketmine\event\Listener;
use pocketmine\event\world\ChunkUnloadEvent;

class ChunkUnloadListener implements Listener {
    public function onChunkUnload(ChunkUnloadEvent $event): void {
        PathPointManager::removePathPointsByChunk($event->getWorld(), $event->getChunkX(), $event->getChunkZ());
    }
}