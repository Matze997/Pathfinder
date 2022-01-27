<?php
declare(strict_types=1);
namespace pathfinder\algorithm\navigator\handler;
use pathfinder\algorithm\navigator\Navigator;
use pathfinder\algorithm\path\PathPoint;


abstract class MovementHandler{
	protected float $gravity = 0.08;

	public function getGravity(): float{
		return $this->gravity;
	}

	public function setGravity(float $gravity): void{
		$this->gravity = $gravity;
	}

	abstract public function handle(Navigator $navigator, PathPoint $pathPoint): void;
}