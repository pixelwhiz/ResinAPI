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
 * KnownMessages - Centralized message key constants for the ResinAPI plugin
 *
 * Contains all message keys used in the plugin's language system to ensure
 * consistency and prevent typos. These constants should be used instead of
 * hard-coded strings throughout the codebase.
 *
 * @package pixelwhiz\resinapi\language
 * @final This class should not be extended
 */
final class KnownMessages {

    /* Error Messages */

    /**
     * Message key: Error when target player is not online
     */
    public const ERROR_PLAYER_NOT_ONLINE = "error.player.not.online";

    /**
     * Message key: Error when player account is not found
     */
    public const ERROR_PLAYER_NOT_FOUND = "error.player.not.found";

    /**
     * Message key: Error when invalid resin type is specified
     */
    public const ERROR_INVALID_RESIN_TYPE = "error.invalid.resin.type";

    /**
     * Message key: Error when invalid numeric value is provided
     */
    public const ERROR_INVALID_NUMBER = "error.invalid.number";

    /**
     * Message key: Error when player doesn't have enough resin
     */
    public const ERROR_INSUFFICIENT_AMOUNT = "error.insufficient.amount";

    /* Resin Check Messages */

    /**
     * Message key: Success response for checking own resin
     */
    public const SUCCESS_RESIN_CHECK = "success.resin.check";

    /**
     * Message key: Success response for checking another player's resin
     */
    public const SUCCESS_RESIN_CHECK_OTHER = "success.resin.check.other";

    /* Resin Give Messages */

    /**
     * Message key: Console success message when giving resin
     */
    public const SUCCESS_CONSOLE_RESIN_GIVE = "success.console.resin.give";

    /**
     * Message key: Player success message when receiving resin
     */
    public const SUCCESS_PLAYER_RESIN_GIVE = "success.player.resin.give";

    /* Resin Set Messages */

    /**
     * Message key: Console success message when setting resin
     */
    public const SUCCESS_CONSOLE_RESIN_SET = "success.console.resin.set";

    /**
     * Message key: Player success message when resin is set
     */
    public const SUCCESS_PLAYER_RESIN_SET = "success.player.resin.set";

    /* Resin Take Messages */

    /**
     * Message key: Console success message when taking resin
     */
    public const SUCCESS_CONSOLE_RESIN_TAKE = "success.console.resin.take";

    /**
     * Message key: Player notification when resin is taken
     */
    public const SUCCESS_PLAYER_RESIN_TAKE = "success.player.resin.take";

    /**
     * Private constructor to prevent instantiation
     */
    private function __construct() {}
}