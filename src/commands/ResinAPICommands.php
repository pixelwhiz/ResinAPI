<?php

namespace pixelwhiz\resinapi\commands;

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

                $commands = [
                    "help (Showing all commands)" => "resinapi.command.help",
                    "list (List of all resin type)" => "resinapi.command.list",
                    "check (Check your resin)" => "resinapi.command.check",
                    "give <player> <resin type> <amount> (Give resin to player)" => "resinapi.command.give",
                    "set <player> <resin type> <amount> (Set the player's resin)" => "resinapi.command.set",
                    "take <player> <resin type> <amount> (Take resin from player)" => "resinapi.command.take"
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
                $sender->sendMessage("All Resin Type:");
                foreach (ResinTypes::$allResin as $resin => $resinValue) {
                    $sender->sendMessage("- ". $resinValue);
                }

                break;
            case "check":

                if (!$sender instanceof Player and !isset($args[1])) {
                    $sender->sendMessage("Usage: /resin check <player>");
                    return false;
                }

                if (isset($args[1])) {
                    if (!$sender->hasPermission("resinapi.command.check.other")) {
                        $sender->sendMessage(ResinAPI::getInstance()->getMessage("no-permission"));
                        return false;
                    }

                    $player = Server::getInstance()->getPlayerExact($args[1]);
                    if ($player === null) {
                        $sender->sendMessage("Player $args[1] not found");
                        return false;
                    }

                    $allResin = ResinAPI::getInstance()->getAllResin($player);
                    $maxResinConfig = $this->config->get("max-resin");

                    $originalResinAmount = $allResin[ResinTypes::ORIGINAL_RESIN];
                    $condensedResinAmount = $allResin[ResinTypes::CONDENSED_RESIN];
                    $fragileResinAmount = $allResin[ResinTypes::FRAGILE_RESIN];

                    $originalResinMaxAmount = $maxResinConfig[ResinTypes::ORIGINAL_RESIN];
                    $condensedResinMaxAmount = $maxResinConfig[ResinTypes::CONDENSED_RESIN];
                    $fragileResinMaxAmount = $maxResinConfig[ResinTypes::FRAGILE_RESIN];

                    $message = $this->language->translateToString("command.resin.check.other",
                        [
                            TranslationKeys::PLAYER => $player->getName(),
                            TranslationKeys::ORIGINAL_RESIN_AMOUNT => $originalResinAmount,
                            TranslationKeys::CONDENSED_RESIN_AMOUNT => $condensedResinAmount,
                            TranslationKeys::FRAGILE_RESIN_AMOUNT => $fragileResinAmount,

                            TranslationKeys::ORIGINAL_RESIN_MAX_AMOUNT => $originalResinMaxAmount,
                            TranslationKeys::CONDENSED_RESIN_MAX_AMOUNT => $condensedResinMaxAmount,
                            TranslationKeys::FRAGILE_RESIN_MAX_AMOUNT => $fragileResinMaxAmount,
                        ]
                    );

                    $sender->sendMessage($message);

                }

                if ($sender instanceof Player and !isset($args[1])) {
                    $player = $sender;
                    $allResin = ResinAPI::getInstance()->getAllResin($player);
                    $maxResinConfig = $this->config->get("max-resin");

                    $originalResinAmount = $allResin[ResinTypes::ORIGINAL_RESIN];
                    $condensedResinAmount = $allResin[ResinTypes::CONDENSED_RESIN];
                    $fragileResinAmount = $allResin[ResinTypes::FRAGILE_RESIN];

                    $originalResinMaxAmount = $maxResinConfig[ResinTypes::ORIGINAL_RESIN];
                    $condensedResinMaxAmount = $maxResinConfig[ResinTypes::CONDENSED_RESIN];
                    $fragileResinMaxAmount = $maxResinConfig[ResinTypes::FRAGILE_RESIN];

                    $message = $this->language->translateToString("command.resin.check",
                        [
                            TranslationKeys::ORIGINAL_RESIN_AMOUNT => $originalResinAmount,
                            TranslationKeys::CONDENSED_RESIN_AMOUNT => $condensedResinAmount,
                            TranslationKeys::FRAGILE_RESIN_AMOUNT => $fragileResinAmount,

                            TranslationKeys::ORIGINAL_RESIN_MAX_AMOUNT => $originalResinMaxAmount,
                            TranslationKeys::CONDENSED_RESIN_MAX_AMOUNT => $condensedResinMaxAmount,
                            TranslationKeys::FRAGILE_RESIN_MAX_AMOUNT => $fragileResinMaxAmount,
                        ]
                    );

                    $sender->sendMessage($message);
                }

                break;

            case "give":
                if (count($args) !== 4) {
                    $sender->sendMessage("Usage: /resin give <player> <resin type> <amount>");
                    return false;
                }

                if (isset($args[3])) {
                    $player = Server::getInstance()->getPlayerExact($args[1]);
                    $amount = (int)$args[3];
                    $resinType = null;

                    foreach (ResinTypes::$allResin as $resin => $resinValue) {
                        if ($resinValue === $args[2]) {
                            $resinType = $resin;
                            break;
                        }
                    }

                    $result = ResinAPI::getInstance()->addResin($player, $amount, $resinType);

                    switch ($result) {
                        case ResinAPI::RET_SUCCESS:
                            $sender->sendMessage("Give {$amount}x {$args[2]} resin to " . $player->getName());
                            break;
                        case ResinAPI::RET_NO_ACCOUNT:
                            $sender->sendMessage("Player not found");
                            break;
                        case ResinAPI::RET_INVALID_RESIN_TYPE:
                            $sender->sendMessage("Invalid resin type");
                            break;
                        case ResinAPI::RET_INVALID_NUMBER:
                            $sender->sendMessage("Invalid number");
                            break;
                    }

                    return true;
                }

                break;
            case "set":
                if (count($args) !== 4) {
                    $sender->sendMessage("Usage: /resin set <player> <resin type> <amount>");
                    return false;
                }

                if (isset($args[3])) {
                    $player = Server::getInstance()->getPlayerExact($args[1]);
                    $amount = (int)$args[3];
                    $resinType = null;

                    foreach (ResinTypes::$allResin as $resin => $resinValue) {
                        if ($resinValue === $args[2]) {
                            $resinType = $resin;
                            break;
                        }
                    }

                    $result = ResinAPI::getInstance()->setResin($player, $amount, $resinType);
                    switch ($result) {
                        case ResinAPI::RET_SUCCESS:
                            $sender->sendMessage("Player ".$player->getName()." ".$resin." resin was set to ".$amount);
                            break;
                        case ResinAPI::RET_NO_ACCOUNT:
                            $sender->sendMessage("Player not found");
                            break;
                        case ResinAPI::RET_INVALID_RESIN_TYPE:
                            $sender->sendMessage("Invalid resin type");
                            break;
                        case ResinAPI::RET_INVALID_NUMBER:
                            $sender->sendMessage("Invalid number");
                            break;
                    }

                    return true;
                }
                break;
            case "take":

                break;
            default:
                $sender->sendMessage($this->getUsage());
                return false;
        }

        if (!$sender->hasPermission("resinapi.command.". $args[0])) {
            $sender->sendMessage(ResinAPI::getInstance()->getMessage("no-permission"));
            return false;
        }

        return true;
    }
}