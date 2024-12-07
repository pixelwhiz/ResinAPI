<?php

namespace pixelwhiz\resinapi\commands;

use pixelwhiz\resinapi\ResinAPI;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class ResinCommands extends Command {

    private ResinAPI $plugin;

    public function __construct(ResinAPI $plugin)
    {
        parent::__construct("resin", "Resin main commands", "Usage: /resin", []);
        $this->plugin = $plugin;
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

                $this->setPermission("res.command.check");
                if (!$this->testPermission($sender)) {
                    return false;
                }

                if (!$sender instanceof Player and !isset($args[1])) {
                    $sender->sendMessage("Usage: /resin check {player}");
                    return false;
                }

                $player = $sender instanceof Player? $sender : $this->plugin->getServer()->getPlayerExact($args[1]);


            case "set":
            case "give":
            case "take":
            default:
                $sender->sendMessage($this->getUsage());
                break;
        }
    }

}