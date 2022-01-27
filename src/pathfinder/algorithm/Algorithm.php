<?php
declare(strict_types=1);
namespace pathfinder\algorithm;
use Closure;
use pathfinder\algorithm\path\PathResult;
use pathfinder\Pathfinder;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;
use pocketmine\scheduler\TaskHandler;
use pocketmine\world\World;

use function microtime;
use function round;


abstract class Algorithm{
	protected AlgorithmSettings $settings;
	protected AxisAlignedBB $axisAlignedBB;
	protected ?PathResult $pathResult = null;
	protected ?Closure $onCompletion = null;
	protected int $tick = 0;
	protected float $startTime;
	protected float $startTickTime;
	protected bool $running = false;

	public function __construct(protected World $world, protected Vector3 $startVector3, protected Vector3 $targetVector3, ?AxisAlignedBB $axisAlignedBB = null, ?AlgorithmSettings $settings = null){
		$this->axisAlignedBB = $axisAlignedBB ?? AxisAlignedBB::one();
		$this->settings = $settings ?? new AlgorithmSettings();
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

	public function getSettings(): AlgorithmSettings{
		return $this->settings;
	}

	public function getPathResult(): ?PathResult{
		return $this->pathResult;
	}

	/**
	 * $closure(?PathResult $pathResult) is called, when the path has been found or the pathfinder has a timeout
	 *  => $pathResult is null, when no path was found
	 */
	public function then(Closure $closure): self{
		$this->onCompletion = $closure;
		return $this;
	}

	public function isRunning(): bool{
		return $this->running;
	}

	public function start(): self{
		if ($this->running)
			return $this;
		$this->running = true;
		$this->startTime = microtime(true);
		$this->tick = $this->settings->getMaxTicks();
		if ($this->tick > 0) {
			Pathfinder::$instance->getScheduler()->scheduleRepeatingTask(new class($this) extends Task{
					private Algorithm $algorithm;

					public function __construct(Algorithm $algorithm){
						$this->algorithm = $algorithm;
					}

					public function onRun(): void{
						$this->algorithm->onTick($this->getHandler());
					}
				}, 1);
		} else {
			$this->onTick(null);
		}
		return $this;
	}

	public function onTick(?TaskHandler $handler): void{
		$this->startTickTime = microtime(true);
		$this->tick();
		if (--$this->tick <= 0 || !$this->running) {
			$handler?->cancel();
			$this->stop();
		}
	}

	abstract protected function tick(): void;

	public function stop(): void{
		if (!$this->running)
			return;
		$this->finish();
		$this->pathResult?->setTime(round(microtime(true) - $this->startTime, 5));
		if ($this->onCompletion !== null) {
			($this->onCompletion)($this->pathResult);
		}
		$this->running = false;
	}

	abstract protected function finish(): void;

	protected function checkTimout(): bool{
		$timeout = $this->settings->getTimeout();
		return $timeout === 0.0 || microtime(true) - $this->startTickTime < $timeout;
	}
}