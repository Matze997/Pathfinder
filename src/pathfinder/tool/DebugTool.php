<?php

declare(strict_types=1);

namespace pathfinder\tool;

use pathfinder\Pathfinder;
use pathfinder\pathpoint\PathPointManager;
use pathfinder\queue\ValidatorQueue;
use pocketmine\block\VanillaBlocks;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use function count;
use function implode;

class DebugTool {
    public static function start(): void {
        Pathfinder::$instance->getScheduler()->scheduleRepeatingTask(new class() extends Task {
            public function onRun(): void{
                foreach(Server::getInstance()->getOnlinePlayers() as $player) {
                    $block = $player->getTargetBlock(5);
                    if($block === null) $block = VanillaBlocks::AIR();
                    $player->sendTip("§r§a".implode(" | ", [
                            "§r§a".$block->getFullId(),
                            count(ValidatorQueue::getEntries(ValidatorQueue::TYPE_CHUNK)),
                            count(ValidatorQueue::getEntries(ValidatorQueue::TYPE_XZ)),
                            PathPointManager::getPathPointByPosition($player->getPosition())?->isCollisionFreeToStand($player->getWorld(), $player->getBoundingBox()) ?? "N/A",
                            Server::getInstance()->getTickUsageAverage()."%"
                        ]));
                }
            }
        }, 1);
    }
}