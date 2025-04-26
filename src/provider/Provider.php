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

use pixelwhiz\resinapi\ResinTypes;

/**
 * Provider Interface - Defines the contract for resin data storage implementations
 *
 * This interface establishes the standard API for all resin data providers,
 * ensuring consistent behavior across different storage backends (JSON, MySQL, etc.).
 * All concrete providers must implement these methods to guarantee interoperability.
 *
 * @package pixelwhiz\resinapi\provider
 * @since 1.0.0
 */
interface Provider {

    /**
     * Checks if a player account exists in the data store
     *
     * @param string $playerName The name of the player to check
     * @return bool True if the account exists, false otherwise
     */
    public function accountExists(string $playerName): bool;

    /**
     * Creates a new player account with default resin values
     *
     * @param string $playerName The name of the player to create an account for
     * @return bool True if account was created, false if it already existed
     */
    public function createAccount(string $playerName): bool;

    /**
     * Retrieves the default resin values from configuration
     *
     * @return array An associative array of default resin amounts by type
     */
    public function getDefaultResin(): array;

    /**
     * Gets a player's resin amount of a specific type
     *
     * @param string $playerName The name of the player
     * @param string $resinType The type of resin (must be a ResinTypes constant)
     * @return int The amount of resin the player has of this type
     * @throws \InvalidArgumentException If an invalid resin type is provided
     */
    public function getResin(string $playerName, string $resinType): int;

    /**
     * Gets all resin amounts for a player
     *
     * @param string $playerName The name of the player
     * @return array An associative array of [resinType => amount] pairs
     */
    public function getAllResin(string $playerName): array;

    /**
     * Sets a player's resin to a specific amount
     *
     * @param string $playerName The name of the player
     * @param int $amount The exact amount to set
     * @param string $resinType The type of resin to modify
     * @return bool True if successful, false if player doesn't exist
     */
    public function setResin(string $playerName, int $amount, string $resinType): bool;

    /**
     * Adds resin to a player's balance
     *
     * @param string $playerName The name of the player
     * @param int $amount The amount to add
     * @param string $resinType The type of resin to add
     * @return bool True if successful, false if player doesn't exist
     */
    public function addResin(string $playerName, int $amount, string $resinType): bool;

    /**
     * Deducts resin from a player's balance
     *
     * @param string $playerName The name of the player
     * @param int $amount The amount to deduct
     * @param string $resinType The type of resin to deduct
     * @return bool True if successful, false if player doesn't exist
     */
    public function reduceResin(string $playerName, int $amount, string $resinType): bool;

    /**
     * Persists all data to storage
     *
     * @return void
     * @throws \RuntimeException If saving fails
     */
    public function save(): void;

    /**
     * Loads data from storage
     *
     * @return void
     * @throws \RuntimeException If loading fails
     */
    public function open(): void;

    /**
     * Gets all registered player names
     *
     * @return array An array of all player names with accounts
     */
    public function getAll(): array;
}