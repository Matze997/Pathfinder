<?php
/*
 * Copyright (c) Matze997
 * All rights reserved.
 * Under GPL license
 */

declare(strict_types=1);

namespace pathfinder\entity\navigator;

use Closure;
use pathfinder\algorithm\Algorithm;
use pathfinder\algorithm\AlgorithmSettings;
use pathfinder\algorithm\astar\AStar;
use pathfinder\algorithm\cost\CostCalculator;
use pathfinder\algorithm\cost\DefaultCostCalculator;
use pathfinder\algorithm\path\PathPoint;
use pathfinder\algorithm\path\PathResult;
use pathfinder\entity\navigator\handler\DefaultMovementHandler;
use pathfinder\entity\navigator\handler\MovementHandler;
use pocketmine\block\Block;
use pocketmine\entity\Living;
use pocketmine\math\Vector3;
use function count;

class Navigator {
    protected float $speed = 0.3;

    protected int $index = 0;

    protected ?Vector3 $targetVector3 = null;
    protected ?PathResult $pathResult = null;

    protected ?PathPoint $lastPathPoint = null;
    protected ?Vector3 $lastVector3 = null;

    protected array $blockValidators = [];

    protected MovementHandler $movementHandler;
    protected CostCalculator $costCalculator;
    protected AlgorithmSettings $algorithmSettings;

    protected ?Algorithm $algorithm = null;

    protected int $jumpTicks = 0;
    protected int $stuckTicks = 0;

    public function __construct(protected Living $entity, ?MovementHandler $movementHandler = null, ?CostCalculator $costCalculator = null, ?AlgorithmSettings $algorithmSettings = null){
        $this->movementHandler = $movementHandler ?? new DefaultMovementHandler();
        $this->costCalculator = $costCalculator ?? new DefaultCostCalculator();
        $this->algorithmSettings = $algorithmSettings ?? new AlgorithmSettings();
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

    public function getAlgorithmSettings(): AlgorithmSettings{
        return $this->algorithmSettings;
    }

    public function getPathResult(): ?PathResult{
        return $this->pathResult;
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
        if($this->targetVector3 === null) return;

        $location = $this->entity->getLocation();
        if($this->pathResult === null) {
            if($this->algorithm === null || !$this->algorithm->isRunning()) {
                $this->algorithm = (new AStar($this->entity->getWorld(), $location->floor(), $this->targetVector3, $this->entity->getBoundingBox(), $this->getAlgorithmSettings()))
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