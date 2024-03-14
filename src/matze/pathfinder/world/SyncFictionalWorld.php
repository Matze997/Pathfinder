<?php

declare(strict_types=1);

namespace matze\pathfinder\world;

use pocketmine\block\Block;
use pocketmine\Server;
use pocketmine\world\World;

class SyncFictionalWorld extends FictionalWorld {
    private World $pmWorld;

    public function __construct(string $world){
        parent::__construct($world);
        $this->pmWorld = Server::getInstance()->getWorldManager()->getWorldByName($world);
    }

    public function getBlockAt(int $x, int $y, int $z): Block{
        return $this->pmWorld->getBlockAt($x, $y, $z);
    }
}