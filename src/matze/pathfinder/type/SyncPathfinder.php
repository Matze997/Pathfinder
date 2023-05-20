<?php

declare(strict_types=1);

namespace matze\pathfinder\type;

use matze\pathfinder\BasePathfinder;
use matze\pathfinder\IPathfinder;
use matze\pathfinder\setting\Settings;
use pocketmine\block\Block;
use pocketmine\world\World;

class SyncPathfinder extends BasePathfinder implements IPathfinder {
    public function __construct(
        Settings $settings,
        protected World $world
    ){
        parent::__construct($settings);
    }

    protected function getBlockAt(int $x, int $y, int $z): Block{
        return $this->world->getBlockAt($x, $y, $z);
    }
}