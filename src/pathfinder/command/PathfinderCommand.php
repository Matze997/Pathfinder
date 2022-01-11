<?php

declare(strict_types=1);

namespace pathfinder\command;

use pathfinder\algorithm\AlgorithmSettings;
use pathfinder\algorithm\astar\AStar;
use pathfinder\entity\TestEntity;
use pathfinder\Pathfinder;
use pathfinder\pathresult\PathResult;
use pocketmine\block\Block;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\world\Position;
use function array_key_first;
use function floatval;

class PathfinderCommand extends Command {
    public function __construct(){
        parent::__construct("pathfinder");
        $this->setPermission("pathfinder.command.use");
    }

    /** @var Vector3[]  */
    protected array $positions = [];
    protected float $timeout = 0.05;

    public function execute(CommandSender $sender, string $commandLabel, array $args): void{
        $player = $sender;
        if(!$sender instanceof Player) {
            $player = Server::getInstance()->getOnlinePlayers()[array_key_first(Server::getInstance()->getOnlinePlayers())] ?? null;
        }
        if(!$player instanceof Player) {//Writing commands on mobile sucks, so everything can be done in the console
            $sender->sendMessage("No player found!");
            return;
        }
        $location = $player->getLocation();
        $position = Position::fromObject($location->floor(), $location->getWorld());

        switch($args[0] ?? "") {
            case "1": {
                $this->positions[1] = $position;
                $sender->sendMessage("First position set.");
                break;
            }
            case "2": {
                $this->positions[2] = $position;
                $sender->sendMessage("Second position set.");
                break;
            }
            case "run": {
                if(!isset($this->positions[1]) || !isset($this->positions[2])) {
                    $sender->sendMessage("Setup required!");
                    return;
                }
                $world = $location->getWorld();
                (new AStar($world, $this->positions[1], $this->positions[2], $player->getBoundingBox(),
                    (new AlgorithmSettings())
                        ->setTimeout($this->timeout))
                )->then(function(?PathResult $pathResult) use ($sender, $world): void {
                        if(($sender instanceof Player && !$sender->isConnected()) || !$world->isLoaded()) return;
                        if($pathResult === null) {
                            $sender->sendMessage("No path found!");
                            return;
                        }
                        /** @var Block[] $blocks */
                        $blocks = [];
                        foreach($pathResult->getPathPoints() as $pathPoint) {
                            if(!$world->isChunkLoaded($pathPoint->getFloorX() >> 4, $pathPoint->getFloorZ() >> 4)) continue;
                            $blocks[] = $$world->getBlock($pathPoint->subtract(0, 1, 0));
                            $world->setBlock($pathPoint->subtract(0, 1, 0), VanillaBlocks::CONCRETE()->setColor(DyeColor::BLUE()), false);
                        }
                        $sender->sendMessage("Done! Took ".$pathResult->getTime()." seconds");

                        Pathfinder::$instance->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($blocks): void {
                            foreach($blocks as $block){
                                $position = $block->getPosition();
                                if(!$position->getWorld()->isChunkLoaded($position->getFloorX() >> 4, $position->getFloorZ() >> 4)) continue;
                                $position->getWorld()->setBlock($block->getPosition(), $block, false);
                            }
                        }), 140);
                    })->start();
                break;
            }
            case "timeout": {
                $timeout = floatval($args[1] ?? 0.05);
                $this->timeout = $timeout;
                $sender->sendMessage("Set timeout to ".$timeout." seconds");
                break;
            }
            case "entity": {
                $entity = new TestEntity($player->getLocation());
                $entity->spawnToAll();
                break;
            }
            default: {
                $sender->sendMessage("Pathfinder Command Help:");
                $sender->sendMessage("/pathfinder 1");
                $sender->sendMessage("/pathfinder 2");
                $sender->sendMessage("/pathfinder run");
                $sender->sendMessage("/pathfinder timeout");
                $sender->sendMessage("/pathfinder entity");
                break;
            }
        }
    }
}