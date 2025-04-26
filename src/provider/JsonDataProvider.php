<?php

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

namespace pixelwhiz\resinapi\provider;

use pixelwhiz\resinapi\ResinAPI;
use pixelwhiz\resinapi\ResinTypes;
use pocketmine\utils\Config;
use InvalidArgumentException;

/**
 * JSON Data Provider - Handles persistent storage of player resin data in JSON format
 *
 * Implements the Provider interface to store and manage player resin data
 * using JSON files for persistence. Maintains an in-memory cache of data
 * for quick access and periodically saves to disk.
 *
 * @package pixelwhiz\resinapi\provider
 * @implements Provider
 */
class JsonDataProvider implements Provider {

    /**
     * Configuration instance for JSON data storage
     * @var Config
     */
    private Config $data;

    /**
     * In-memory cache of player resin data
     * Structure: [playerName => [resinType => amount]]
     * @var array
     */
    private array $resin = [];

    /**
     * Main plugin instance reference
     * @var ResinAPI
     */
    private ResinAPI $plugin;

    /**
     * Plugin configuration container
     * @var Config
     */
    private Config $config;

    /**
     * Constructs a new JSON data provider instance
     *
     * @param ResinAPI $plugin Main plugin instance
     */
    public function __construct(ResinAPI $plugin) {
        $this->plugin = $plugin;
        $this->config = $plugin->config;
        $dataPath = $plugin->getDataFolder() . "database/data.json";

        // Ensure data directory exists
        if (!is_dir(dirname($dataPath))) {
            mkdir(dirname($dataPath), 0777, true);
        }

        $this->data = new Config($dataPath, Config::JSON);
    }

    /**
     * Gets default resin values from configuration
     *
     * @return array Array of default resin amounts by type
     */
    public function getDefaultResin(): array {
        return $this->config->get("default-resin");
    }

    /**
     * Checks if a player account exists
     *
     * @param string $playerName Name of player to check
     * @return bool True if account exists, false otherwise
     */
    public function accountExists(string $playerName): bool {
        return isset($this->resin[$playerName]);
    }

    /**
     * Creates a new player account with default resin values
     *
     * @param string $playerName Name of player to create account for
     * @return bool True if account was created, false if it already existed
     */
    public function createAccount(string $playerName): bool {
        if (!isset($this->resin[$playerName])) {
            $this->resin[$playerName] = [
                ResinTypes::ORIGINAL_RESIN => $this->config->get("default-resin")[ResinTypes::ORIGINAL_RESIN],
                ResinTypes::FRAGILE_RESIN => $this->config->get("default-resin")[ResinTypes::FRAGILE_RESIN],
                ResinTypes::CONDENSED_RESIN => $this->config->get("default-resin")[ResinTypes::CONDENSED_RESIN],
            ];
            return true;
        }
        return false;
    }

    /**
     * Gets a player's resin amount of specific type
     *
     * @param string $playerName Name of player
     * @param string $resinType Type of resin to get
     * @return int Amount of resin
     * @throws InvalidArgumentException If invalid resin type is provided
     */
    public function getResin(string $playerName, string $resinType): int {
        if (isset($this->resin[$playerName])) {
            return match ($resinType) {
                ResinTypes::ORIGINAL_RESIN => $this->resin[$playerName][ResinTypes::ORIGINAL_RESIN],
                ResinTypes::FRAGILE_RESIN => $this->resin[$playerName][ResinTypes::FRAGILE_RESIN],
                ResinTypes::CONDENSED_RESIN => $this->resin[$playerName][ResinTypes::CONDENSED_RESIN],
                default => throw new InvalidArgumentException("ResinType {$resinType} not found!")
            };
        }
        return 0;
    }

    /**
     * Gets all resin amounts for a player
     *
     * @param string $playerName Name of player
     * @return array Array of all resin types and amounts
     */
    public function getAllResin(string $playerName): array {
        return $this->resin[$playerName] ?? [];
    }

    /**
     * Adds resin to a player's balance
     *
     * @param string $playerName Name of player
     * @param int $amount Amount to add
     * @param string $resinType Type of resin to add
     * @return bool True if successful, false if player doesn't exist
     */
    public function addResin(string $playerName, int $amount, string $resinType): bool {
        if (isset($this->resin[$playerName])) {
            $this->resin[$playerName][$resinType] += $amount;
            return true;
        }
        return false;
    }

    /**
     * Sets a player's resin to specific amount
     *
     * @param string $playerName Name of player
     * @param int $amount Amount to set
     * @param string $resinType Type of resin to set
     * @return bool True if successful, false if player doesn't exist
     */
    public function setResin(string $playerName, int $amount, string $resinType): bool {
        if (isset($this->resin[$playerName])) {
            $this->resin[$playerName][$resinType] = $amount;
            return true;
        }
        return false;
    }

    /**
     * Reduces a player's resin balance
     *
     * @param string $playerName Name of player
     * @param int $amount Amount to deduct
     * @param string $resinType Type of resin to deduct
     * @return bool True if successful, false if player doesn't exist
     */
    public function reduceResin(string $playerName, int $amount, string $resinType): bool {
        if (isset($this->resin[$playerName])) {
            $this->resin[$playerName][$resinType] -= $amount;
            return true;
        }
        return false;
    }

    /**
     * Gets all registered player names
     *
     * @return array Array of all player names with accounts
     */
    public function getAll(): array {
        return array_keys($this->resin);
    }

    /**
     * Saves all data to JSON file
     */
    public function save(): void {
        $this->data->setAll($this->resin);
        $this->data->save();
    }

    /**
     * Loads data from JSON file
     */
    public function open(): void {
        $this->resin = $this->data->getAll();
    }
}