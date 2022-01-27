<?php
declare(strict_types=1);
namespace pathfinder\algorithm\navigator\handler;
use pathfinder\algorithm\navigator\Navigator;
use pathfinder\algorithm\path\PathPoint;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\block\utils\SlabType;
use pocketmine\math\Vector3;

use function atan2;
use function cos;
use function deg2rad;
use function sin;


class DefaultMovementHandler extends MovementHandler{
	public function handle(Navigator $navigator, PathPoint $pathPoint): void{
		$entity = $navigator->getEntity();
		$location = $entity->getLocation();
		$jumpTicks = $navigator->getJumpTicks();
		$speed = $navigator->getSpeed();
		if ($entity->isOnGround() || $jumpTicks === 0) {
			$motion = $entity->getMotion();
			if ($jumpTicks <= 0) {
				$navigator->resetJumpTicks(-1);
				$xDist = $pathPoint->x - $location->x;
				$zDist = $pathPoint->z - $location->z;
				$yaw = atan2($zDist, $xDist) / M_PI * 180 - 90;
				if ($yaw < 0)
					$yaw += 360.0;
				$entity->setRotation($yaw, 0);
				$x = -1 * sin(deg2rad($yaw));
				$z = cos(deg2rad($yaw));
				$directionVector = (new Vector3($x, 0, $z))->normalize()->multiply($speed);
				$motion->x = $directionVector->x;
				$motion->z = $directionVector->z;
				$lastPathPoint = $navigator->getPathResult()->getPathPoint($navigator->getIndex() + 1);
				if ($lastPathPoint !== null) {
					if ($entity->isCollidedHorizontally) {
						$block = $location->getWorld()->getBlock($location);
						$isFullBlock = false;
						if (!$block instanceof Slab && !$block instanceof Stair) {
							$facing = $entity->getHorizontalFacing();
							$frontBlock = $location->getWorld()->getBlock($location->add(0, 0.5, 0)->getSide($facing));
							if (!$frontBlock->canBeFlowedInto()) {
								if ((!$frontBlock instanceof Slab || $frontBlock->getSlabType()->equals(SlabType::TOP()) || $frontBlock->getSlabType()->equals(SlabType::DOUBLE())) && (!$frontBlock instanceof Stair || $frontBlock->isUpsideDown() || $frontBlock->getFacing() !== $facing)) {
									$motion->y = 0.42 + $this->gravity;
									$navigator->resetJumpTicks(5);
									$isFullBlock = true;
								}
							} else {
								$isFullBlock = true;
							}
						}
						if (!$isFullBlock) {
							$motion->y = 0.3 + $this->gravity;
							$navigator->resetJumpTicks(2);
						}
						if ($motion->y > 0) {
							$motion->x /= 3;
							$motion->z /= 3;
						}
					}
				}
				$entity->setMotion($motion);
			}
			if ($entity->fallDistance > 0.0) {
				$motion->x = 0;
				$motion->z = 0;
				$entity->setMotion($motion);
			}
		}
	}
}