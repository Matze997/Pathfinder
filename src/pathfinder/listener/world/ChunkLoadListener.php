<?php

declare(strict_types=1);

namespace pathfinder\listener\world;

use pathfinder\queue\ValidatorQueue;
use pocketmine\event\Listener;
use pocketmine\event\world\ChunkLoadEvent;

class ChunkLoadListener implements Listener {
    public function onChunkLoad(ChunkLoadEvent $event): void {
        ValidatorQueue::queueChunkValidation($event->getChunkX(), $event->getChunkZ(), $event->getWorld());
    }
}