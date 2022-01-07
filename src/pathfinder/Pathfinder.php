<?php

declare(strict_types=1);

namespace pathfinder;

use pathfinder\command\PathfinderCommand;
use pathfinder\entity\TestEntity;
use pocketmine\data\bedrock\EntityLegacyIds;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\world\World;

class Pathfinder extends PluginBase {
    public static Pathfinder $instance;

    protected function onEnable(): void{
        self::$instance = $this;

        Server::getInstance()->getCommandMap()->register("pathfinder", new PathfinderCommand());

        EntityFactory::getInstance()->register(TestEntity::class, function(World $world, CompoundTag $nbt) : TestEntity{
            return new TestEntity(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ["TestEntity"], EntityLegacyIds::VILLAGER);

        //DebugTool::start();
    }
}