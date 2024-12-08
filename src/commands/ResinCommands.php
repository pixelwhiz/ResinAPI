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

class ResinCommands extends Command {

    private ResinAPI $plugin;
    private ResinLang $language;
    private Provider $provider;

    public function __construct(ResinAPI $plugin)
    {
        parent::__construct("resin", "Resin main commands", "Usage: /resin", []);
        $this->plugin = $plugin;
        $this->language = $plugin->language;
        $this->provider = $plugin->provider;
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
                if (!$sender->hasPermission("resin.command.help")) {
                    return false;
                }

            case "check":

                $this->setPermission("resinapi.command.check");
                if (!$this->testPermission($sender)) {
                    return false;
                }

                if (!$sender instanceof Player and !isset($args[1])) {
                    $sender->sendMessage("Usage: /resin check {player}");
                    return false;
                }

                if (isset($args[1])) {
                    $player = $this->plugin->getServer()->getOfflinePlayer($args[1]);
                    if ($player === null) {
                        $sender->sendMessage($this->language->translateToString("command.resin.player-not-found"));
                        return false;
                    }
                }

                $amount = $this->provider->getResin($player, ResinTypes::OIRIGINAL_RESIN);

                $sender->sendMessage($this->language->translateToString("command.resin.description",
                    [
                        TranslationKeys::PLAYER => $player->getName(),
                        TranslationKeys::AMOUNT => $amount
                    ]
                ));

            case "set":
            case "give":
            case "take":
            default:
                $sender->sendMessage($this->getUsage());
                break;
        }
    }

}