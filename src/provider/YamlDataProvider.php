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

    public function accountExists(Player $player): bool
    {
        if ($this->data->exists($player->getName())) {
            return true;
        }

        return false;
    }

    public function createAccount(Player $player): void
    {
        if (!$this->data->exists($player->getName())) {
            $this->data->set($player->getName(), [
                ResinTypes::ORIGINAL_RESIN => $this->config->get("default-resin")[ResinTypes::ORIGINAL_RESIN],
                ResinTypes::FRAGILE_RESIN => $this->config->get("default-resin")[ResinTypes::FRAGILE_RESIN],
                ResinTypes::CONDENSED_RESIN => $this->config->get("default-resin")[ResinTypes::CONDENSED_RESIN],
            ]);
            $this->data->save();
        }
    }

    public function getResin(Player $player, int $resinType): int
    {
        return match ($resinType) {
            ResinTypes::ORIGINAL_RESIN => $this->data->get($player->getName())[ResinTypes::ORIGINAL_RESIN],
            ResinTypes::FRAGILE_RESIN => $this->data->get($player->getName())[ResinTypes::FRAGILE_RESIN],
            ResinTypes::CONDENSED_RESIN => $this->data->get($player->getName())[ResinTypes::CONDENSED_RESIN],
            default => throw new \InvalidArgumentException("ResinType {$resinType} not found!")
        };
    }

}