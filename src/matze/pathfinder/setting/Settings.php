<?php

declare(strict_types=1);

namespace matze\pathfinder\setting;

class Settings {
    private int $maxTravelDistanceUp = 1;
    private int $maxTravelDistanceDown = 1;
    private bool $pathSmoothing = true;

    public static function get(): self {
        return new self();
    }

    /**
     * Set how many blocks the pathfinder should travel down until a position is declared invalid
     */
    public function setMaxTravelDistanceDown(int $maxTravelDistanceDown): self{
        $this->maxTravelDistanceDown = $maxTravelDistanceDown;
        return $this;
    }

    public function getMaxTravelDistanceDown(): int{
        return $this->maxTravelDistanceDown;
    }

    /**
     * Set how many blocks the pathfinder should travel up until a position is declared invalid
     */
    public function setMaxTravelDistanceUp(int $maxTravelDistanceUp): self{
        $this->maxTravelDistanceUp = $maxTravelDistanceUp;
        return $this;
    }

    public function getMaxTravelDistanceUp(): int{
        return $this->maxTravelDistanceUp;
    }

    /**
     * Set if the path should be smoothed before it gets returned
     */
    public function setPathSmoothing(bool $pathSmoothing): self{
        $this->pathSmoothing = $pathSmoothing;
        return $this;
    }

    public function isPathSmoothing(): bool{
        return $this->pathSmoothing;
    }
}