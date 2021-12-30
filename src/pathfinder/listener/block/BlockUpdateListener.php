<?php

declare(strict_types=1);

namespace pathfinder\listener\block;

use pathfinder\queue\ValidatorQueue;
use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\Listener;
use pocketmine\world\format\Chunk;

class BlockUpdateListener implements Listener {
    /**
     * @priority MONITOR
     */
    public function onBlockUpdate(BlockUpdateEvent $event): void {
        $block = $event->getBlock();
        $position = $block->getPosition();
        $world = $position->getWorld();
        ValidatorQueue::queueChunkXZValidation($position->getX() >> 4, $position->getZ() >> 4, $world, $position->getFloorX() & Chunk::COORD_MASK, $position->getFloorZ() & Chunk::COORD_MASK);
    }
}