<?php

declare(strict_types=1);

/*
 *   _____           _                _____ _____
 *  |  __ \         (_)         /\   |  __ \_   _|
 *  | |__) |___  ___ _ _ __    /  \  | |__) || |
 *  |  _  // _ \/ __| | '_ \  / /\ \ |  ___/ | |
 *  | | \ \  __/\__ \ | | | |/ ____ \| |    _| |_
 *  |_|  \_\___||___/_|_| |_/_/    \_\_|   |_____|
 *
 * ResinAPI - Advanced Resin Economy System for PocketMine-MP
 * Copyright (C) 2024 pixelwhiz
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

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

/**
 * ResinAPI - Core plugin class for managing resin-based economy system in PocketMine
 *
 * @package pixelwhiz\resinapi
 * @main ResinAPI
 */

class ResinAPI extends PluginBase implements Listener {

    /**
     * Database provider instance responsible for all persistent data operations
     *
     * @var Provider $provider Handles storage and retrieval of resin data
     *                        using the configured storage backend (YAML/JSON/SQLite/MySQL)
     */
    public Provider $provider;

    /**
     * Language manager instance for handling multilingual support
     *
     * @var ResinLang $language Manages translation files and provides
     *                         localized string retrieval functionality
     */
    public ResinLang $language;

    /**
     * Primary configuration container for plugin settings
     *
     * @var Config $config Stores all runtime configuration parameters
     *                   including storage settings, resin limits, and
     *                   regeneration rules loaded from config.yml
     */
    public Config $config;

    /**
     * Singleton instance reference for global plugin access
     *
     * @var ResinAPI $instance Static reference to the active plugin instance
     *                       following the singleton pattern to ensure
     *                       single initialization and global accessibility
     */

    public static ResinAPI $instance;



    public const RET_PROVIDER_FAILURE = -5;     // Database operation failed
    public const RET_INVALID_RESIN_TYPE = -4;   // Invalid resin type specified
    public const RET_INSUFFICENT_AMOUNT = -3;   // Not enough resin available
    public const RET_INVALID_NUMBER = -2;       // Invalid numeric value provided
    public const RET_NOT_ONLINE = -1;           // Player not online
    public const RET_NO_ACCOUNT = 0;            // Player account doesn't exist
    public const RET_SUCCESS = 1;               // Operation completed successfully

    /**
     * Called when plugin loads
     * - Initializes singleton instance
     * - Loads configuration
     * - Checks for updates
     */
    protected function onLoad(): void {
        self::$instance = $this;
        $this->saveResource("config.yml");
        $this->config = new Config($this->getDataFolder(). "config.yml", Config::YAML);
        $this->checkUpdate();
    }

    /**
     * Get plugin singleton instance
     *
     * @return self
     */
    public static function getInstance(): self {
        return self::$instance;
    }

    /**
     * Called when plugin enables
     * - Initializes database and language systems
     * - Registers commands and events
     * - Starts scheduled tasks
     */
    protected function onEnable(): void {
        $this->initDatabase();
        $this->initLanguage();
        Server::getInstance()->getCommandMap()->register("resin", new ResinAPICommands($this));
        Server::getInstance()->getPluginManager()->registerEvents($this, $this);
        $this->getScheduler()->scheduleRepeatingTask(new ResinUpdateTask($this->config, $this->provider), 20);
        $this->getScheduler()->scheduleRepeatingTask(new SaveTask($this), 20);
    }

    /**
     * Placeholder for update checking functionality
     */
    public function checkUpdate(): void {}

    /**
     * Initializes database provider based on config
     *
     * @throws InvalidArgumentException If invalid provider specified
     */
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

    /**
     * Initializes language system
     * - Loads language files
     * - Sets default language
     *
     * @throws InvalidArgumentException If language file not found
     */
    public function initLanguage(): void {
        $language = $this->config->get("default-lang", "en-US");
        $languageDir = "languages/";
        $languagePath = $this->getDataFolder() . $languageDir;

        if (!is_dir($languagePath)) {
            mkdir($languagePath, 0777, true);
        }

        // Copy default language files if they don't exist
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

    /**
     * Handles player join event
     * - Creates player account if doesn't exist
     *
     * @param PlayerJoinEvent $event
     */
    public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();

        if (!$this->provider->accountExists($player->getName())) {
            $this->provider->createAccount($player->getName());
        }
    }

    /**
     * Checks if player has an account
     *
     * @param string $playerName
     * @return bool
     */
    public function hasAccount(string $playerName): bool {
        return $this->provider->accountExists($playerName);
    }

    /**
     * Checks player resin status
     *
     * @param Player|string $player
     * @return int Status code (see RET_* constants)
     */
    public function checkResin(Player|string $player): int {
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

    /**
     * Gets all resin types and amounts for a player
     *
     * @param Player|string $player
     * @return array Associative array of resin types => amounts
     */
    public function getAllResins(Player|string $player) : array {
        if ($player instanceof Player) {
            $player = $player->getName();
        }

        return [
            ResinTypes::ORIGINAL_RESIN => $this->provider->getResin($player, ResinTypes::ORIGINAL_RESIN),
            ResinTypes::CONDENSED_RESIN => $this->provider->getResin($player, ResinTypes::CONDENSED_RESIN),
            ResinTypes::FRAGILE_RESIN => $this->provider->getResin($player, ResinTypes::FRAGILE_RESIN),
        ];
    }

    /**
     * Displays resin usage invoice form to player
     *
     * @param Player $player
     * @param callable|null $onSuccess Callback when transaction succeeds
     * @return bool
     */
    public function sendInvoice(Player $player, ?callable $onSuccess = null) : bool {
        $resins = $this->getAllResins($player);
        $original_resin = $resins[ResinTypes::ORIGINAL_RESIN];
        $condensed_resin = $resins[ResinTypes::CONDENSED_RESIN];

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

    /**
     * Adds resin to player's balance
     *
     * @param Player|string $player
     * @param int $amount
     * @param string $resinType
     * @return int Status code (see RET_* constants)
     */
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

    /**
     * Sets player's resin to specific amount
     *
     * @param Player|string $player
     * @param int $amount
     * @param string $resinType
     * @return int Status code (see RET_* constants)
     */
    public function setResin($player, int $amount, string $resinType): int {
        // Input validation
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

    /**
     * Reduces player's resin balance
     *
     * @param Player|string $player
     * @param int $amount
     * @param string $resinType
     * @return int Status code (see RET_* constants)
     */
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
            if ($playerResin < $amount) {
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

    /**
     * Saves all data to persistent storage
     */
    public function saveAll(): void {
        $this->provider->save();
    }
}