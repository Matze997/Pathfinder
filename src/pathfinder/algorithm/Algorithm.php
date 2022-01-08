<?php

declare(strict_types=1);

namespace pathfinder\algorithm;

use Closure;
use pathfinder\Pathfinder;
use pathfinder\pathresult\PathResult;
use pocketmine\block\BaseRail;
use pocketmine\block\Block;
use pocketmine\block\Lava;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;
use pocketmine\scheduler\TaskHandler;
use pocketmine\world\World;
use function ceil;
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

    private bool $onlyAcceptFullPath = false;

    private ?Closure $onCompletion = null;

    protected int $tick = 0;
    protected int $maxTicks = 0;
    protected float $startTickTime;

    protected bool $running = false;

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

    public function setJumpHeight(int $jumpHeight): self{
        $this->jumpHeight = $jumpHeight;
        return $this;
    }

    public function getFallDistance(): int{
        return $this->fallDistance;
    }

    public function setFallDistance(int $fallDistance): self{
        $this->fallDistance = $fallDistance;
        return $this;
    }

    public function isOnlyAcceptFullPath(): bool{
        return $this->onlyAcceptFullPath;
    }

    /**
     * When true, the pathfinder either returns the full path and when not found, null
     */
    public function setOnlyAcceptFullPath(bool $onlyAcceptFullPath): self{
        $this->onlyAcceptFullPath = $onlyAcceptFullPath;
        return $this;
    }

    public function getTimeout(): float{
        return $this->timeout;
    }

    /**
     * Set, after how many seconds the pathfinder will stop
     */
    public function setTimeout(float $timeout): self{
        $this->timeout = $timeout;
        return $this;
    }

    public function getMaxTicks(): int{
        return $this->maxTicks;
    }

    /**
     * Set, after how many ticks the pathfinder will stop
     */
    public function setMaxTicks(int $maxTicks): self{
        $this->maxTicks = $maxTicks;
        return $this;
    }

    public function getPathResult(): ?PathResult{
        return $this->pathResult;
    }

    /**
     * $closure(?PathResult $pathResult) is called, when the path has been found or the pathfinder has a timeout
     *  => $pathResult is null, when no path was found
     */
    public function then(Closure $closure): self {
        $this->onCompletion = $closure;
        return $this;
    }

    /**
     * Register new block validator
     * E.g. You can register a block validator for powered rails so that mobs can path find through them
     *
     *  $this->registerBlockValidator(VanillaBlocks::POWERED_RAIL(), function(Block $block): bool {
     *       return true;
     *   });
     */
    public function registerBlockValidator(Block $block, Closure $closure): self {
        $this->blockValidators[$block->getId()] = $closure;
        return $this;
    }

    /**
     * @see registerBlockValidator()
     *
     * [
     *   BLOCK_ID => Closure,
     *   ...
     * ]
     */
    public function setBlockValidators(array $blockValidators): self{
        $this->blockValidators = $blockValidators;
        return $this;
    }

    public function isRunning(): bool{
        return $this->running;
    }

    public function getTick(): int{
        return $this->tick;
    }

    protected function checkTimout(): bool{
        return $this->timeout === 0.0 || microtime(true) - $this->startTickTime < $this->timeout;
    }

    public function start(): self {
        if($this->running) return $this;
        $this->running = true;
        $this->startTime = microtime(true);
        $this->tick = $this->maxTicks;
        if($this->tick > 0) {
            Pathfinder::$instance->getScheduler()->scheduleRepeatingTask(
                new class($this) extends Task {
                    private Algorithm $algorithm;

                    public function __construct(Algorithm $algorithm){
                        $this->algorithm = $algorithm;
                    }

                    public function onRun(): void{
                        $this->algorithm->onTick($this->getHandler());
                    }
                }, 1
            );
        } else {
            $this->onTick(null);
        }
        return $this;
    }

    public function stop(): void {
        if(!$this->running) return;
        $this->finish();
        $this->pathResult?->setTime(round(microtime(true) - $this->startTime, 5));
        if($this->onCompletion !== null){
            ($this->onCompletion)($this->pathResult);
        }
        $this->running = false;
    }

    public function onTick(?TaskHandler $handler): void {
        $this->startTickTime = microtime(true);
        $this->tick();
        if(--$this->tick <= 0 || !$this->running){
            $handler?->cancel();
            $this->stop();
        }
    }

    abstract protected function finish(): void;
    abstract protected function tick(): void;

    protected function isBlockEmpty(Block $block): bool {
        if(isset($this->blockValidators[$block->getId()])) {
            return ($this->blockValidators[$block->getId()])($block);
        }
        return !$block->isSolid() && !$block instanceof BaseRail && !$block instanceof Lava;
    }

    protected function isSafeToStandAt(Vector3 $vector3): bool {
        $world = $this->getWorld();
        $block = $world->getBlock($vector3->subtract(0, 1, 0));
        if(!$block->isSolid() && !$block instanceof Slab && !$block instanceof Stair) return false;
        $axisAlignedBB = $this->getAxisAlignedBB();
        $height = ceil($axisAlignedBB->maxY - $axisAlignedBB->minY);
        for($y = 0; $y <= $height; $y++) {
            if(!$this->isBlockEmpty($world->getBlock($vector3->add(0, $y, 0)))) return false;
        }
        return true;
    }
}