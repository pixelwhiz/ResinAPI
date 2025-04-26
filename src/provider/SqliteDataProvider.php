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
use SQLite3;
use RuntimeException;
use InvalidArgumentException;

/**
 * SqliteDataProvider - SQLite implementation of resin data storage
 *
 * Provides persistent storage for player resin data using SQLite database.
 * Implements the Provider interface with SQLite-specific operations.
 *
 * Features:
 * - Lightweight file-based storage
 * - Prepared statements for security
 * - ACID-compliant transactions
 * - Automatic table initialization
 *
 * @package pixelwhiz\resinapi\provider
 * @implements Provider
 */
class SqliteDataProvider implements Provider {

    /**
     * In-memory cache of player resin data
     * @var array Structure: [playerName => [resinType => amount]]
     */
    private array $resin = [];

    /**
     * SQLite3 database connection
     * @var SQLite3
     */
    private SQLite3 $data;

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
     * Constructor - Initializes SQLite database connection
     *
     * @param ResinAPI $plugin Main plugin instance
     * @throws RuntimeException If database initialization fails
     */
    public function __construct(ResinAPI $plugin) {
        $this->plugin = $plugin;
        $this->config = $plugin->config;

        $dbPath = $this->plugin->getDataFolder() . "database/data.sqlite";
        if (!is_dir(dirname($dbPath))) {
            mkdir(dirname($dbPath), 0777, true);
        }

        try {
            $this->data = new SQLite3($dbPath);
            $this->data->enableExceptions(true);
            $this->initializeTables();
        } catch (\Exception $e) {
            throw new RuntimeException("SQLite initialization failed: " . $e->getMessage());
        }
    }

    /**
     * Initializes required database tables
     *
     * @throws RuntimeException If table creation fails
     */
    private function initializeTables(): void {
        $query = "CREATE TABLE IF NOT EXISTS resin (
            player_name TEXT PRIMARY KEY,
            original_resin INTEGER NOT NULL,
            condensed_resin INTEGER NOT NULL,
            fragile_resin INTEGER NOT NULL
        )";

        if (!$this->data->exec($query)) {
            throw new RuntimeException("Failed to create table: " . $this->data->lastErrorMsg());
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
     * @throws InvalidArgumentException If invalid resin type provided
     */
    public function getResin(string $playerName, string $resinType): int {
        if (!isset($this->resin[$playerName])) {
            return 0;
        }

        return match ($resinType) {
            ResinTypes::ORIGINAL_RESIN => $this->resin[$playerName][ResinTypes::ORIGINAL_RESIN],
            ResinTypes::FRAGILE_RESIN => $this->resin[$playerName][ResinTypes::FRAGILE_RESIN],
            ResinTypes::CONDENSED_RESIN => $this->resin[$playerName][ResinTypes::CONDENSED_RESIN],
            default => throw new InvalidArgumentException("Invalid ResinType: {$resinType}")
        };
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
     * @return array List of all player names with accounts
     */
    public function getAll(): array {
        return array_keys($this->resin);
    }

    /**
     * Saves all data to SQLite database
     *
     * @throws RuntimeException On database errors
     */
    public function save(): void {
        try {
            $this->data->exec('BEGIN TRANSACTION');

            foreach ($this->resin as $playerName => $resinData) {
                $stmt = $this->data->prepare("
                    INSERT OR REPLACE INTO resin 
                    (player_name, original_resin, condensed_resin, fragile_resin)
                    VALUES (:player_name, :original_resin, :condensed_resin, :fragile_resin)
                ");

                $stmt->bindValue(":player_name", $playerName, SQLITE3_TEXT);
                $stmt->bindValue(':original_resin', $resinData[ResinTypes::ORIGINAL_RESIN], SQLITE3_INTEGER);
                $stmt->bindValue(':condensed_resin', $resinData[ResinTypes::CONDENSED_RESIN], SQLITE3_INTEGER);
                $stmt->bindValue(':fragile_resin', $resinData[ResinTypes::FRAGILE_RESIN], SQLITE3_INTEGER);

                if (!$stmt->execute()) {
                    throw new RuntimeException("Execute failed: " . $this->data->lastErrorMsg());
                }
            }

            $this->data->exec('COMMIT');
        } catch (\Exception $e) {
            $this->data->exec('ROLLBACK');
            throw new RuntimeException("Save operation failed: " . $e->getMessage());
        }
    }

    /**
     * Loads data from SQLite database
     *
     * @throws RuntimeException On database errors
     */
    public function open(): void {
        try {
            $result = $this->data->query("SELECT * FROM resin");
            if ($result === false) {
                throw new RuntimeException("Query failed: " . $this->data->lastErrorMsg());
            }

            $this->resin = [];
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $this->resin[$row['player_name']] = [
                    ResinTypes::ORIGINAL_RESIN => (int)$row['original_resin'],
                    ResinTypes::CONDENSED_RESIN => (int)$row['condensed_resin'],
                    ResinTypes::FRAGILE_RESIN => (int)$row['fragile_resin']
                ];
            }
        } catch (\Exception $e) {
            throw new RuntimeException("Load operation failed: " . $e->getMessage());
        }
    }
}