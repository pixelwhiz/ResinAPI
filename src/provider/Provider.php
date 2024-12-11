<?php

namespace pixelwhiz\resinapi\provider;

use pixelwhiz\resinapi\ResinTypes;
use pocketmine\player\Player;

interface Provider {

    public function accountExists(string $playerName): bool;
    public function createAccount(string $playerName): void;
    public function getDefaultResin(): int;

    public function getResin(string $playerName, string $resinType): int;
    public function getAllResin(string $playerName): array;

    public function setResin(string $playerName, int $amount, string $resinType): void;
    public function addResin(string $playerName, int $amount, string $resinType): void;
    public function reduceResin(string $playerName, int $amount, string $resinType): void;
//
    public function getAll(): array;
//    public function save(): void;
//    public function close(): void;

}