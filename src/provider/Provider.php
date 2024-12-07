<?php

namespace pixelwhiz\resinapi\provider;

use pocketmine\player\Player;

interface Provider {

    public function accountExists(Player $player): bool;
    public function createAccount(Player $player, int $defaultResin): void;
    public function getDefaultResin(): int;

    public function getResin(Player $player, int $resinType): int;
//    public function setResin(Player $player, int $amount, int $resinType): void;
//    public function addResin(Player $player, int $amount, int $resinType): void;
//    public function reduceResin(Player $player, int $amount, int $resinType): void;
//
//    public function getAll(): void;
//    public function save(): void;
//    public function close(): void;

}