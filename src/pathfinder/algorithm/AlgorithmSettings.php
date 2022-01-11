<?php

declare(strict_types=1);

namespace pathfinder\algorithm;

use pathfinder\cost\CostCalculator;
use pathfinder\cost\DefaultCostCalculator;

class AlgorithmSettings {
    protected CostCalculator $costCalculator;

    public function __construct(
        protected int $jumpHeight = 1,
        protected int $fallDistance = 2,
        protected float $timeout = 0.05,
        protected int $maxTicks = 0,
        protected bool $onlyAcceptFullPath = false,
        ?CostCalculator $costCalculator = null
    ) {
        //TODO: Do not load every time all blocks
        $this->costCalculator = $costCalculator ?? new DefaultCostCalculator();
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

    public function getCostCalculator(): ?CostCalculator{
        return $this->costCalculator;
    }

    public function setCostCalculator(?CostCalculator $costCalculator): self{
        $this->costCalculator = $costCalculator;
        return $this;
    }
}