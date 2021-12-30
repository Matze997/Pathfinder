<?php

declare(strict_types=1);

namespace pathfinder\tool;

use pathfinder\Pathfinder;
use pocketmine\block\VanillaBlocks;
use pocketmine\scheduler\Task;
use pocketmine\Server;
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
                            Server::getInstance()->getTickUsageAverage()."%"
                        ]));
                }
            }
        }, 1);
    }
}