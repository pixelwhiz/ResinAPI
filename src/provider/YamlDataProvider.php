<?php

namespace pixelwhiz\resinapi\provider;

use pixelwhiz\resinapi\ResinAPI;
use pixelwhiz\resinapi\ResinTypes;
use pocketmine\player\Player;
use pocketmine\utils\Config;

class YamlDataProvider implements Provider {

    public Config $data;

    public function __construct(ResinAPI $plugin) {
        $this->plugin = $plugin;
        $this->data = new Config(ResinAPI::getInstance()->getDataFolder(). "database/data.yml", Config::YAML);
    }

    public function getDefaultResin(): int {
        return $this->data->get("default-resin");
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