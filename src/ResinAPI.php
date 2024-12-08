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
        $this->saveResource("config.yml");
        $this->config = new Config($this->getDataFolder(). "config.yml", Config::YAML);
        $this->checkUpdate();
    }

    public static function getInstance(): self {
        return self::$instance;
    }

    protected function onEnable(): void
    {
        $this->initDatabase();
        $this->initLanguage();
        Server::getInstance()->getPluginManager()->registerEvents(new EventListener($this), $this);
        Server::getInstance()->getCommandMap()->register("resin", new ResinCommands($this));
        $this->getScheduler()->scheduleRepeatingTask(new ResinUpdateTask($this->config, $this->provider), 20);
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
        $language = $this->config->get("default-lang", "en-US");
        $languageDir = "languages/";
        $languagePath = $this->getDataFolder() . $languageDir;

        if (!is_dir($languagePath)) {
            mkdir($languagePath, 0777, true);
        }

        foreach (scandir($this->getFile() . "resources/" . $languageDir) as $file) {
            if ($file !== "." && $file !== ".." && pathinfo($file, PATHINFO_EXTENSION) === "ini") {
                if (!file_exists($languagePath . $file)) {
                    $this->saveResource($languageDir . $file);
                }
            }
        }

        $languageFile = $languagePath . "{$language}.ini";

        if (!file_exists($languageFile)) {
            throw new InvalidArgumentException("Language file for '{$language}' not found in '{$languagePath}'");
        }

        $this->language = new ResinLang($this);
    }


    public function getProvider(): Provider {
        return $this->provider;
    }

    public function addResin(Player $player, int $amount, int $resinType): void {

    }


}
