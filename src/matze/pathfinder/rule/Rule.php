<?php

declare(strict_types=1);

namespace matze\pathfinder\rule;

use matze\pathfinder\setting\Settings;
use matze\pathfinder\world\FictionalWorld;
use pocketmine\math\Vector3;

abstract class Rule {
    public const PRIORITY_LOWEST = 0;
    public const PRIORITY_LOW = 1;
    public const PRIORITY_NORMAL = 2;
    public const PRIORITY_HIGH = 3;
    public const PRIORITY_HIGHEST = 4;

    public function __construct(
        private int $priority = self::PRIORITY_NORMAL,
    ){}

    /**
     * Rules will be executed from HIGHEST to LOWEST
     */
    public function getPriority(): int{
        return $this->priority;
    }

    public function setPriority(int $priority): void{
        $this->priority = $priority;
    }

    public function couldWalkTo(Vector3 $currentNode, Vector3 $targetNode, FictionalWorld $world, Settings $settings, int &$cost): bool {
        return $this->couldStandAt($targetNode, $world, $cost);
    }

    abstract public function couldStandAt(Vector3 $node, FictionalWorld $world, int &$cost): bool;
}