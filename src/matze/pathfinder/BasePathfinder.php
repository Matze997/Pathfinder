<?php

declare(strict_types=1);

namespace matze\pathfinder;

use matze\pathfinder\node\Node;
use matze\pathfinder\result\PathResult;
use matze\pathfinder\setting\Settings;
use pocketmine\block\Block;
use pocketmine\math\Vector3;

abstract class BasePathfinder{
    private const SIDES = [
        [0, 1],
        [1, 0],
        [0, -1],
        [-1, 0],
    ];

    /** @var Node[]  */
    private array $openList = [];

    public function __construct(
        protected Settings $settings,
    ){}

    abstract protected function getBlockAt(int $x, int $y, int $z): Block;

    public function findPath(Vector3 $startVector, Vector3 $targetVector): ?PathResult {
        if($startVector->floor()->equals($targetVector->floor())) {
            return null;
        }
        $this->openList = [];
        $closedList = [];

        $startNode = Node::fromVector3($startVector);
        $startNode->setG(0.0);

        $targetNode = Node::fromVector3($targetVector);

        $this->openList[$startNode->getHash()] = $startNode;

        $bestNode = null;

        $result = new PathResult();

        $startTime = microtime(true);
        $timeout = $this->settings->getTimeout();
        while((microtime(true) - $startTime) < $timeout) {
            $key = $this->getLowestFCost();
            if($key === null){
                break;
            }
            /** @var Node $currentNode */
            $currentNode = $this->openList[$key];

            unset($this->openList[$currentNode->getHash()]);
            $closedList[$currentNode->getHash()] = $currentNode;

            if($currentNode->getHash() === $targetNode->getHash()) {
                $targetNode->setParentNode($currentNode);
                $result->addNode($targetNode);
                break;
            }

            foreach(self::SIDES as $SIDE) {
                $x = (int)floor($currentNode->x + $SIDE[0]);
                $y = $currentNode->y;
                $z = (int)floor($currentNode->z + $SIDE[1]);
                if(!$this->isSafeToWalkAt($currentNode, $x, $y, $z)) {
                    continue;
                }
                $node = new Node($x + 0.5, $y, $z + 0.5);
                if(isset($closedList[$node->getHash()])) {
                    continue;
                }
                $cost = $this->getCostInside($this->getBlockAt($x, (int)$y, $z)) + $this->getCostStanding($this->getBlockAt($x, (int)($y - 1), $z)) + abs($currentNode->getY() - $node->getY());
                if(!isset($this->openList[$node->getHash()]) || ($currentNode->getG() + $cost) < $node->getG()) {
                    $node->setG(($currentNode->getG() + $cost));
                    $node->setH($node->distance($targetVector));
                    $node->setParentNode($currentNode);
                    $this->openList[$node->getHash()] = $node;
                    if($bestNode === null || $bestNode->getH() > $node->getH()) {
                        $bestNode = $node;
                    }
                }
            }
        }
        $node = $targetNode->getParentNode();
        if($node === null) {
            $node = $bestNode;
            if($node === null) {
                return null;
            }
        }
        $result->addNode($node);

        $start = null;
        $clear = false;
        while(true) {
            $last = clone $node;
            $node = $node->getParentNode();
            if(!$node instanceof Node) {
                $result->addNode($last);
                break;
            }
            if($start === null) {
                $start = $last;
            }
            if($start !== null && $this->isClearBetweenPoints($start, $node)) {
                $clear = true;
                continue;
            }
            if($clear) {
                $result->addNode($last);
                $clear = false;
                $start = null;
                $node = $last;
            } else {
                $result->addNode($node);
                $start = null;
            }
        }
        $result->nodes = array_reverse($result->nodes);
        return $result;
    }

    /**
     * Checks if there is enough space at the given coords and validates jump height, fall distance and entity height
     */
    protected function isSafeToWalkAt(Node $from, int $x, int &$y, int $z): bool {
        if($this->isSafeToStandAt($x, $y, $z)) {
            return true;
        }
        $jumpHeight = $this->settings->getJumpHeight();
        if($jumpHeight > 0) {
            for ($height = 1; $height <= $jumpHeight; $height++) {
                if($this->isSafeToStandAt($x, $y + $height, $z)) {
                    if(!$this->isClearShaft($from, $y + $height)) {
                        break;
                    }
                    $y += $height;
                    return true;
                }
            }
        }
        $fallDistance = $this->settings->getFallDistance();
        if($fallDistance > 0) {
            for ($distance = 1; $distance <= $fallDistance; $distance++) {
                if($this->isSafeToStandAt($x, $y - $distance, $z)) {
                    if(!$this->isClearShaft($from, $y)) {
                        break;
                    }
                    $y -= $distance;
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Only checks if the coords given are safe to stand at. Does not validate jump height and fall distance
     */
    protected function isSafeToStandAt(int $x, int $y, int $z): bool {
        if(!$this->isBlockSolid($this->getBlockAt($x, $y - 1, $z))) {
            return false;
        }
        for ($height = 0; $height <= $this->settings->getSize()->getHeight(); $height++) {
            if(!$this->isBlockPassable($this->getBlockAt($x, $y + $height, $z))) {
                return false;
            }
        }
        return true;
    }

    protected function isBlockSolid(Block $block): bool {
        return $this->settings->getPathRules()->isBlockSolid($block);
    }

    protected function isBlockPassable(Block $block): bool {
        return $this->settings->getPathRules()->isBlockPassable($block);
    }

    protected function getCostInside(Block $block): int {
        return $this->settings->getPathRules()->getCostInside($block);
    }

    protected function getCostStanding(Block $block): int {
        return $this->settings->getPathRules()->getCostStanding($block);
    }

    protected function isClearShaft(Vector3 $from, int $targetY): bool {
        $height = $this->settings->getSize()->getHeight();

        $x = $from->getFloorX();
        $z = $from->getFloorZ();

        $minY = min($from->getY(), $targetY);
        $maxY = max($from->getY(), $targetY) + $height;
        for ($y = $minY; $y <= $maxY; $y++) {
            if(!$this->isBlockPassable($this->getBlockAt($x, $y, $z))) {
                return false;
            }
        }
        return true;
    }

    protected function getLowestFCost(): ?int {
        $openList = [];
        foreach($this->openList as $hash => $node) {
            $openList[$hash] = $node->getF();
        }
        asort($openList);
        return array_key_first($openList);
    }

    protected function isClearBetweenPoints(Vector3 $vec1, Vector3 $vec2): bool {
        if($vec1->getFloorY() !== $vec2->getFloorY()) {
            return false;
        }
        $distance = $vec1->distance($vec2);
        $rayPos = clone $vec1;
        $direction = $vec2->subtractVector($vec1)->normalize();
        if($distance < $direction->length()) {
            return true;
        }
        $y = $rayPos->getFloorY();
        while($distance > $vec1->distance($rayPos)) {
            $minX = $rayPos->getX() - 0.49;
            $maxX = $rayPos->getX() + 0.49;
            $minZ = $rayPos->getZ() - 0.49;
            $maxZ = $rayPos->getZ() + 0.49;
            for ($x = $minX; $x <= $maxX; ($x += 0.5)) {
                for ($z = $minZ; $z <= $maxZ; ($z += 0.5)) {
                    if(!$this->isSafeToStandAt((int)floor($x), $y, (int)floor($z))) {
                        return false;
                    }
                }
            }
            $rayPos = $rayPos->addVector($direction);
        }
        return true;
    }
}