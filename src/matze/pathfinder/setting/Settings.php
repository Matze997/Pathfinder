<?php

declare(strict_types=1);

namespace matze\pathfinder\setting;

use matze\pathfinder\setting\rule\DefaultPathRules;
use matze\pathfinder\setting\rule\PathRules;
use pocketmine\entity\EntitySizeInfo;

class Settings{
    private int $jumpHeight = 1;
    private int $fallDistance = 2;

    private float $timeout = 0.05;

    private ?EntitySizeInfo $size = null;

    private ?PathRules $pathRules = null;

    public static function get(): self {
        return new self();
    }

    public function getTimeout(): float{
        return $this->timeout;
    }

    public function setTimeout(float $timeout): self {
        $this->timeout = $timeout;
        return $this;
    }

    public function getFallDistance(): int{
        return $this->fallDistance;
    }

    public function setFallDistance(int $fallDistance): self {
        $this->fallDistance = $fallDistance;
        return $this;
    }

    public function getJumpHeight(): int{
        return $this->jumpHeight;
    }

    public function setJumpHeight(int $jumpHeight): self {
        $this->jumpHeight = $jumpHeight;
        return $this;
    }

    public function getSize(): EntitySizeInfo{
        return $this->size ?? new EntitySizeInfo(1.8, 1);
    }

    public function setSize(?EntitySizeInfo $size): void{
        $this->size = $size;
    }

    public function getPathRules(): PathRules{
        return $this->pathRules ??= new DefaultPathRules();
    }

    public function setPathRules(PathRules $pathRules): self{
        $this->pathRules = $pathRules;
        return $this;
    }
}