<?php

declare(strict_types=1);

namespace pathfinder\navigator;

use Closure;
use pathfinder\algorithm\Algorithm;
use pathfinder\algorithm\astar\AStar;
use pathfinder\cost\CostCalculator;
use pathfinder\cost\DefaultCostCalculator;
use pathfinder\navigator\handler\DefaultMovementHandler;
use pathfinder\navigator\handler\MovementHandler;
use pathfinder\pathpoint\PathPoint;
use pathfinder\pathresult\PathResult;
use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\math\Vector3;
use function count;
use function intval;

class Navigator {
    protected Entity $entity;

    protected float $speed = 0.3;

    protected ?Vector3 $targetVector3 = null;
    protected ?PathResult $pathResult = null;

    protected int $index = 0;

    protected int $jumpTicks = 0;
    protected int $stuckTicks = 0;

    protected ?PathPoint $lastPathPoint = null;
    protected ?Vector3 $lastVector3 = null;

    protected array $blockValidators = [];

    protected MovementHandler $movementHandler;
    protected CostCalculator $costCalculator;

    protected ?Living $targetEntity = null;

    protected ?Algorithm $algorithm = null;

    public function __construct(Living $entity, ?MovementHandler $movementHandler = null, ?CostCalculator $costCalculator = null){
        $this->entity = $entity;
        $this->movementHandler = $movementHandler ?? new DefaultMovementHandler();
        $this->costCalculator = $costCalculator ?? new DefaultCostCalculator();
    }

    public function getEntity(): Living{
        return $this->entity;
    }

    public function getSpeed(): float{
        return $this->speed;
    }

    public function setSpeed(float $speed): void{
        $this->speed = $speed;
    }

    public function getMovementHandler(): MovementHandler|DefaultMovementHandler{
        return $this->movementHandler;
    }

    public function getPathResult(): ?PathResult{
        return $this->pathResult;
    }

    public function getLastPathPoint(): ?PathPoint{
        return $this->lastPathPoint;
    }

    public function getIndex(): int{
        return $this->index;
    }

    public function getStuckTicks(): int{
        return $this->stuckTicks;
    }

    public function getJumpTicks(): int{
        return $this->jumpTicks;
    }

    public function resetJumpTicks(int $ticks = 4): void {
        $this->jumpTicks = $ticks;
    }

    public function registerBlockValidator(Block $block, Closure $closure): void {
        $this->blockValidators[$block->getId()] = $closure;
    }

    public function getTargetEntity(): ?Entity{
        return $this->targetEntity;
    }

    public function setTargetEntity(?Entity $targetEntity): void{
        $this->targetEntity = $targetEntity;
    }

    public function getTargetVector3(): ?Vector3{
        return $this->targetVector3;
    }

    public function setTargetVector3(?Vector3 $targetVector3): void{
        $this->targetVector3 = $targetVector3;
        $this->recalculatePath();
    }

    public function recalculatePath(): void {
        $this->algorithm?->stop();
        $this->algorithm = null;
        $this->pathResult = null;
        $this->lastPathPoint = null;
        $this->index = 0;
    }

    public function onUpdate(): void {
        if($this->targetEntity !== null) {
            //TODO: Move this to behaviors
            if(
                $this->targetEntity->isClosed() ||
                !$this->targetEntity->isAlive()
            ) $this->setTargetEntity(null);

            if($this->targetEntity !== null) {
                $position = $this->targetEntity->getPosition();
                if(!$position->world->isInWorld(intval($position->x), intval($position->y), intval($position->z))) return;
                if($this->targetVector3 === null || $this->targetVector3->distanceSquared($position) > 1) {
                    $this->setTargetVector3($position);
                }
            }
        }
        if($this->targetVector3 === null) return;

        $location = $this->entity->getLocation();
        if($this->pathResult === null) {
            if($this->algorithm === null || !$this->algorithm->isRunning()) {
                $this->algorithm = (new AStar($this->entity->getWorld(), $location->floor(), $this->targetVector3, $this->entity->getBoundingBox()))
                    ->setBlockValidators($this->blockValidators)
                    ->setTimeout(0.0005)
                    ->setMaxTicks(0)
                    ->then(function(?PathResult $pathResult): void {
                        $this->pathResult = $pathResult;
                        if($pathResult === null) return;
                        $count = count($this->pathResult->getPathPoints());
                        $this->index = match (true) {
                            ($count > 1) => ($count - 2),
                            default => ($count - 1)
                        };
                    })->start();
            }
            return;
        }
        $pathPoint = $this->pathResult->getPathPoint($this->index);
        if($pathPoint === null){
            $this->lastPathPoint = null;
            $this->recalculatePath();
            return;
        }

        if($location->withComponents(null, 0, null)->distanceSquared($pathPoint->withComponents(null, 0, null)) <= 0.2) {
            $pathPoint = $this->pathResult->getPathPoint(--$this->index);
            if($pathPoint === null){
                $this->recalculatePath();
                return;
            }
        }
        if($this->jumpTicks > 0) $this->jumpTicks--;
        $this->movementHandler->handle($this, $pathPoint);
        if($this->lastVector3 !== null && $this->lastVector3->x === $location->x && $this->lastVector3->z === $location->z) {
            if(++$this->stuckTicks >= 20){
                $this->recalculatePath();
                $this->stuckTicks = 0;
            }
        } else {
            $this->stuckTicks = 0;
        }
        $this->lastVector3 = $location->asVector3();
        $this->lastPathPoint = $pathPoint;
    }
}