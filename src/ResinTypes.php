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

namespace pixelwhiz\resinapi;

/**
 * ResinTypes - Centralized Definition of Resin Types and Identifiers
 *
 * This final class serves as the authoritative source for all resin-related type definitions
 * within the ResinAPI ecosystem. It provides constants and mappings for the different
 * categories of resin supported by the system.
 *
 * @final This class should not be extended
 * @since 1.0.0
 * @version 1.2.0
 */
final class ResinTypes {

    /**
     * Complete mapping of all resin types with their display names and internal identifiers
     *
     * Structure:
     * [
     *    "Display Name" => "internal_identifier",
     *    ...
     * ]
     *
     * @var array<string, string> $allResin
     * @public
     */
    public static array $allResin = [
        "Original Resin" => "original_resin",
        "Condensed Resin" => "condensed_resin",
        "Fragile Resin" => "fragile_resin"
    ];

    /**
     * Constant representing Condensed Resin type
     *
     * @const string CONDENSED_RESIN
     * @usage ResinTypes::CONDENSED_RESIN
     */
    public const CONDENSED_RESIN = "Condensed Resin";

    /**
     * Constant representing Original Resin type
     *
     * @const string ORIGINAL_RESIN
     * @usage ResinTypes::ORIGINAL_RESIN
     */
    public const ORIGINAL_RESIN = "Original Resin";

    /**
     * Constant representing Fragile Resin type
     *
     * @const string FRAGILE_RESIN
     * @usage ResinTypes::FRAGILE_RESIN
     */
    public const FRAGILE_RESIN = "Fragile Resin";

    /**
     * Private constructor to prevent instantiation
     *
     * This is a utility class that should not be instantiated
     */
    private function __construct() {
        // Intentionally empty
    }
}