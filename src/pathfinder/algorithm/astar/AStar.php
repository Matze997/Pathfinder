<?php

declare(strict_types=1);

namespace pathfinder\algorithm\astar;

use pathfinder\algorithm\Algorithm;
use pathfinder\cost\CostCalculator;
use pathfinder\pathpoint\PathPoint;
use pathfinder\pathresult\PathResult;
use pocketmine\block\BaseRail;
use pocketmine\block\Block;
use pocketmine\block\Lava;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\math\Vector3;
use function abs;
use function array_key_first;
use function asort;
use function ceil;

class AStar extends Algorithm {
    public const SIDES = [
        [0, 1],
        [1, 0],
        [0, -1],
        [-1, 0],

        [1, 1],
        [-1, 1],
        [1, -1],
        [-1, -1]
    ];

    /** @var Node[]  */
    private array $openList = [];
    /** @var Node[]  */
    private array $closedList = [];

    protected function run(): ?PathResult{
        $world = $this->getWorld();

        $startNode = Node::fromVector3($this->startVector3);
        $startNode->setG(0.0);
        $startNode->setH($this->calculateHCost($startNode));

        $startBlock = $world->getBlock($startNode);
        if($startBlock instanceof Slab || $startBlock instanceof Stair) {
            $startNode->y++;
        }

        $targetNode = Node::fromVector3($this->targetVector3);

        $this->openList[$startNode->getHash()] = $startNode;
        $currentNode = null;
        while($this->checkTimout()) {
            $key = $this->getLowestFCost();
            if($key === null) break;
            $currentNode = $this->openList[$key];

            unset($this->openList[$currentNode->getHash()]);
            $this->closedList[$currentNode->getHash()] = $currentNode;

            if($currentNode->getHash() === $targetNode->getHash()) {//Path found
                $targetNode->setParentNode($currentNode);
                break;
            }

            foreach(self::SIDES as $SIDE) {
                $side = $currentNode->add($SIDE[0], 0, $SIDE[1]);
                if(!$this->isSafeToStandAt($side)){
                    if($SIDE[0] !== 0 && $SIDE[1] !== 0) continue;

                    //Jump Height Check
                    $success = false;
                    for($y = 0; $y <= $this->getJumpHeight(); ++$y) {
                        if(!$this->isSafeToStandAt($side->add(0, $y, 0))) continue;
                        $side->y += $y;
                        $success = true;
                        break;
                    }
                    if(!$success) {
                        //Fall Distance Check
                        $success = false;
                        for($y = 0; $y <= $this->getFallDistance(); ++$y) {
                            if(!$this->isSafeToStandAt($side->subtract(0, $y, 0))) continue;
                            $side->y -= $y;
                            $success = true;
                            break;
                        }
                        if(!$success) continue;
                    }
                }

                $sideNode = Node::fromVector3($side);
                if(isset($this->closedList[$sideNode->getHash()])) {
                    continue;
                }

                $cost = CostCalculator::getCost($world->getBlock($side->subtract(0, 1, 0))->getFullId());
                if(!isset($this->openList[$sideNode->getHash()]) || $currentNode->getG() + $cost < $sideNode->getG()) {
                    $sideNode->setG($currentNode->getG() + $cost);
                    $sideNode->setH($this->calculateHCost($side));
                    $sideNode->setParentNode($currentNode);
                    if(!isset($this->openList[$sideNode->getHash()])) {
                        $this->openList[$sideNode->getHash()] = $sideNode;
                    }
                }
            }
        }
        if($currentNode === null) return null;
        $node = $targetNode->getParentNode();
        if($node === null){
            $key = $this->getLowestFCost();
            if($key === null) return null;
            $node = $this->openList[$key];
        }
        $pathResult = new PathResult($world, $this->startVector3, $this->targetVector3);
        while(true) {
            $node = $node->getParentNode();
            if($node instanceof Node) {
                $pathResult->addPathPoint(new PathPoint($node->x, $node->y, $node->z));
                continue;
            }
            break;
        }
        return $pathResult->finish();
    }

    private function getLowestFCost(): ?int {
        $openList = [];
        foreach($this->openList as $hash => $node) {
            $openList[$hash] = $node->getF();
        }
        asort($openList);
        return array_key_first($openList);
    }

    private function calculateHCost(Vector3 $pos): float{
        $targetPos = $this->getTargetVector3();
        return abs($pos->x - $targetPos->x) + abs($pos->y - $targetPos->y) + abs($pos->z - $targetPos->z);
    }

    private function isBlockEmpty(Block $block): bool {
        return !$block->isSolid() && !$block instanceof BaseRail && !$block instanceof Lava;
    }

    private function isSafeToStandAt(Vector3 $vector3): bool {
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