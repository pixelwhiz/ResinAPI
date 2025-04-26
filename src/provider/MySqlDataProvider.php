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

use pixelwhiz\resinapi\provider\Provider;
use pixelwhiz\resinapi\ResinAPI;
use pixelwhiz\resinapi\ResinTypes;
use pocketmine\utils\Config;
use mysqli;
use InvalidArgumentException;
use RuntimeException;

/**
 * MySQL Data Provider - Database-backed resin storage implementation
 *
 * Provides persistent storage of player resin data using MySQL database.
 * Implements the Provider interface with MySQL-specific operations.
 *
 * Features:
 * - Connection pooling and management
 * - Prepared statements for security
 * - Batch operations for efficiency
 * - Automatic table initialization
 *
 * @package pixelwhiz\resinapi\provider
 * @implements Provider
 */
class MySqlDataProvider implements Provider {

    /**
     * In-memory cache of player resin data
     * @var array Structure: [playerName => [resinType => amount]]
     */
    private array $resin = [];

    /**
     * MySQL database connection
     * @var mysqli
     */
    private mysqli $data;

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
     * Constructor - Establishes MySQL connection and initializes tables
     *
     * @param ResinAPI $plugin Main plugin instance
     * @throws RuntimeException On connection or initialization failure
     */
    public function __construct(ResinAPI $plugin) {
        $this->plugin = $plugin;
        $this->config = $plugin->config;

        $config = $this->config->get("database")["mysql"];

        $host = $config["host"];
        $username = $config["username"];
        $password = $config["password"];
        $database = $config["schema"];
        $port = $config["port"];

        $this->data = new mysqli($host, $username, $password, $database, $port);

        if ($this->data->connect_error) {
            throw new RuntimeException("Failed to connect to MySQL: ". $this->data->connect_error);
        }

        $this->initializeTables();
    }

    /**
     * Initializes required database tables
     *
     * @throws RuntimeException If table creation fails
     */
    private function initializeTables(): void {
        $query = "CREATE TABLE IF NOT EXISTS resin (
            player_name VARCHAR(255) PRIMARY KEY,
            original_resin INT NOT NULL,
            condensed_resin INT NOT NULL,
            fragile_resin INT NOT NULL
        )";

        if (!$this->data->query($query)) {
            throw new RuntimeException("Failed to create table: ". $this->data->error);
        }
    }

    /**
     * Retrieves default resin values from configuration
     *
     * @return array Default resin amounts by type
     */
    public function getDefaultResin(): array {
        return $this->config->get("default-resin");
    }

    /**
     * Checks if a player account exists
     *
     * @param string $playerName Player name to check
     * @return bool True if account exists
     */
    public function accountExists(string $playerName): bool {
        return isset($this->resin[$playerName]);
    }

    /**
     * Creates a new player account with default resin values
     *
     * @param string $playerName Player name to create account for
     * @return bool True if account was created, false if already exists
     */
    public function createAccount(string $playerName): bool {
        if (!isset($this->resin[$playerName])) {
            $this->resin[$playerName] = [
                ResinTypes::ORIGINAL_RESIN => $this->getDefaultResin()[ResinTypes::ORIGINAL_RESIN],
                ResinTypes::FRAGILE_RESIN => $this->getDefaultResin()[ResinTypes::FRAGILE_RESIN],
                ResinTypes::CONDENSED_RESIN => $this->getDefaultResin()[ResinTypes::CONDENSED_RESIN]
            ];
            return true;
        }
        return false;
    }

    /**
     * Gets a player's resin amount by type
     *
     * @param string $playerName Player name
     * @param string $resinType Type of resin
     * @return int Amount of resin
     * @throws InvalidArgumentException For invalid resin types
     */
    public function getResin(string $playerName, string $resinType): int {
        if (isset($this->resin[$playerName])) {
            return match ($resinType) {
                ResinTypes::ORIGINAL_RESIN => $this->resin[$playerName][ResinTypes::ORIGINAL_RESIN],
                ResinTypes::FRAGILE_RESIN => $this->resin[$playerName][ResinTypes::FRAGILE_RESIN],
                ResinTypes::CONDENSED_RESIN => $this->resin[$playerName][ResinTypes::CONDENSED_RESIN],
                default => throw new InvalidArgumentException("Invalid ResinType: {$resinType}")
            };
        }
        return 0;
    }

    /**
     * Gets all resin amounts for a player
     *
     * @param string $playerName Player name
     * @return array All resin types and amounts
     */
    public function getAllResin(string $playerName): array {
        return $this->resin[$playerName] ?? [];
    }

    /**
     * Adds resin to a player's balance
     *
     * @param string $playerName Player name
     * @param int $amount Amount to add
     * @param string $resinType Type of resin
     * @return bool True if successful
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
     * @param string $playerName Player name
     * @param int $amount Amount to set
     * @param string $resinType Type of resin
     * @return bool True if successful
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
     * @param string $playerName Player name
     * @param int $amount Amount to deduct
     * @param string $resinType Type of resin
     * @return bool True if successful
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
     * @return array List of all player names
     */
    public function getAll(): array {
        return array_keys($this->resin);
    }

    /**
     * Saves all data to MySQL database
     *
     * @throws RuntimeException On database errors
     */
    public function save(): void {
        foreach ($this->resin as $playerName => $resinData) {
            $query = "INSERT INTO resin (
                player_name, 
                original_resin, 
                condensed_resin, 
                fragile_resin
            ) VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                original_resin = VALUES(original_resin),
                condensed_resin = VALUES(condensed_resin),
                fragile_resin = VALUES(fragile_resin)";

            $stmt = $this->data->prepare($query);
            if ($stmt === false) {
                throw new RuntimeException("Prepare failed: ". $this->data->error);
            }

            $stmt->bind_param(
                "siii",
                $playerName,
                $resinData[ResinTypes::ORIGINAL_RESIN],
                $resinData[ResinTypes::CONDENSED_RESIN],
                $resinData[ResinTypes::FRAGILE_RESIN]
            );

            if (!$stmt->execute()) {
                $stmt->close();
                throw new RuntimeException("Execute failed: ". $stmt->error);
            }

            $stmt->close();
        }
    }

    /**
     * Loads data from MySQL database
     *
     * @throws RuntimeException On database errors
     */
    public function open(): void {
        $query = "SELECT 
            player_name, 
            original_resin, 
            condensed_resin, 
            fragile_resin 
            FROM resin";

        $result = $this->data->query($query);
        if ($result === false) {
            throw new RuntimeException("Query failed: ". $this->data->error);
        }

        $this->resin = [];
        while ($row = $result->fetch_assoc()) {
            $this->resin[$row['player_name']] = [
                ResinTypes::ORIGINAL_RESIN => (int) $row['original_resin'],
                ResinTypes::CONDENSED_RESIN => (int) $row['condensed_resin'],
                ResinTypes::FRAGILE_RESIN => (int) $row['fragile_resin']
            ];
        }

        $result->free();
    }
}