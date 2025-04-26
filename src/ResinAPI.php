<?php

declare(strict_types=1);

namespace pixelwhiz\resinapi;

use pixelwhiz\resinapi\libs\jojoe77777\FormAPI\SimpleForm;
use pixelwhiz\resinapi\language\ResinLang;
use pixelwhiz\resinapi\provider\Provider;
use pixelwhiz\resinapi\commands\ResinAPICommands;
use pixelwhiz\resinapi\provider\JsonDataProvider;
use pixelwhiz\resinapi\provider\MySqlDataProvider;
use pixelwhiz\resinapi\provider\SqliteDataProvider;
use pixelwhiz\resinapi\provider\YamlDataProvider;
use pixelwhiz\resinapi\task\ResinUpdateTask;
use pixelwhiz\resinapi\task\SaveTask;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use InvalidArgumentException;

class ResinAPI extends PluginBase implements Listener {

    public Provider $provider;

    public ResinLang $language;

    public Config $config;

    public static ResinAPI $instance;

    public const RET_PROVIDER_FAILURE = -5;
    public const RET_INVALID_RESIN_TYPE = -4;
    public const RET_INSUFFICENT_AMOUNT = -3;
    public const RET_INVALID_NUMBER = -2;
    public const RET_NOT_ONLINE = -1;
    public const RET_NO_ACCOUNT = 0;
    public const RET_SUCCESS = 1;

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
        Server::getInstance()->getCommandMap()->register("resin", new ResinAPICommands($this));
        Server::getInstance()->getPluginManager()->registerEvents($this, $this);
        $this->getScheduler()->scheduleRepeatingTask(new ResinUpdateTask($this->config, $this->provider), 20);
        $this->getScheduler()->scheduleRepeatingTask(new SaveTask($this), 20);
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

        $this->provider->open();
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

    public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();

        if (!$this->provider->accountExists($player->getName())) {
            $this->provider->createAccount($player->getName());
        }
    }

    public function hasAccount(string $playerName): bool {
        return $this->provider->accountExists($playerName);
    }

    public function checkResin(Player|string $player): int
    {
        $playerName = $player instanceof Player ? $player->getName() : (string)$player;

        if ($this->hasAccount($playerName)) {

            $player = Server::getInstance()->getPlayerExact($playerName);
            if (!$player) {
                return self::RET_NOT_ONLINE;
            }

            return self::RET_SUCCESS;
        }

        return self::RET_NO_ACCOUNT;
    }

    public function getAllResins(Player|string $player) : array {
        if ($player instanceof Player) {
            $player = $player->getName();
        }

        $resins = [
            ResinTypes::ORIGINAL_RESIN => $this->provider->getResin($player, ResinTypes::ORIGINAL_RESIN),
            ResinTypes::CONDENSED_RESIN => $this->provider->getResin($player, ResinTypes::CONDENSED_RESIN),
            ResinTypes::FRAGILE_RESIN => $this->provider->getResin($player, ResinTypes::FRAGILE_RESIN),
        ];

        return $resins;
    }

    public function sendInvoice(Player $player, ?callable $onSuccess = null) : bool {
        $original_resin = $this->getAllResins($player)[ResinTypes::ORIGINAL_RESIN];
        $condensed_resin = $this->getAllResins($player)[ResinTypes::CONDENSED_RESIN];

        $form = new SimpleForm(function (Player $formPlayer, $data) use($player, $original_resin, $condensed_resin, $onSuccess) {
            if ($data === null) {
                return false;
            }

            $success = false;
            $resinType = null;
            $amount = 0;

            switch ($data) {
                case 0:
                    $resinType = ResinTypes::ORIGINAL_RESIN;
                    $amount = 40;
                    if ($original_resin >= $amount) {
                        $this->provider->reduceResin($player->getName(), $amount, $resinType);
                        $success = true;
                    } else {
                        $player->sendMessage("§cYou dont have enough original resin to Open!");
                    }
                    break;
                case 1:
                    $resinType = ResinTypes::CONDENSED_RESIN;
                    $amount = 1;
                    if ($condensed_resin >= $amount) {
                        $this->provider->reduceResin($player->getName(), $amount, $resinType);
                        $success = true;
                    } else {
                        $player->sendMessage("§cYou dont have enough condensed resin to Open!");
                    }
                    break;
            }

            if ($success && $onSuccess) {
                $onSuccess($player, $resinType, $amount);
            }

            return $success;
        });

        $form->setTitle("Resin Invoice");
        $form->addButton("Open 40 Original Resin");
        $form->addButton("Open 1 Condensed Resin");
        $form->addButton("Close", 0, "textures/blocks/barrier");

        $form->sendToPlayer($player);
        return true;
    }

    public function addResin($player, int $amount, string $resinType): int {
        if ($amount <= 0 || !is_numeric($amount)) {
            return self::RET_INVALID_NUMBER;
        }

        if (!in_array($resinType, ResinTypes::$allResin)) {
            return self::RET_INVALID_RESIN_TYPE;
        }

        if (!isset($this->config->get("max-resin")[$resinType])) {
            return self::RET_PROVIDER_FAILURE;
        }

        $playerName = $player instanceof Player ? $player->getName() : (string)$player;

        if ($this->provider->getResin($playerName, $resinType) !== false) {
            $playerResin = $this->provider->getResin($playerName, $resinType);
            if ($playerResin + $amount > $this->config->get("max-resin")[$resinType]) {
                return self::RET_INSUFFICENT_AMOUNT;
            }

            $player = Server::getInstance()->getPlayerExact($playerName);
            if (!$player) {
                return self::RET_NOT_ONLINE;
            }

            $this->provider->addResin($playerName, $amount, $resinType);
            return self::RET_SUCCESS;
        }

        return self::RET_NO_ACCOUNT;
    }


    public function setResin($player, int $amount, string $resinType): int {
        if ($amount <= 0 or !is_numeric($amount)) {
            return self::RET_INVALID_NUMBER;
        }

        if (!in_array($resinType, ResinTypes::$allResin)) {
            return self::RET_INVALID_RESIN_TYPE;
        }

        if (!isset($this->config->get("max-resin")[$resinType])) {
            return self::RET_PROVIDER_FAILURE;
        }

        $playerName = $player instanceof Player ? $player->getName() : (string)$player;

        if ($this->provider->getResin($playerName, $resinType) !== false) {
            if ($amount > $this->config->get("max-resin")[$resinType]) {
                return self::RET_INSUFFICENT_AMOUNT;
            }

            $player = Server::getInstance()->getPlayerExact($playerName);
            if (!$player) {
                return self::RET_NOT_ONLINE;
            }

            $this->provider->setResin($playerName, $amount, $resinType);
            return self::RET_SUCCESS;
        }

        return self::RET_NO_ACCOUNT;
    }

    public function reduceResin($player, int $amount, string $resinType): int {
        if ($amount <= 0 || !is_numeric($amount)) {
            return self::RET_INVALID_NUMBER;
        }

        if (!in_array($resinType, ResinTypes::$allResin)) {
            return self::RET_INVALID_RESIN_TYPE;
        }

        $playerName = $player instanceof Player ? $player->getName() : (string)$player;

        if ($this->provider->getResin($playerName, $resinType) !== false) {
            $playerResin = $this->provider->getResin($playerName, $resinType);
            if ($playerResin - $amount > 0) {
                return self::RET_INSUFFICENT_AMOUNT;
            }

            $player = Server::getInstance()->getPlayerExact($playerName);
            if (!$player) {
                return self::RET_NOT_ONLINE;
            }

            $this->provider->reduceResin($playerName, $amount, $resinType);
            return self::RET_SUCCESS;
        }

        return self::RET_NO_ACCOUNT;
    }

    public function saveAll(): void {
        $this->provider->save();
    }

}