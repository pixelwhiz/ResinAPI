<?php

namespace pixelwhiz\resinapi\provider;

use pixelwhiz\resinapi\ResinAPI;
use pixelwhiz\resinapi\ResinTypes;
use pocketmine\utils\Config;

use InvalidArgumentException;

class YamlDataProvider implements Provider {

    private Config $data;
    private array $resin = [];
    private ResinAPI $plugin;
    private Config $config;

    public function __construct(ResinAPI $plugin) {
        $this->plugin = $plugin;
        $this->config = $plugin->config;
        $dataPath = $plugin->getDataFolder() . "database/data.yml";

        if (!is_dir(dirname($dataPath))) {
            mkdir(dirname($dataPath), 0777, true);
        }

        $this->data = new Config($dataPath, Config::YAML);
    }

    public function getDefaultResin(): mixed {
        return $this->config->get("default-resin");
    }

    public function accountExists(string $playerName): bool {
        return isset($this->resin[$playerName]);
    }

    public function createAccount(string $playerName): bool
    {
        if (!isset($this->resin[$playerName])) {
            $this->resin[$playerName] = [
                ResinTypes::ORIGINAL_RESIN => $this->config->get("default-resin")[ResinTypes::ORIGINAL_RESIN],
                ResinTypes::FRAGILE_RESIN => $this->config->get("default-resin")[ResinTypes::FRAGILE_RESIN],
                ResinTypes::CONDENSED_RESIN => $this->config->get("default-resin")[ResinTypes::CONDENSED_RESIN],
            ];
            return true;
        }
        return false;
    }

    public function getResin(string $playerName, string $resinType): int
    {
        if (isset($this->resin[$playerName])) {
            return match ($resinType) {
                ResinTypes::ORIGINAL_RESIN => $this->resin[$playerName][ResinTypes::ORIGINAL_RESIN],
                ResinTypes::FRAGILE_RESIN => $this->resin[$playerName][ResinTypes::FRAGILE_RESIN],
                ResinTypes::CONDENSED_RESIN => $this->resin[$playerName][ResinTypes::CONDENSED_RESIN],
                default => throw new InvalidArgumentException("ResinType {$resinType} not found!")
            };
        }
        return false;
    }

    public function getAllResin(string $playerName): array {
        return $this->resin[$playerName];
    }

    public function addResin(string $playerName, int $amount, string $resinType): bool {
        if (isset($this->resin[$playerName])) {
            $this->resin[$playerName][$resinType] += $amount;
            return true;
        }
        return false;
    }

    public function setResin(string $playerName, int $amount, string $resinType): bool {
        if (isset($this->resin[$playerName])) {
            $this->resin[$playerName][$resinType] = $amount;
            return true;
        }
        return false;
    }

    public function reduceResin(string $playerName, int $amount, string $resinType): bool {
        if (isset($this->resin[$playerName])) {
            $this->resin[$playerName][$resinType] -= $amount;
            return true;
        }
        return false;
    }


    public function getAll(): array {
        return array_keys($this->resin);
    }

    public function save(): void {
        $this->data->setAll($this->resin);
        $this->data->save();
    }

    public function open(): void {
        $this->resin = $this->data->getAll();
    }

}