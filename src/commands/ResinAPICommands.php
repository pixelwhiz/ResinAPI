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

namespace pixelwhiz\resinapi\commands;

use pixelwhiz\resinapi\commands\constant\PermissionList;
use pixelwhiz\resinapi\language\KnownMessages;
use pixelwhiz\resinapi\language\ResinLang;
use pixelwhiz\resinapi\language\TranslationKeys;
use pixelwhiz\resinapi\provider\Provider;
use pixelwhiz\resinapi\ResinAPI;
use pixelwhiz\resinapi\ResinTypes;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\Config;

/**
 * Class ResinAPICommands
 *
 * The main command handler for ResinAPI that provides all resin management commands.
 * This class handles the execution of all resin-related commands including:
 * - help: Shows available commands
 * - list: Lists all resin types
 * - check: Checks player's resin amounts
 * - give: Gives resin to a player
 * - set: Sets player's resin amount
 * - take: Takes resin from a player
 *
 * All commands are permission-protected and support both players and console execution.
 *
 * @package pixelwhiz\resinapi\commands
 * @since 1.0.0
 */
class ResinAPICommands extends Command {

    /** @var ResinAPI $plugin The main plugin instance */
    private ResinAPI $plugin;

    /** @var ResinLang $language The language handler for translations */
    private ResinLang $language;

    /** @var Provider $provider The data provider for resin storage */
    private Provider $provider;

    /** @var Config $config The plugin configuration */
    private Config $config;

    /**
     * Constructor for ResinAPICommands
     *
     * Initializes the command with basic properties and dependencies.
     *
     * @param ResinAPI $plugin The main plugin instance
     */
    public function __construct(ResinAPI $plugin)
    {
        parent::__construct("resinapi", "ResinAPI main commands", "Usage: /resin help", ["resin"]);
        $this->plugin = $plugin;
        $this->language = $plugin->language;
        $this->provider = $plugin->provider;
        $this->config = $plugin->config;
        $this->setPermission("resinapi.commands");
    }

    /**
     * Executes the command when called
     *
     * Handles all subcommands and their execution flow with proper permission checks.
     *
     * @param CommandSender $sender The entity executing the command (player or console)
     * @param string $commandLabel The actual command used
     * @param array $args The arguments passed with the command
     * @return bool Returns true on success, false on failure
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (count($args) < 1) {
            $sender->sendMessage($this->getUsage());
            return false;
        }

        switch ($args[0]) {
            case "help":
                /* HELP COMMAND IMPLEMENTATION */
                if (!$this->testPermission($sender, PermissionList::COMMAND_RESIN_HELP)) {
                    return false;
                }

                $commands = [
                    "help (Showing all commands)" => PermissionList::COMMAND_RESIN_HELP,
                    "list (List of all resin type)" => PermissionList::COMMAND_RESIN_LIST,
                    "check (Check your resin)" => PermissionList::COMMAND_RESIN_CHECK,
                    "give <player> <resin type> <amount> (Give resin to player)" => PermissionList::COMMAND_RESIN_GIVE,
                    "set <player> <resin type> <amount> (Set the player's resin)" => PermissionList::COMMAND_RESIN_SET,
                    "take <player> <resin type> <amount> (Take resin from player)" => PermissionList::COMMAND_RESIN_TAKE
                ];

                $sender->sendMessage("All ResinAPI main commands:");
                foreach ($commands as $command => $permission) {
                    if ($sender instanceof Player) {
                        if ($sender->hasPermission($permission)) {
                            $sender->sendMessage("- /resin ". $command . "\n");
                        }
                    } else {
                        $sender->sendMessage("- /resin ". $command . "\n");
                    }
                }

                break;
            case "list":
                /* LIST COMMAND IMPLEMENTATION */
                if (!$this->testPermission($sender, PermissionList::COMMAND_RESIN_LIST)) {
                    return false;
                }

                $sender->sendMessage("All Resin Type:");
                foreach (ResinTypes::$allResin as $resin => $resinValue) {
                    $sender->sendMessage("- ". $resinValue);
                }

                break;
            case "check":
                /* CHECK COMMAND IMPLEMENTATION */
                if (!$this->testPermission($sender, PermissionList::COMMAND_RESIN_CHECK)) {
                    return false;
                }

                if (!$sender instanceof Player and !isset($args[1])) {
                    $sender->sendMessage("Usage: /resin check <player>");
                    return false;
                }

                if (isset($args[1])) {
                    if (!$this->testPermission($sender, PermissionList::COMMAND_RESIN_CHECK_OTHER)) {
                        return false;
                    }

                    $target = $args[1];
                    $p = Server::getInstance()->getPlayerByPrefix($target);
                    if ($p instanceof Player) {
                        $target = $p->getName();
                    }

                    $result = ResinAPI::getInstance()->checkResin($target);
                    switch ($result) {
                        case ResinAPI::RET_SUCCESS:
                            $allResin = $this->provider->getAllResin($target);
                            $maxResinConfig = $this->config->get("max-resin");

                            $message = $this->language->translateToString(KnownMessages::SUCCESS_RESIN_CHECK_OTHER,
                                [
                                    TranslationKeys::PLAYER => $target,
                                    TranslationKeys::ORIGINAL_RESIN_AMOUNT => $allResin[ResinTypes::ORIGINAL_RESIN],
                                    TranslationKeys::CONDENSED_RESIN_AMOUNT => $allResin[ResinTypes::CONDENSED_RESIN],
                                    TranslationKeys::FRAGILE_RESIN_AMOUNT => $allResin[ResinTypes::FRAGILE_RESIN],

                                    TranslationKeys::ORIGINAL_RESIN_MAX_AMOUNT => $maxResinConfig[ResinTypes::ORIGINAL_RESIN],
                                    TranslationKeys::CONDENSED_RESIN_MAX_AMOUNT => $maxResinConfig[ResinTypes::CONDENSED_RESIN],
                                    TranslationKeys::FRAGILE_RESIN_MAX_AMOUNT => $maxResinConfig[ResinTypes::FRAGILE_RESIN],
                                ]
                            );

                            $sender->sendMessage($message);
                            break;
                        case ResinAPI::RET_NOT_ONLINE:
                            $message = $this->language->translateToString("error.player.not.online", [
                                TranslationKeys::PLAYER => $target
                            ]);
                            $sender->sendMessage($message);
                            break;
                        case ResinAPI::RET_NO_ACCOUNT:
                            $message = $this->language->translateToString("error.player.not.found", [
                                TranslationKeys::PLAYER => $target
                            ]);
                            $sender->sendMessage($message);
                            break;
                    }
                }

                if ($sender instanceof Player and !isset($args[1])) {
                    $target = $sender->getName();
                    $result = ResinAPI::getInstance()->checkResin($target);
                    switch ($result) {
                        case ResinAPI::RET_SUCCESS:
                            $allResin = $this->provider->getAllResin($target);
                            $maxResinConfig = $this->config->get("max-resin");

                            $message = $this->language->translateToString(KnownMessages::SUCCESS_RESIN_CHECK,
                                [
                                    TranslationKeys::ORIGINAL_RESIN_AMOUNT => $allResin[ResinTypes::ORIGINAL_RESIN],
                                    TranslationKeys::CONDENSED_RESIN_AMOUNT => $allResin[ResinTypes::CONDENSED_RESIN],
                                    TranslationKeys::FRAGILE_RESIN_AMOUNT => $allResin[ResinTypes::FRAGILE_RESIN],

                                    TranslationKeys::ORIGINAL_RESIN_MAX_AMOUNT => $maxResinConfig[ResinTypes::ORIGINAL_RESIN],
                                    TranslationKeys::CONDENSED_RESIN_MAX_AMOUNT => $maxResinConfig[ResinTypes::CONDENSED_RESIN],
                                    TranslationKeys::FRAGILE_RESIN_MAX_AMOUNT => $maxResinConfig[ResinTypes::FRAGILE_RESIN],
                                ]
                            );

                            $sender->sendMessage($message);
                            break;
                        case ResinAPI::RET_NOT_ONLINE:
                            $message = $this->language->translateToString(KnownMessages::ERROR_PLAYER_NOT_ONLINE, [
                                TranslationKeys::PLAYER => $target
                            ]);
                            $sender->sendMessage($message);
                            break;
                        case ResinAPI::RET_NO_ACCOUNT:
                            $message = $this->language->translateToString(KnownMessages::ERROR_PLAYER_NOT_FOUND, [
                                TranslationKeys::PLAYER => $target
                            ]);
                            $sender->sendMessage($message);
                            break;
                    }
                }

                break;
            case "give":
                /* GIVE COMMAND IMPLEMENTATION */
                if (!$this->testPermission($sender, PermissionList::COMMAND_RESIN_GIVE)) {
                    return false;
                }

                if (count($args) !== 4) {
                    $sender->sendMessage("Usage: /resin give <player> <resin type> <amount>");
                    return false;
                }

                if (isset($args[3])) {
                    $target = $args[1];
                    $p = Server::getInstance()->getPlayerByPrefix($target);
                    if ($p instanceof Player) {
                        $target = $p->getName();
                    }

                    $amount = (int)$args[3];
                    $resinType = (string)$args[2];

                    $result = ResinAPI::getInstance()->addResin($target, $amount, $resinType);

                    switch ($result) {
                        case ResinAPI::RET_SUCCESS:
                            $sender->sendMessage(
                                $this->language->translateToString(KnownMessages::SUCCESS_CONSOLE_RESIN_GIVE, [
                                    TranslationKeys::PLAYER => $target,
                                    TranslationKeys::RESIN_TYPE => $resinType,
                                    TranslationKeys::AMOUNT => $amount
                                ])
                            );

                            $player = Server::getInstance()->getPlayerExact($target);
                            $player->sendMessage(
                                $this->language->translateToString(KnownMessages::SUCCESS_PLAYER_RESIN_GIVE, [
                                    TranslationKeys::COMMAND_SENDER => $sender->getName(),
                                    TranslationKeys::RESIN_TYPE => $resinType,
                                    TranslationKeys::AMOUNT => $amount
                                ])
                            );
                            break;
                        case ResinAPI::RET_NO_ACCOUNT:
                            $message = $this->language->translateToString(KnownMessages::ERROR_PLAYER_NOT_FOUND, [
                                TranslationKeys::PLAYER => $target
                            ]);
                            $sender->sendMessage($message);
                            break;
                        case ResinAPI::RET_NOT_ONLINE:
                            $message = $this->language->translateToString(KnownMessages::ERROR_PLAYER_NOT_ONLINE, [
                                TranslationKeys::PLAYER => $target
                            ]);
                            $sender->sendMessage($message);
                            break;
                        case ResinAPI::RET_INVALID_RESIN_TYPE:
                            $message = $this->language->translateToString(KnownMessages::ERROR_INVALID_RESIN_TYPE, [
                                TranslationKeys::RESIN_TYPE => $resinType
                            ]);
                            $sender->sendMessage($message);
                            break;
                        case ResinAPI::RET_INVALID_NUMBER:
                            $message = $this->language->translateToString(KnownMessages::ERROR_INVALID_NUMBER, []);
                            $sender->sendMessage($message);
                            break;
                        case ResinAPI::RET_INSUFFICENT_AMOUNT:
                            $message = $this->language->translateToString(KnownMessages::ERROR_INSUFFICIENT_AMOUNT, []);
                            $sender->sendMessage($message);
                            break;
                    }

                    return true;
                }

                break;
            case "set":
                /* SET COMMAND IMPLEMENTATION */
                if (!$this->testPermission($sender, PermissionList::COMMAND_RESIN_SET)) {
                    return false;
                }

                if (count($args) !== 4) {
                    $sender->sendMessage("Usage: /resin set <player> <resin type> <amount>");
                    return false;
                }

                if (isset($args[3])) {
                    $target = $args[1];
                    $p = Server::getInstance()->getPlayerByPrefix($target);
                    if ($p instanceof Player) {
                        $target = $p->getName();
                    }
                    $amount = (int)$args[3];
                    $resinType = (string)$args[2];

                    $result = ResinAPI::getInstance()->setResin($target, $amount, $resinType);
                    switch ($result) {
                        case ResinAPI::RET_SUCCESS:
                            $sender->sendMessage(
                                $this->language->translateToString(KnownMessages::SUCCESS_CONSOLE_RESIN_SET, [
                                    TranslationKeys::PLAYER => $target,
                                    TranslationKeys::RESIN_TYPE => $resinType,
                                    TranslationKeys::AMOUNT => $amount
                                ])
                            );

                            $player = Server::getInstance()->getPlayerExact($target);
                            $player->sendMessage(
                                $this->language->translateToString(KnownMessages::SUCCESS_PLAYER_RESIN_SET, [
                                    TranslationKeys::COMMAND_SENDER => $sender->getName(),
                                    TranslationKeys::RESIN_TYPE => $resinType,
                                    TranslationKeys::AMOUNT => $amount
                                ])
                            );
                            break;
                        case ResinAPI::RET_NO_ACCOUNT:
                            $message = $this->language->translateToString(KnownMessages::ERROR_PLAYER_NOT_FOUND, [
                                TranslationKeys::PLAYER => $target
                            ]);
                            $sender->sendMessage($message);
                            break;
                        case ResinAPI::RET_NOT_ONLINE:
                            $message = $this->language->translateToString(KnownMessages::ERROR_PLAYER_NOT_ONLINE, [
                                TranslationKeys::PLAYER => $target
                            ]);
                            $sender->sendMessage($message);
                            break;
                        case ResinAPI::RET_INVALID_RESIN_TYPE:
                            $message = $this->language->translateToString(KnownMessages::ERROR_INVALID_RESIN_TYPE);
                            $sender->sendMessage($message);
                            break;
                        case ResinAPI::RET_INVALID_NUMBER:
                            $message = $this->language->translateToString(KnownMessages::ERROR_INVALID_NUMBER);
                            $sender->sendMessage($message);
                            break;
                        case ResinAPI::RET_INSUFFICENT_AMOUNT:
                            $message = $this->language->translateToString(KnownMessages::ERROR_INSUFFICIENT_AMOUNT);
                            $sender->sendMessage($message);
                            break;
                    }

                    return true;
                }
                break;
            case "take":
                /* TAKE COMMAND IMPLEMENTATION */
                if (!$this->testPermission($sender, PermissionList::COMMAND_RESIN_TAKE)) {
                    return false;
                }

                if (count($args) !== 4) {
                    $sender->sendMessage("Usage: /resin take <player> <resin type> <amount>");
                    return false;
                }

                if (isset($args[3])) {
                    $target = $args[1];
                    $p = Server::getInstance()->getPlayerByPrefix($target);
                    if ($p instanceof Player) {
                        $target = $p->getName();
                    }
                    $amount = (int)$args[3];
                    $resinType = (string)$args[2];

                    $result = ResinAPI::getInstance()->reduceResin($target, $amount, $resinType);

                    switch ($result) {
                        case ResinAPI::RET_SUCCESS:
                            $sender->sendMessage(
                                $this->language->translateToString(KnownMessages::SUCCESS_CONSOLE_RESIN_TAKE, [
                                    TranslationKeys::PLAYER => $target,
                                    TranslationKeys::RESIN_TYPE => $resinType,
                                    TranslationKeys::AMOUNT => $amount
                                ])
                            );

                            $player = Server::getInstance()->getPlayerExact($target);
                            $player->sendMessage(
                                $this->language->translateToString(KnownMessages::SUCCESS_PLAYER_RESIN_TAKE, [
                                    TranslationKeys::COMMAND_SENDER => $sender->getName(),
                                    TranslationKeys::RESIN_TYPE => $resinType,
                                    TranslationKeys::AMOUNT => $amount
                                ])
                            );

                            break;
                        case ResinAPI::RET_NO_ACCOUNT:
                            $sender->sendMessage(
                                $this->language->translateToString(KnownMessages::ERROR_PLAYER_NOT_FOUND)
                            );
                        case ResinAPI::RET_NOT_ONLINE:
                            $sender->sendMessage(
                                $this->language->translateToString(KnownMessages::ERROR_PLAYER_NOT_ONLINE)
                            );
                            break;
                        case ResinAPI::RET_INVALID_RESIN_TYPE:
                            $sender->sendMessage(
                                $this->language->translateToString(KnownMessages::ERROR_INVALID_RESIN_TYPE)
                            );
                            break;
                        case ResinAPI::RET_INVALID_NUMBER:
                            $sender->sendMessage(
                                $this->language->translateToString(KnownMessages::ERROR_INVALID_NUMBER)
                            );
                            break;
                        case ResinAPI::RET_INSUFFICENT_AMOUNT:
                            $sender->sendMessage(
                                $this->language->translateToString(KnownMessages::ERROR_INSUFFICIENT_AMOUNT)
                            );
                            break;
                    }

                    return true;
                }

                break;
            default:
                $sender->sendMessage($this->getUsage());
                return false;
        }

        return true;
    }
}