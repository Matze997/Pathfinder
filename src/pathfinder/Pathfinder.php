<?php

declare(strict_types=1);

namespace pathfinder;

use pathfinder\command\TestCommand;
use pathfinder\entity\TestEntity;
use pathfinder\listener\block\BlockBreakListener;
use pathfinder\listener\block\BlockPlaceListener;
use pathfinder\listener\block\BlockUpdateListener;
use pathfinder\listener\world\ChunkLoadListener;
use pathfinder\listener\world\ChunkUnloadListener;
use pathfinder\listener\world\WorldUnloadListener;
use pathfinder\queue\ValidatorQueue;
use pathfinder\tool\DebugTool;
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

        Server::getInstance()->getCommandMap()->register("pathfinder", new TestCommand());

        $pluginManager = Server::getInstance()->getPluginManager();
        $pluginManager->registerEvents(new ChunkLoadListener(), $this);
        $pluginManager->registerEvents(new ChunkUnloadListener(), $this);
        $pluginManager->registerEvents(new WorldUnloadListener(), $this);
        $pluginManager->registerEvents(new BlockUpdateListener(), $this);
        $pluginManager->registerEvents(new BlockPlaceListener(), $this);
        $pluginManager->registerEvents(new BlockBreakListener(), $this);

        EntityFactory::getInstance()->register(TestEntity::class, function(World $world, CompoundTag $nbt) : TestEntity{
            return new TestEntity(EntityDataHelper::parseLocation($nbt, $world), $nbt);
        }, ["TestEntity"], EntityLegacyIds::VILLAGER);

        ValidatorQueue::start();
        DebugTool::start();
    }
}