<?php


namespace pixelwhiz\resinapi\task;

use pixelwhiz\resinapi\provider\Provider;
use pixelwhiz\resinapi\ResinTypes;
use pocketmine\scheduler\Task;
use pocketmine\utils\Config;

class ResinUpdateTask extends Task {

    public int $updateTime;

    private Config $config;
    private Provider $provider;

    public function __construct(Config $config, Provider $provider) {
        $this->config = $config;
        $this->provider = $provider;
        $this->updateTime = 60 * $config->get("interval-to-update");
    }

    public function onRun(): void {

        $this->updateTime--;

        foreach ($this->provider->getAll() as $playerName) {
            if ($this->updateTime === 0) {
                if ($this->provider->getResin($playerName, ResinTypes::ORIGINAL_RESIN) < $this->config->get("max-resin")[ResinTypes::ORIGINAL_RESIN]) {
                    $this->provider->addResin($playerName, 1, ResinTypes::ORIGINAL_RESIN);
                    $this->updateTime = 60 * $this->config->get("interval-to-update");
                }
            }
        }
    }


}