<?php

declare(strict_types=1);

namespace pixelwhiz\resinapi;

use pixelwhiz\resinapi\provider\Provider;
use pixelwhiz\resinapi\commands\ResinCommands;
use pixelwhiz\resinapi\provider\JsonDataProvider;
use pixelwhiz\resinapi\provider\MySqlDataProvider;
use pixelwhiz\resinapi\provider\SqliteDataProvider;
use pixelwhiz\resinapi\provider\YamlDataProvider;
use pixelwhiz\resinapi\task\ResinUpdateTask;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;

class ResinAPI extends PluginBase {

    public Provider $provider;

    public Config $config;

    public static ResinAPI $instance;

    public static function getInstance(): ResinAPI {
        return self::$instance;
    }

    protected function onEnable(): void
    {
        Server::getInstance()->getPluginManager()->registerEvents(new EventListener($this), $this);
        Server::getInstance()->getCommandMap()->register("resin", new ResinCommands($this));
        $this->getScheduler()->scheduleRepeatingTask(new ResinUpdateTask($this->config, $this->provider), 20);
        $this->initDatabase();
        $this->checkUpdate();
        $this->config = new Config($this->getDataFolder(). "config.yml", Config::YAML);
    }

    public function checkUpdate(): void {}

    public function initDatabase(): void {
        $provider = $this->config->get("provider");

        $this->provider = match ($provider) {
            "yaml" => new YamlDataProvider($this),
            "json" => new JsonDataProvider($this),
            "sqlite" => new SqliteDataProvider($this),
            "mysql" => new MySqlDataProvider($this),
            default => throw new \InvalidArgumentException("Unsupported provider: $provider")
        };
    }

    public function getProvider(): Provider {
        return $this->provider;
    }

    public function addResin(Player $player, int $amount, int $resinType): void {

    }


}
