<?php

declare(strict_types=1);

namespace matze\pathfinder;

use Closure;
use matze\pathfinder\debug\PathfinderCommand;
use matze\pathfinder\result\PathResult;
use matze\pathfinder\rule\Rule;
use matze\pathfinder\setting\Settings;
use matze\pathfinder\thread\AsyncPathfinderTask;
use matze\pathfinder\world\SyncFictionalWorld;
use pmmp\thread\ThreadSafeArray;
use pocketmine\math\Vector3;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\world\World;

class Pathfinder {
    private static bool $debug = false;

    public static function registerDebug(Plugin $plugin): void {
        if(self::$debug) {
            return;
        }
        self::$debug = true;

        $permission = new Permission("command.pathfinder.use");
        $permission->addChild(DefaultPermissions::ROOT_USER, true);
        DefaultPermissions::registerPermission($permission, [PermissionManager::getInstance()->getPermission(DefaultPermissions::ROOT_OPERATOR)], []);

        Server::getInstance()->getCommandMap()->register("pathfinder", new PathfinderCommand($plugin));
    }

    private bool $running = false;

    /** @var Rule[] */
    protected array $rules = [];

    public function __construct(
        array $rules,
        protected Settings $settings,
    ){
        foreach($rules as $rule) {
            $this->addRule($rule);
        }
    }

    /**
     * @return Rule[]
     */
    public function getRules(): array{
        return $this->rules;
    }

    public function addRule(Rule $rule): self {
        $this->rules[$rule::class] = $rule;
        return $this;
    }

    public function removeRule(Rule|string $rule): self{
        if($rule instanceof Rule) {
            $rule = $rule::class;
        }
        unset($this->rules[$rule]);
        return $this;
    }

    public function getRule(string $class): ?Rule {
        return $this->rules[$class] ?? null;
    }

    public function getSettings(): Settings{
        return $this->settings;
    }

    public function setSettings(Settings $settings): void{
        $this->settings = $settings;
    }

    public function findPath(Vector3 $from, Vector3 $to, World $world, float $timeout = 0.2): ?PathResult {
        $pathfinder = new BasePathfinder(new SyncFictionalWorld($world->getFolderName()), $this->settings, $timeout, $this->rules);
        return $pathfinder->findPath($from, $to);
    }

    public function findPathAsync(Vector3 $from, Vector3 $to, World $world, Closure $closure, float $timeout = 0.2, int $chunkCacheLimit = 32): void {
        Server::getInstance()->getAsyncPool()->submitTask(new AsyncPathfinderTask(World::blockHash($from->getFloorX(), $from->getFloorY(), $from->getFloorZ()), World::blockHash($to->getFloorX(), $to->getFloorY(), $to->getFloorZ()), $world->getFolderName(), $timeout, igbinary_serialize($this->settings), ThreadSafeArray::fromArray(array_map(function(Rule $rule): string {
            return igbinary_serialize($rule);
        }, $this->rules)), $chunkCacheLimit, $closure));
    }

    public function isRunning(): bool{
        return $this->running;
    }
}