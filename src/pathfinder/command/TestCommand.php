<?php

declare(strict_types=1);

namespace pathfinder\command;

use pathfinder\algorithm\astar\AStar;
use pathfinder\entity\TestEntity;
use pathfinder\Pathfinder;
use pathfinder\pathpoint\PathPointManager;
use pathfinder\queue\ValidatorQueue;
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

class TestCommand extends Command {
    public function __construct(){
        parent::__construct("pathfinder");
    }

    /** @var Vector3[]  */
    protected array $positions = [];
    protected float $timeout = 0.05;

    public function execute(CommandSender $sender, string $commandLabel, array $args): void{
        $player = $sender;
        if(!$sender instanceof Player) {
            $player = Server::getInstance()->getOnlinePlayers()[array_key_first(Server::getInstance()->getOnlinePlayers())] ?? null;
        }
        if(!$player instanceof Player) {
            $sender->sendMessage("No player found!");
            return;
        }
        $location = $player->getLocation();
        $position = Position::fromObject($location->floor(), $location->getWorld());

        switch($args[0] ?? "") {
            case "1": {
                $this->positions[1] = $position;
                $sender->sendMessage("First position set.");
                $player->sendMessage("First position set.");
                break;
            }
            case "2": {
                $this->positions[2] = $position;
                $sender->sendMessage("Second position set.");
                $player->sendMessage("Second position set.");
                break;
            }
            case "run": {
                if(!isset($this->positions[1]) || !isset($this->positions[2])) {
                    $sender->sendMessage("Setup required!");
                    $player->sendMessage("Setup required!");
                    return;
                }
                $aStar = new AStar($location->getWorld(), $this->positions[1], $this->positions[2], $player->getBoundingBox());
                $aStar->setTimeout($this->timeout);
                $aStar->start();

                $pathResult = $aStar->getPathResult();
                if($pathResult === null) {
                    $sender->sendMessage("No path found!");
                    $player->sendMessage("No path found!");
                    return;
                }
                /** @var Block[] $blocks */
                $blocks = [];
                foreach($pathResult->getPathPoints() as $pathPoint) {
                    $blocks[] = $location->world->getBlock($pathPoint->subtract(0, 1, 0));
                    $location->world->setBlock($pathPoint->subtract(0, 1, 0), VanillaBlocks::CONCRETE()->setColor(DyeColor::BLUE()), false);
                }
                $sender->sendMessage("Done! Took ".$pathResult->getTime()." seconds");
                $player->sendMessage("Done! Took ".$pathResult->getTime()." seconds");

                Pathfinder::$instance->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use ($blocks): void {
                    foreach($blocks as $block) $block->getPosition()->getWorld()->setBlock($block->getPosition(), $block, false);
                }), 140);
                break;
            }
            case "timeout": {
                $timeout = floatval($args[1] ?? 0.05);
                $this->timeout = $timeout;
                $sender->sendMessage("Set timeout to ".$timeout." seconds");
                $player->sendMessage("Set timeout to ".$timeout." seconds");
                break;
            }
            case "entity": {
                $entity = new TestEntity($player->getLocation());
                $entity->spawnToAll();
                break;
            }
        }
    }
}