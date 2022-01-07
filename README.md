# A WIP [PocketMine](https://github.com/pmmp/PocketMine-MP) Pathfinder

## Features:
- AStar pathfinding
- Pathfinding can be divided into several ticks
- Set costs for various blocks
- Navigator for entities

## API:

#### Finding a path between two points:

1. Create an [AStar-Object](https://github.com/Matze997/Pathfinder/blob/master/src/pathfinder/algorithm/astar/AStar.php)
````php
$aStar = new AStar(World $world, Vector3 $startVector3, Vector3 $targetVector3, ?AxisAlignedBB $axisAlignedBB);
````

2. Configure settings (See [Algorithm-Class](https://github.com/Matze997/Pathfinder/blob/master/src/pathfinder/algorithm/Algorithm.php) for all available methods)
````php
$aStar->setTimeout(float $timeout);
$aStar->setJumpHeight(int $jumpHeight);
...
````

3. Start and get pathfinding result
````php
$aStar->whenDone(function(?PathResult $pathResult): void {
    if($pathResult === null) {
        echo "No path found!";
        return;
    }
    echo "Path found!";
})->start();
````


## Important:
This pathfinder is not the best, but you can help to improve it. If you see code places that can be improved or any method/variable names that don't fit, please create a pull request with the respective changes.
### Thanks!

## Contact me:

[Twitter](https://twitter.com/Matze998/with_replies)  
Discord: Matze#1754
