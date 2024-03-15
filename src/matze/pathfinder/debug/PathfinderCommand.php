<?php

declare(strict_types=1);

namespace matze\pathfinder\debug;

use matze\pathfinder\Pathfinder;
use matze\pathfinder\result\PathResult;
use matze\pathfinder\rule\default\ChickenRule;
use matze\pathfinder\setting\Settings;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\world\World;

class PathfinderCommand extends Command {
    private Vector3 $first;
    private Vector3 $second;

    private Pathfinder $pathfinder;

    public function __construct(
        private Plugin $plugin,
    ){
        parent::__construct("pathfinder", "Pathfinder Command");
        $this->setPermission("command.pathfinder.use");
        $this->pathfinder = new Pathfinder([
            new ChickenRule(),
        ], Settings::get()->setPathSmoothing(false));
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void{
        if(!$sender instanceof Player || !$this->testPermission($sender)) {
            return;
        }
        switch($args[0] ?? "") {
            case "1": {
                $this->first = $sender->getLocation()->floor();
                $sender->sendMessage("Set first position.");
                break;
            }
            case "2": {
                $this->second = $sender->getLocation()->floor();
                $sender->sendMessage("Set second position.");
                break;
            }
            case "swap": {
                $first = clone $this->first;
                $this->first = clone $this->second;
                $this->second = $first;
                $sender->sendMessage("Swapped two positions.");
                break;
            }
            case "runsync": {
                if(!isset($this->first, $this->second)) {
                    $sender->sendMessage("You first have to set two positions!");
                    return;
                }
                $ms = microtime(true);
                $result = $this->pathfinder->findPath($this->first, $this->second, $sender->getWorld(), (float)($args[1] ?? 0.2));
                if($result === null) {
                    $sender->sendMessage("Path not found.");
                    return;
                }
                $this->visualizePath($sender->getWorld(), $result);
                $sender->sendMessage("Path found! Took ".round(microtime(true) - $ms, 5)." seconds.");
                break;
            }
            case "runasync": {
                if(!isset($this->first, $this->second)) {
                    $sender->sendMessage("You first have to set two positions!");
                    return;
                }
                $ms = microtime(true);
                $this->pathfinder->findPathAsync($this->first, $this->second, $sender->getWorld(), function(?PathResult $result) use ($sender, $ms): void {
                    if(!$sender->isConnected()) {
                        return;
                    }
                    if($result === null) {
                        $sender->sendMessage("Path not found.");
                        return;
                    }
                    $this->visualizePath($sender->getWorld(), $result);
                    $sender->sendMessage("Path found! Took ".round(microtime(true) - $ms, 5)." seconds.");
                }, (float)($args[1] ?? 0.2), 64);
                break;
            }
            default: {
                $sender->sendMessage("Pathfinder Command Help:");
                $sender->sendMessage("/pathfinder 1");
                $sender->sendMessage("/pathfinder 2");
                $sender->sendMessage("/pathfinder swap");
                $sender->sendMessage("/pathfinder runsync [timeout=0.2]");
                $sender->sendMessage("/pathfinder runasync [timeout=0.2]");
            }
        }
    }

    private function visualizePath(World $world, PathResult $result): void {
        $id = 0;
        foreach($result->getNodes() as $node) {
            $entity = (new class(Location::fromObject($node, $world, 0, 0)) extends Entity {
                protected function getInitialSizeInfo(): EntitySizeInfo{
                    return new EntitySizeInfo(0.1, 0.1);
                }

                protected function getInitialDragMultiplier(): float{
                    return 0.0;
                }

                protected function getInitialGravity(): float{
                    return 0.0;
                }

                public static function getNetworkTypeId(): string{
                    return EntityIds::PIG;
                }

                public function onUpdate(int $currentTick): bool{
                    if($this->ticksLived > 400) {
                        $this->flagForDespawn();
                        return true;
                    }
                    parent::onUpdate($currentTick);
                    return true;
                }
            });
            $entity->setNameTagVisible();
            $entity->setNameTagAlwaysVisible();
            $entity->setNameTag("§r§6§l".$id++);
            $entity->setCanSaveWithChunk(false);
            $entity->setScale(0.0000000001);
            $entity->spawnToAll();
        }
    }
}