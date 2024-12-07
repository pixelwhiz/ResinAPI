<?php

declare(strict_types=1);

namespace pixelwhiz\resinapi;

use pixelwhiz\resinapi\language\ResinLang;
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
use InvalidArgumentException;

class ResinAPI extends PluginBase {

    public Provider $provider;

    public ResinLang $language;

    public Config $config;

    public static ResinAPI $instance;

    protected function onLoad(): void
    {
        self::$instance = $this;

        $this->config = new Config($this->getDataFolder(). "config.yml", Config::YAML);

        $this->saveResource("config.yml");
        $this->saveResource("languages/en-US.ini");
        $this->initDatabase();
        $this->checkUpdate();
        $this->language = new ResinLang($this);

    }

    public static function getInstance(): self {
        return self::$instance;
    }

    protected function onEnable(): void
    {
        Server::getInstance()->getPluginManager()->registerEvents(new EventListener($this), $this);
        Server::getInstance()->getCommandMap()->register("resin", new ResinCommands($this));
        $this->getScheduler()->scheduleRepeatingTask(new ResinUpdateTask($this->config, $this->provider), 20);
        $this->getLogger()->info($this->language->translateToString("command.resin.description"));
    }

    public function checkUpdate(): void {}

    public function initDatabase(): void {
        $provider = $this->config->get("provider");

        $this->provider = match ($provider) {
            "yaml" => new YamlDataProvider($this),
            "json" => new JsonDataProvider($this),
            "sqlite" => new SqliteDataProvider($this),
            "mysql" => new MySqlDataProvider($this),
            default => throw new InvalidArgumentException("Unsupported provider: $provider")
        };
    }

    public function initLanguage(): void {
        $language = $this->config->get("default-language");

    }

    public function getProvider(): Provider {
        return $this->provider;
    }

    public function addResin(Player $player, int $amount, int $resinType): void {

    }


}
