<?php

declare(strict_types=1);

namespace pathfinder\algorithm\astar;

use pathfinder\algorithm\Algorithm;
use pathfinder\pathpoint\PathPointManager;
use pathfinder\pathresult\PathResult;
use pocketmine\math\Vector3;
use pocketmine\world\Position;
use function array_key_first;
use function asort;

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
        $axisAlignedBB = $this->getAxisAlignedBB();
        $world = $this->getWorld();

        $startNode = Node::fromVector3($this->startVector3);
        $startNode->setG(0.0);
        $startNode->setH($this->calculateHCost($startNode));

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
            $currentPathPoint = PathPointManager::getPathPointByPosition(Position::fromObject($currentNode, $world));
            if($currentPathPoint === null){
                for($y = -$this->getFallDistance(); $y <= $this->getJumpHeight(); $y++) {
                    $currentPathPoint = PathPointManager::getPathPointByPosition(Position::fromObject($currentNode->add(0, $y, 0), $world));
                    if($currentPathPoint !== null){
                        $currentNode->y = $currentPathPoint->y;
                        break;
                    }
                }
                if($currentPathPoint === null) break;
            }
            foreach(self::SIDES as $SIDE) {
                $side = $currentNode->add($SIDE[0], 0, $SIDE[1]);
                $sidePathPoint = PathPointManager::getPathPointByPosition(Position::fromObject($side, $world));

                if($sidePathPoint === null){
                    if($SIDE[0] !== 0 && $SIDE[1] !== 0) continue;
                    for($y = -$this->getFallDistance(); $y <= $this->getJumpHeight(); $y++) {
                        $sidePathPoint = PathPointManager::getPathPointByPosition(Position::fromObject($side->add(0, $y, 0), $world));
                        if($sidePathPoint !== null && $sidePathPoint->isCollisionFreeToStand($world, $axisAlignedBB)) break;
                    }
                    if($sidePathPoint === null) continue;
                }
                $sideNode = Node::fromVector3($sidePathPoint);
                if(isset($this->closedList[$sideNode->getHash()]) || !$sidePathPoint->isCollisionFreeToStand($world, $axisAlignedBB)) {
                    continue;
                }

                $cost = $sidePathPoint->getCost();
                if(!isset($this->openList[$sideNode->getHash()]) || $currentNode->getG() + $cost < $sideNode->getG()) {
                    $sideNode->setG($currentNode->getG() + $cost);
                    $sideNode->setH($this->calculateHCost($sidePathPoint));
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
                $pathPoint = PathPointManager::getPathPointByPosition(Position::fromObject($node, $world));
                if($pathPoint === null) return null;
                $pathResult->addPathPoint($pathPoint);
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
}