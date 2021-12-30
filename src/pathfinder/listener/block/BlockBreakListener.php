<?php

declare(strict_types=1);

namespace pathfinder\listener\block;

use pathfinder\queue\ValidatorQueue;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\world\format\Chunk;

class BlockBreakListener implements Listener {
    /**
     * @priority MONITOR
     */
    public function onBlockPlace(BlockBreakEvent $event): void {
        $block = $event->getBlock();
        $position = $block->getPosition();
        $world = $position->getWorld();
        ValidatorQueue::queueChunkXZValidation($position->getX() >> 4, $position->getZ() >> 4, $world, $position->getFloorX() & Chunk::COORD_MASK, $position->getFloorZ() & Chunk::COORD_MASK);
    }
}