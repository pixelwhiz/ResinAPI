<?php

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

class ResinAPICommands extends Command {

    private ResinAPI $plugin;
    private ResinLang $language;
    private Provider $provider;
    private Config $config;

    public function __construct(ResinAPI $plugin)
    {
        parent::__construct("resinapi", "ResinAPI main commands", "Usage: /resin help", ["resin"]);
        $this->plugin = $plugin;
        $this->language = $plugin->language;
        $this->provider = $plugin->provider;
        $this->config = $plugin->config;
        $this->setPermission("resinapi.commands");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (count($args) < 1) {
            $sender->sendMessage($this->getUsage());
            return false;
        }

        switch ($args[0]) {
            case "help":
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
                if (!$this->testPermission($sender, PermissionList::COMMAND_RESIN_LIST)) {
                    return false;
                }

                $sender->sendMessage("All Resin Type:");
                foreach (ResinTypes::$allResin as $resin => $resinValue) {
                    $sender->sendMessage("- ". $resinValue);
                }

                break;
            case "check":
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