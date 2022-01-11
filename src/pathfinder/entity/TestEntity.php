<?php

declare(strict_types=1);

namespace pathfinder\entity;

use pathfinder\algorithm\AlgorithmSettings;
use pathfinder\navigator\Navigator;
use pocketmine\entity\Location;
use pocketmine\entity\Villager;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Server;
use function array_key_first;
use function intval;

class TestEntity extends Villager {
    protected Navigator $navigator;

    public function __construct(Location $location, ?CompoundTag $nbt = null){
        $this->navigator = new Navigator($this, null, null,
            (new AlgorithmSettings())
                ->setTimeout(0.05)
                ->setMaxTicks(0)
        );
        parent::__construct($location, $nbt);

        $this->setScale(0.5);
    }

    public function onUpdate(int $currentTick): bool{
        $target = Server::getInstance()->getOnlinePlayers()[array_key_first(Server::getInstance()->getOnlinePlayers())] ?? null;
        if($target === null) return parent::onUpdate($currentTick);
        $position = $target->getPosition();
        $targetVector3 = $this->navigator->getTargetVector3();
        if(!$position->world->isInWorld(intval($position->x), intval($position->y), intval($position->z))){
            return parent::onUpdate($currentTick);
        }

        if($this->navigator->getTargetVector3() === null || $targetVector3->distanceSquared($position) > 1) {
            $this->navigator->setTargetVector3($position);
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