<?php

namespace pixelwhiz\resinapi\provider;

use pixelwhiz\resinapi\ResinAPI;
use pixelwhiz\resinapi\ResinTypes;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class YamlDataProvider implements Provider {

    private Config $data;
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

    public function getDefaultResin(): int {
        return $this->data->get("default-resin");
    }

    public function accountExists(string $playerName): bool
    {
        if ($this->data->exists($playerName)) {
            return true;
        }

        return false;
    }

    public function createAccount(string $playerName): void
    {
        if (!$this->data->exists($playerName)) {
            $this->data->set($playerName, [
                ResinTypes::ORIGINAL_RESIN => $this->config->get("default-resin")[ResinTypes::ORIGINAL_RESIN],
                ResinTypes::FRAGILE_RESIN => $this->config->get("default-resin")[ResinTypes::FRAGILE_RESIN],
                ResinTypes::CONDENSED_RESIN => $this->config->get("default-resin")[ResinTypes::CONDENSED_RESIN],
            ]);
            $this->data->save();
        }
    }

    public function getResin(string $playerName, string $resinType): int
    {
        return match ($resinType) {
            ResinTypes::ORIGINAL_RESIN => $this->data->get($playerName)[ResinTypes::ORIGINAL_RESIN],
            ResinTypes::FRAGILE_RESIN => $this->data->get($playerName)[ResinTypes::FRAGILE_RESIN],
            ResinTypes::CONDENSED_RESIN => $this->data->get($playerName)[ResinTypes::CONDENSED_RESIN],
            default => throw new \InvalidArgumentException("ResinType {$resinType} not found!")
        };
    }

    public function getAllResin(string $playerName): array {
        return $this->data->get($playerName);
    }

    public function addResin(string $playerName, int $amount, string $resinType): void {
        $playerData = $this->data->get($playerName);
        if (is_array($playerData)) {
            $resin = $playerData[$resinType] ?? 0;
            $this->data->set($playerName, array_merge($playerData, [$resinType => $resin + $amount]));
            $this->data->save();
        } else {
            $this->data->set($playerName, [
                'Original Resin' => 0,
                'Fragile Resin' => 0,
                'Condensed Resin' => 0,
            ]);
            $this->data->save();
            $this->addResin($playerName, $amount, $resinType);
        }
    }


    public function getAll(): array
    {
        return $this->data->getAll(true);
    }

}