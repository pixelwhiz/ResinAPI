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

namespace pixelwhiz\resinapi\language;

/**
 * TranslationKeys - Constants for language system placeholders
 *
 * Defines all placeholder keys used in language files for dynamic text replacement.
 * These constants are used throughout the plugin for consistent localization.
 *
 * @package pixelwhiz\resinapi\language
 * @final This class should not be extended
 */
final class TranslationKeys {

    /**
     * Placeholder for original resin current amount
     * @var string
     */
    public const ORIGINAL_RESIN_AMOUNT = "{original_resin_amount}";

    /**
     * Placeholder for original resin maximum capacity
     * @var string
     */
    public const ORIGINAL_RESIN_MAX_AMOUNT = "{original_resin_max_amount}";

    /**
     * Placeholder for condensed resin current amount
     * @var string
     */
    public const CONDENSED_RESIN_AMOUNT = "{condensed_resin_amount}";

    /**
     * Placeholder for condensed resin maximum capacity
     * @var string
     */
    public const CONDENSED_RESIN_MAX_AMOUNT = "{condensed_resin_max_amount}";

    /**
     * Placeholder for fragile resin current amount
     * @var string
     */
    public const FRAGILE_RESIN_AMOUNT = "{fragile_resin_amount}";

    /**
     * Placeholder for fragile resin maximum capacity
     * @var string
     */
    public const FRAGILE_RESIN_MAX_AMOUNT = "{fragile_resin_max_amount}";

    /**
     * Placeholder for player name in messages
     * @var string
     */
    public const PLAYER = "{player}";

    /**
     * Placeholder for command sender name
     * @var string
     */
    public const COMMAND_SENDER = "{sender}";

    /**
     * Placeholder for resin type in messages
     * @var string
     */
    public const RESIN_TYPE = "{resin_type}";

    /**
     * Placeholder for generic amount in messages
     * @var string
     */
    public const AMOUNT = "{amount}";

    /**
     * Private constructor to prevent instantiation
     *
     * This is a utility class that should not be instantiated
     */
    private function __construct() {
        // Intentionally empty
    }
}