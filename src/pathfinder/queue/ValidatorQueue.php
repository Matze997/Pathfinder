<?php

declare(strict_types=1);

namespace pathfinder\queue;

use pathfinder\Pathfinder;
use pocketmine\scheduler\Task;
use pocketmine\world\World;
use function array_shift;

class ValidatorQueue {
    public const TYPE_CHUNK = 0;
    public const TYPE_XZ = 1;

    public const CHUNKS_PER_TICK = 1;
    public const POSITIONS_PER_TICK = 2;

    private static array $entries = [];

    public static function queueChunkValidation(int $chunkX, int $chunkZ, World $world): void {
        $hash = World::chunkHash($chunkX, $chunkZ);
        self::$entries[self::TYPE_CHUNK][$hash] = ["world" => $world->getFolderName(), "chunk" => $hash];
    }

    public static function queueChunkXZValidation(int $chunkX, int $chunkZ, World $world, int $x, int $z): void {
        $hash = World::chunkHash($chunkX, $chunkZ);
        self::$entries[self::TYPE_XZ][World::blockHash($x, 0, $z)] = ["world" => $world->getFolderName(), "chunk" => $hash, "x" => $x, "z" => $z];
    }

    public static function getEntries(int $type): array{
        return self::$entries[$type] ?? [];
    }

    public static function shiftEntry(int $type): mixed {
        if(!isset(self::$entries[$type])) return null;
        return array_shift(self::$entries[$type]);
    }

    public static function start(): void {
        Pathfinder::$instance->getScheduler()->scheduleRepeatingTask(new class() extends Task {
            public function onRun(): void{
                for($i = 1; $i <= ValidatorQueue::CHUNKS_PER_TICK; $i++) {
                    $entry = ValidatorQueue::shiftEntry(ValidatorQueue::TYPE_CHUNK);
                    if($entry === null) break;
                    ValidatorHandler::handleChunkEntry($entry);
                }

                for($i = 1; $i <= ValidatorQueue::POSITIONS_PER_TICK; $i++) {
                    $entry = ValidatorQueue::shiftEntry(ValidatorQueue::TYPE_XZ);
                    if($entry === null) break;
                    ValidatorHandler::handleXZEntry($entry);
                }
            }
        }, 1);
    }
}