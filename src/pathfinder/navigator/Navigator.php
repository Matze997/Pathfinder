<?php

declare(strict_types=1);

namespace pathfinder\navigator;

use pathfinder\algorithm\astar\AStar;
use pathfinder\pathpoint\PathPointManager;
use pathfinder\pathresult\PathResult;
use pathfinder\queue\ValidatorQueue;
use pocketmine\block\Slab;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\world\format\SubChunk;
use function atan2;
use function cos;
use function count;
use function deg2rad;
use function intval;
use function reset;
use function sin;

class Navigator {
    protected Entity $entity;

    protected float $speed = 0.3;

    protected ?Vector3 $targetVector3 = null;
    protected ?PathResult $pathResult = null;

    protected int $index = 0;

    protected int $jumpTicks = 0;
    protected int $stuckTicks = 0;

    protected ?Vector3 $lastVector3 = null;

    protected ?Entity $targetEntity = null;

    public function __construct(Entity $entity){
        $this->entity = $entity;
    }

    public function getEntity(): Entity{
        return $this->entity;
    }

    public function getSpeed(): float{
        return $this->speed;
    }

    public function setSpeed(float $speed): void{
        $this->speed = $speed;
    }

    public function getTargetEntity(): ?Entity{
        return $this->targetEntity;
    }

    public function setTargetEntity(?Entity $targetEntity): void{
        $this->targetEntity = $targetEntity;
    }

    public function getTargetVector3(): ?Vector3{
        return $this->targetVector3;
    }

    public function setTargetVector3(?Vector3 $targetVector3): void{
        $this->targetVector3 = $targetVector3;
        $this->pathResult = null;
        $this->index = 0;
    }

    public function onUpdate(): void {
        if($this->targetEntity !== null) {
            //TODO: Move this to behaviors
            if(
                $this->targetEntity->isClosed() ||
                !$this->targetEntity->isAlive()
            ) $this->setTargetEntity(null);

            if($this->targetEntity !== null) {
                $position = $this->targetEntity->getPosition();
                if(!$position->world->isInWorld(intval($position->x), intval($position->y), intval($position->z))) return;
                if(PathPointManager::getPathPointByPosition($position) === null) {
                    for($y = $position->getFloorY(); $y > 0; $y--) {
                        $position->y = $y;
                        if(PathPointManager::getPathPointByPosition($position) !== null) break;
                    }
                }
                if($this->targetVector3 === null || $this->targetVector3->distanceSquared($position) > 1) {
                    $this->setTargetVector3($position);
                }
            }
        }
        if($this->targetVector3 === null) return;

        $location = $this->entity->getLocation();
        if($this->pathResult === null) {
            if(!$this->entity->isOnGround()) return;
            $aStar = new AStar($this->entity->getWorld(), $location->floor(), $this->targetVector3, $this->entity->getBoundingBox());
            $aStar->setTimeout(0.05);
            $aStar->start();
            $this->pathResult = $aStar->getPathResult();
            if($this->pathResult === null) return;
            $this->index = (count($this->pathResult->getPathPoints()) - 1);
        }
        if($this->pathResult === null) return;

        $pathPoint = $this->pathResult->getPathPoint($this->index);
        if($pathPoint === null) return;

        if($location->withComponents(null, 0, null)->distanceSquared($pathPoint->withComponents(null, 0, null)) <= 0.223) {
            $pathPoint = $this->pathResult->getPathPoint(--$this->index);
            if($pathPoint === null){
                $this->setTargetVector3($this->getTargetVector3());
                return;
            }
        }
        if($this->jumpTicks > 0) $this->jumpTicks--;
        if($this->entity->isOnGround() || $this->jumpTicks === 0) {
            $motion = $this->entity->getMotion();
            if($this->jumpTicks <= 0) {
                $this->jumpTicks = -1;
                $xDist = $pathPoint->x - $location->x;
                $zDist = $pathPoint->z - $location->z;
                $yaw = atan2($zDist, $xDist) / M_PI * 180 - 90;
                if($yaw < 0) $yaw += 360.0;

                $this->entity->setRotation($yaw, 0);

                $x = -1 * sin(deg2rad($yaw));
                $z = cos(deg2rad($yaw));
                $directionVector = (new Vector3($x, 0, $z))->normalize()->multiply($this->speed);

                $motion->x = $directionVector->x;
                $motion->z = $directionVector->z;

                $lastPathPoint = $this->pathResult->getPathPoint($this->index + 1);
                if($lastPathPoint !== null) {
                    $isSlab = $location->world->getBlock($location) instanceof Slab;
                    if($this->entity->isCollidedHorizontally && ($isSlab || ($pathPoint->y - $lastPathPoint->y) > 0.5)){
                        $motion->y = ($isSlab ? 0.35 : 0.5);
                        $this->jumpTicks = ($isSlab ? 3 : 5);
                    }
                }
                $this->entity->setMotion($motion);
            }
            if($this->entity->fallDistance > 0.0) {
                $motion->x = 0;
                $motion->z = 0;
                $this->entity->setMotion($motion);
            }
        }
        if($this->lastVector3 !== null && $this->lastVector3->x === $location->x && $this->lastVector3->z === $location->z) {
            if(++$this->stuckTicks === 20){
                ValidatorQueue::queueChunkValidation($location->getFloorX() >> SubChunk::COORD_BIT_SIZE, $location->getFloorZ() >> SubChunk::COORD_BIT_SIZE, $location->getWorld());
            } elseif($this->stuckTicks > 40) {
                $this->setTargetVector3($this->getTargetVector3());
                $this->stuckTicks = 0;
            }
        } else {
            $this->stuckTicks = 0;
        }
        $this->lastVector3 = $location->asVector3();
        $this->entity->scheduleUpdate();
    }
}