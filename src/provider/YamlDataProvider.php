<?php

namespace pixelwhiz\resinapi\provider;

use pixelwhiz\resinapi\ResinAPI;
use pixelwhiz\resinapi\ResinTypes;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class YamlDataProvider implements Provider {

    private Config $data;
    private ResinAPI $plugin;

    public function __construct(ResinAPI $plugin) {
        $this->plugin = $plugin;
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
        // TODO: Implement accountExists() method.
    }

    public function createAccount(Player $player, int $defaultResin): void
    {
        // TODO: Implement createAccount() method.
    }



    public function getResin(Player $player, int $resinType): int
    {
        return match ($resinType) {
            ResinTypes::OIRIGINAL_RESIN => $this->data->get($player->getName())[ResinTypes::OIRIGINAL_RESIN],
            ResinTypes::FRAGILE_RESIN => $this->data->get($player->getName())[ResinTypes::FRAGILE_RESIN],
            ResinTypes::CONDENSED_RESIN => $this->data->get($player->getName())[ResinTypes::CONDENSED_RESIN],
            default => throw new \InvalidArgumentException("ResinType {$resinType} not found!")
        };
    }

}