<?php

declare(strict_types=1);

namespace pathfinder\algorithm;

use Closure;
use pathfinder\pathresult\PathResult;
use pocketmine\block\Block;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\world\World;
use function microtime;
use function round;

abstract class Algorithm {
    protected World $world;

    protected Vector3 $startVector3;
    protected Vector3 $targetVector3;

    protected float $startTime;
    protected float $timeout = 0.05;

    protected int $jumpHeight = 1;
    protected int $fallDistance = 2;

    protected AxisAlignedBB $axisAlignedBB;

    protected ?PathResult $pathResult = null;

    protected array $blockValidators = [];

    public function __construct(World $world, Vector3 $startVector3, Vector3 $targetVector3, ?AxisAlignedBB $axisAlignedBB = null){
        $this->world = $world;
        $this->startVector3 = $startVector3;
        $this->targetVector3 = $targetVector3;
        $this->axisAlignedBB = $axisAlignedBB ?? AxisAlignedBB::one();
    }

    public function getWorld(): World{
        return $this->world;
    }

    public function getStartVector3(): Vector3{
        return $this->startVector3;
    }

    public function getTargetVector3(): Vector3{
        return $this->targetVector3;
    }

    public function getAxisAlignedBB(): AxisAlignedBB{
        return $this->axisAlignedBB;
    }

    public function getJumpHeight(): int{
        return $this->jumpHeight;
    }

    public function setJumpHeight(int $jumpHeight): void{
        $this->jumpHeight = $jumpHeight;
    }

    public function getFallDistance(): int{
        return $this->fallDistance;
    }

    public function setFallDistance(int $fallDistance): void{
        $this->fallDistance = $fallDistance;
    }

    public function getTimeout(): float{
        return $this->timeout;
    }

    public function setTimeout(float $timeout): void{
        $this->timeout = $timeout;
    }

    protected function checkTimout(): bool{
        return $this->timeout === 0.0 or microtime(true) - $this->startTime < $this->timeout;
    }

    public function start(): void {
        $this->startTime = microtime(true);
        $this->pathResult = $this->run();
        $this->pathResult?->setTime(round(microtime(true) - $this->startTime, 5));
    }

    public function getPathResult(): ?PathResult{
        return $this->pathResult;
    }

    protected function run(): ?PathResult {
        return null;
    }

    public function registerBlockValidator(Block $block, Closure $closure): void {
        $this->blockValidators[$block->getId()] = $closure;
    }

    public function setBlockValidators(array $blockValidators): void{
        $this->blockValidators = $blockValidators;
    }
}