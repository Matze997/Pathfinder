# A WIP [PocketMine](https://github.com/pmmp/PocketMine-MP) Pathfinder

# VIRION IS CURRENTLY IN ALPHA PHASE!

## TO-DO:
- More default rules (Help is appreciated!)

## Features:
- Async Pathfinding
- Sync Pathfinding
- Path smoothing
- Easy to use API

### About asynchron pathfinding:
Async pathfinding is always the method I recommend due to it´s huge performance benefit. But you have to consider that pathfinding on another thread can lead to wrong paths due to block updates during pathfinding.
Additionally, async pathfinding takes a bit longer then running the pathfinder on the main thread (Depends on the distance and chunks required to compute the path). But on the other hand the main thread doesn´t even recognize any path computation.
You also consider adding a queue if many paths have to be calculated, otherwise stuff like chunk loading & packet compression will take longer.

## Code examples:

#### Finding a path between two points:
```php
// Set some settings
$settings = \matze\pathfinder\setting\Settings::get()
    ->setPathSmoothing(false)
    ->setMaxTravelDistanceDown(2)
    ->setMaxTravelDistanceUp(1);

// Initialize pathfinder
$pathfinder = new \matze\pathfinder\Pathfinder([
    new \matze\pathfinder\rule\default\ChickenRule(\matze\pathfinder\rule\Rule::PRIORITY_NORMAL),//Define rules and set priorities
], $settings);

// Find path synchron
$result = $pathfinder->findPath($from, $to, $world, $timeout);
if($result === null) {
    //Path not found
} else {
    //Path found
}

// Find path asynchron
$pathfinder->findPathAsync($from, $to, $world, function(?\matze\pathfinder\result\PathResult $result): void {
    if($result === null) {
        //Path not found
    } else {
        //Path found
    }
}, $timeout);
```

#### You´re always welcome to contribute!

## Contact me:
Discord: matze998