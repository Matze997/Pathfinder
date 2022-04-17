<?php

declare(strict_types=1);

namespace pathfinder\algorithm;

use pathfinder\algorithm\cost\CostCalculator;
use pathfinder\algorithm\cost\DefaultCostCalculator;
use pathfinder\algorithm\validator\DefaultValidator;
use pathfinder\algorithm\validator\Validator;

class AlgorithmSettings {
    protected CostCalculator $costCalculator;
    protected Validator $validator;

    protected float $gCostMultiplier = 1.0;

    public function __construct(
        protected int $jumpHeight = 1,
        protected int $fallDistance = 2,
        protected float $timeout = 0.05,
        protected int $maxTicks = 0,
        protected bool $onlyAcceptFullPath = false,
        ?CostCalculator $costCalculator = null,
        ?Validator $validator = null
    ) {
        //TODO: Do not load every time all blocks
        $this->costCalculator = $costCalculator ?? new DefaultCostCalculator();
        $this->validator = $validator ?? new DefaultValidator();
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

    public function getValidator(): Validator{
        return $this->validator;
    }

    public function setValidator(Validator $validator): self{
        $this->validator = $validator;
        return $this;
    }

    public function getGCostMultiplier(): float{
        return $this->gCostMultiplier;
    }

    public function setGCostMultiplier(float $gCostMultiplier): self{
        $this->gCostMultiplier = $gCostMultiplier;
        return $this;
    }
}