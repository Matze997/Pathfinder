<?php

declare(strict_types=1);

namespace pathfinder\entity;

use pathfinder\navigator\Navigator;
use pocketmine\entity\Location;
use pocketmine\entity\Villager;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Server;
use function array_key_first;

class TestEntity extends Villager {
    protected Navigator $navigator;

    public function __construct(Location $location, ?CompoundTag $nbt = null){
        $this->navigator = new Navigator($this);
        parent::__construct($location, $nbt);

        $this->setScale(0.5);
    }

    public function onUpdate(int $currentTick): bool{
        $target = Server::getInstance()->getOnlinePlayers()[array_key_first(Server::getInstance()->getOnlinePlayers())] ?? null;
        if($target === null) return parent::onUpdate($currentTick);
        if($this->navigator->getTargetEntity() === null) {
            $this->navigator->setTargetEntity($target);
        }

        $this->navigator->onUpdate();
        return parent::onUpdate($currentTick);
    }

    public function attack(EntityDamageEvent $source): void{
        parent::attack($source);
        $this->setHealth($this->getMaxHealth());

        if($source->getCause() === EntityDamageEvent::CAUSE_FIRE) {
            $this->flagForDespawn();
        }
    }
}