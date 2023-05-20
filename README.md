# A WIP [PocketMine](https://github.com/pmmp/PocketMine-MP) Pathfinder

# VIRION IS CURRENTLY IN ALPHA PHASE!

## Features:
- Async Pathfinding
- Sync Pathfinding
- Path smoothing
- Easy to use API

### About asynchronus pathfinding:
Async pathfinding is always the method I recommend due to it´s huge performance benefit. But you have to consider that pathfinding on another thread can lead to wrong paths due to block updates during pathfinding.
Additionally, async pathfinding takes a bit longer then running the pathfinder on the main thread (Depends on the distance and chunks required to compute the path). But on the other hand the main thread doesn´t even recognize any path computation.
You also consider adding a queue if many paths have to be calculated, otherwise stuff like chunk loading & packet compression will take longer.

## Code examples:

#### Finding a path between two points (Sync):
```php
//Initialize settings
$settings = \matze\pathfinder\setting\Settings::get()
    ->setJumpHeight(1)
    ->setFallDistance(2)
    ->setTimeout(0.05);
    
$start = new \pocketmine\math\Vector3(0, 100, 0);
$target = new \pocketmine\math\Vector3(10, 100, 0);

//Create object
$pathfinder = new \matze\pathfinder\type\SyncPathfinder($settings, $player->getWorld());

//Run findPath method and set closure for handling the path result
$result = $pathfinder->findPath($start, $target);
if($result === null) {
    //Path not found
} else {
    //Path found
}
```

#### Finding a path between two points (Async):

```php
//Initialize settings
$settings = \matze\pathfinder\setting\Settings::get()
    ->setJumpHeight(1)
    ->setFallDistance(2)
    ->setTimeout(1.0);
    
$start = new \pocketmine\math\Vector3(0, 100, 0);
$target = new \pocketmine\math\Vector3(10, 100, 0);

//Create object
$pathfinder = new \matze\pathfinder\type\AsyncPathfinder($settings, $player->getWorld());

//Run findPath method and set closure for handling the path result
$pathfinder->findPath($start, $target, function (?\matze\pathfinder\result\PathResult $result): void {
    if($result === null) {
        //Path not found
    } else {
        //Path found
    }
});
```

## TODO:
- Restructure everything (It´s a bit messy atm)

#### You´re always welcome to contribute!

## Contact me:
Discord: Matze#1754