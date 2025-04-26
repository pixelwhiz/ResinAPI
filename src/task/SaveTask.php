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

namespace pixelwhiz\resinapi\task;

use pixelwhiz\resinapi\ResinAPI;
use pocketmine\scheduler\Task;

/**
 * SaveTask - Periodic Data Persistence Handler
 *
 * Responsible for executing scheduled saves of all resin data
 * to prevent data loss and maintain consistency.
 *
 * @package pixelwhiz\resinapi\task
 * @since 1.0.0
 */
class SaveTask extends Task {

    /**
     * Reference to the main plugin instance
     * @var ResinAPI $plugin
     */
    private ResinAPI $plugin;

    /**
     * Constructor - Initializes the save task
     *
     * @param ResinAPI $plugin The main ResinAPI plugin instance
     */
    public function __construct(ResinAPI $plugin) {
        $this->plugin = $plugin;
    }

    /**
     * Task execution handler - Runs on each scheduled tick
     *
     * Triggers a complete save of all resin data through the plugin's
     * saveAll() method. This ensures data persistence at regular intervals.
     *
     * @return void
     */
    public function onRun(): void {
        $this->plugin->saveAll();
    }
}