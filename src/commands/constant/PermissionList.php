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

namespace pixelwhiz\resinapi\commands\constant;

/**
 * Class PermissionList
 *
 * Defines all permission nodes used by the ResinAPI plugin in a type-safe,
 * maintainable way. This final class serves as the single source of truth
 * for all permission-related constants in the application.
 *
 * Key Features:
 * - Contains all permission nodes as class constants
 * - Follows consistent naming conventions
 * - Organized by command functionality
 * - Prevents magic strings in code
 * - Enables IDE autocompletion
 * - Facilitates permission documentation
 *
 * Usage Guidelines:
 * 1. Always reference these constants rather than string literals
 * 2. Add new permissions here when implementing new features
 * 3. Document permission purposes in this header
 *
 * @package pixelwhiz\resinapi\commands\constant
 * @version 1.0.0
 * @since API Version 1.0
 * @final This class should not be extended
 */
final class PermissionList {

    /* --------------------------------------------------------------------- */
    /* Resin Modification Permissions
    /* These permissions control the ability to modify player resin amounts
    /* --------------------------------------------------------------------- */

    /**
     * Allows giving resin to players
     *
     * Scope: OP/Admin
     * Default: op
     * Recommended: resin.admin
     */
    public const COMMAND_RESIN_GIVE = "resinapi.command.give";

    /**
     * Allows setting player resin amounts
     *
     * Scope: OP/Admin
     * Default: op
     * Recommended: resin.admin
     */
    public const COMMAND_RESIN_SET = "resinapi.command.set";

    /**
     * Allows taking resin from players
     *
     * Scope: OP/Admin
     * Default: op
     * Recommended: resin.admin
     */
    public const COMMAND_RESIN_TAKE = "resinapi.command.take";

    /* --------------------------------------------------------------------- */
    /* Resin Viewing Permissions
    /* These permissions control access to resin information
    /* --------------------------------------------------------------------- */

    /**
     * Allows checking own resin amounts
     *
     * Scope: All Players
     * Default: true
     * Recommended: resin.user
     */
    public const COMMAND_RESIN_CHECK = "resinapi.command.check";

    /**
     * Allows checking other players' resin
     *
     * Scope: Moderators+
     * Default: op
     * Recommended: resin.moderator
     */
    public const COMMAND_RESIN_CHECK_OTHER = "resinapi.command.check.other";

    /**
     * Allows listing available resin types
     *
     * Scope: All Players
     * Default: true
     * Recommended: resin.user
     */
    public const COMMAND_RESIN_LIST = "resinapi.command.list";

    /**
     * Allows viewing command help
     *
     * Scope: All Players
     * Default: true
     * Recommended: resin.user
     */
    public const COMMAND_RESIN_HELP = "resinapi.command.help";

    /* --------------------------------------------------------------------- */
    /* Utility Methods (if any would be added in future versions)
    /* --------------------------------------------------------------------- */

    /**
     * Private constructor to prevent instantiation
     *
     * This is a utility class that should not be instantiated
     */
    private function __construct() {
        throw new \LogicException("Cannot instantiate PermissionList - use as constants only");
    }
}