<?php


namespace pixelwhiz\resinapi\task;

use pixelwhiz\resinapi\provider\Provider;
use pixelwhiz\resinapi\ResinAPI;
use pixelwhiz\resinapi\ResinTypes;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\Config;

class ResinUpdateTask extends Task {

    public int $updateTime;

    public function __construct(Config $config, Provider $provider) {
        $this->config = $config;
        $this->provider = $provider;
        $this->updateTime = 60 * $config->get("interval-to-update");
    }

    public function onRun(): void {

        $this->updateTime--;

        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if ($this->updateTime === 0) {
                ResinAPI::getInstance()->addResin($player, 1, ResinTypes::OIRIGINAL_RESIN);
                $this->updateTime = 60 * $this->config->get("interval-to-update");
            }
        }

    }

}