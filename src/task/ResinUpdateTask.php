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

use pixelwhiz\resinapi\provider\Provider;
use pixelwhiz\resinapi\ResinTypes;
use pocketmine\scheduler\Task;
use pocketmine\utils\Config;

/**
 * ResinUpdateTask - Automated Resin Regeneration Scheduler
 *
 * Handles the periodic regeneration of Original Resin for all players
 * according to configured intervals and maximum limits.
 *
 * @package pixelwhiz\resinapi\task
 * @since 1.0.0
 */
class ResinUpdateTask extends Task {

    /**
     * Countdown timer for next resin update (in seconds)
     * @var int $updateTime
     */
    public int $updateTime;

    /**
     * Plugin configuration container
     * @var Config $config
     */
    private Config $config;

    /**
     * Data provider instance for resin storage
     * @var Provider $provider
     */
    private Provider $provider;

    /**
     * Constructor - Initializes the regeneration task
     *
     * @param Config $config Plugin configuration
     * @param Provider $provider Data provider instance
     */
    public function __construct(Config $config, Provider $provider) {
        $this->config = $config;
        $this->provider = $provider;
        $this->updateTime = 60 * $config->get("interval-to-update");
    }

    /**
     * Task execution handler - Runs every tick
     *
     * Manages the resin regeneration cycle:
     * 1. Decrements the update timer
     * 2. When timer reaches zero:
     *    - Checks each player's current resin
     *    - Adds 1 Original Resin if under max limit
     *    - Resets the timer
     *
     * @return void
     */
    public function onRun(): void {
        $this->updateTime--;

        if ($this->updateTime === 0) {
            $maxResin = $this->config->get("max-resin")[ResinTypes::ORIGINAL_RESIN];
            $interval = $this->config->get("interval-to-update");

            foreach ($this->provider->getAll() as $playerName) {
                $currentResin = $this->provider->getResin($playerName, ResinTypes::ORIGINAL_RESIN);

                if ($currentResin < $maxResin) {
                    $this->provider->addResin($playerName, 1, ResinTypes::ORIGINAL_RESIN);
                }
            }

            $this->updateTime = 60 * $interval;
        }
    }
}