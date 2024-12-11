<?php

namespace pixelwhiz\resinapi\commands;

use pixelwhiz\resinapi\commands\constant\PermissionList;
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
                if (!$this->testPermission($sender, PermissionList::COMMAND_RESIN_GIVE)) {
                    return false;
                }

                if (count($args) !== 4) {
                    $sender->sendMessage("Usage: /resin give <player> <resin type> <amount>");
                    return false;
                }

                if (isset($args[3])) {
                    $player = Server::getInstance()->getPlayerExact($args[1]);
                    $amount = (int)$args[3];
                    $resinType = (string)$args[2];

                    $result = ResinAPI::getInstance()->addResin($player, $amount, $resinType);

                    switch ($result) {
                        case ResinAPI::RET_SUCCESS:
                            $sender->sendMessage("Give {$amount} {$args[2]} resin to " . $player->getName());
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
                        case ResinAPI::RET_INSUFFICENT_AMOUNT:
                            $sender->sendMessage("Insufficient amount");
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
                    $player = Server::getInstance()->getPlayerExact($args[1]);
                    $amount = (int)$args[3];
                    $resinType = (string)$args[2];

                    $result = ResinAPI::getInstance()->setResin($player, $amount, $resinType);
                    switch ($result) {
                        case ResinAPI::RET_SUCCESS:
                            $sender->sendMessage("Player ".$player->getName()." ".$resinType." resin was set to ".$amount);
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
                        case ResinAPI::RET_INSUFFICENT_AMOUNT:
                            $sender->sendMessage("Insufficient amount");
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
                    $player = Server::getInstance()->getPlayerExact($args[1]);
                    $amount = (int)$args[3];
                    $resinType = (string)$args[2];

                    $result = ResinAPI::getInstance()->reduceResin($player, $amount, $resinType);

                    switch ($result) {
                        case ResinAPI::RET_SUCCESS:
                            $sender->sendMessage("Take {$amount} {$resinType} resin from " . $player->getName());
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
                        case ResinAPI::RET_INSUFFICENT_AMOUNT:
                            $sender->sendMessage("Insufficient amount");
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