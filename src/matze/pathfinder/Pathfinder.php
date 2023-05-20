<?php

declare(strict_types=1);

namespace matze\pathfinder;

use matze\pathfinder\result\PathResult;
use matze\pathfinder\setting\Settings;
use matze\pathfinder\type\AsyncPathfinder;
use matze\pathfinder\type\SyncPathfinder;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

class Pathfinder extends PluginBase{
    private static self $instance;

    protected function onEnable(): void{
        self::$instance = $this;
    }

    public static function getInstance(): Pathfinder{
        return self::$instance;
    }

    private Vector3 $firstVector;
    private Vector3 $secondVector;

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
        if($command->getName() === "pathfinder" && $command->testPermission($sender) && $sender instanceof Player) {
            switch ($args[0] ?? "") {
                case "1": {
                    $this->firstVector = $sender->getLocation();
                    $sender->sendMessage("First position set");
                    break;
                }
                case "2": {
                    $this->secondVector = $sender->getLocation();
                    $sender->sendMessage("Second position set");
                    break;
                }
                case "async": {
                    if(!isset($this->firstVector)) {
                        $sender->sendMessage("No start position given.");
                        break;
                    }
                    if(!isset($this->secondVector)) {
                        $sender->sendMessage("No target position given.");
                        break;
                    }
                    $sender->sendMessage("Searching path...");

                    $settings = Settings::get()->setJumpHeight((int)($args[1] ?? 1))->setFallDistance((int)($args[2] ?? 2))->setTimeout((float)($args[3] ?? 0.05));

                    $pathfinder = new AsyncPathfinder($settings, $sender->getWorld());
                    $hrtime = hrtime(true);
                    $pathfinder->findPath($this->firstVector, $this->secondVector, function (?PathResult $result) use ($sender, $hrtime): void {
                        $took = hrtime(true) - $hrtime;
                        if($result === null) {
                            $sender->sendMessage("Could not find path.");
                        } else {
                            $color = DyeColor::getAll()[array_rand(DyeColor::getAll())];
                            foreach ($result->getNodes() as $node) {
                                $sender->getWorld()->setBlock($node->subtract(0, 1, 0), VanillaBlocks::CONCRETE()->setColor($color));
                            }
                            $sender->sendMessage("Done!");
                        }
                        $sender->sendMessage("Took ".round($took * 0.000000001, 6)." seconds.");
                    });
                    break;
                }
                case "sync": {
                    if(!isset($this->firstVector)) {
                        $sender->sendMessage("No start position given.");
                        break;
                    }
                    if(!isset($this->secondVector)) {
                        $sender->sendMessage("No target position given.");
                        break;
                    }
                    $sender->sendMessage("Searching path...");

                    $settings = Settings::get()->setJumpHeight((int)($args[1] ?? 1))->setFallDistance((int)($args[2] ?? 2))->setTimeout((float)($args[3] ?? 0.05));

                    $pathfinder = new SyncPathfinder($settings, $sender->getWorld());
                    $hrtime = hrtime(true);
                    $result = $pathfinder->findPath($this->firstVector, $this->secondVector);
                    $took = hrtime(true) - $hrtime;
                    if($result === null) {
                        $sender->sendMessage("Could not find path.");
                    } else {
                        $color = DyeColor::getAll()[array_rand(DyeColor::getAll())];
                        foreach ($result->getNodes() as $node) {
                            $sender->getWorld()->setBlock($node->subtract(0, 1, 0), VanillaBlocks::CONCRETE()->setColor($color));
                        }
                        $sender->sendMessage("Done!");
                    }
                    $sender->sendMessage("Took ".round($took * 0.000000001, 6)." seconds.");
                    break;
                }
            }
            return true;
        }
        return false;
    }
}