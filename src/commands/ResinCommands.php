<?php

namespace pixelwhiz\resinapi\commands;

use pixelwhiz\resinapi\ResinAPI;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class ResinCommands extends Command {

    public function __construct(ResinAPI $plugin)
    {
        parent::__construct("resin", "Resin main commands", "Usage: /resin", []);
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("Only player can execute this command!");
            return false;
        }

        if (count($args) < 1) {
            $sender->sendMessage($this->getUsage());
            return false;
        }

        switch ($args[0]) {
            case "help":
            case "check":

            case "set":
            case "give":
            case "take":
            default:
                $sender->sendMessage($this->getUsage());
                break;
        }
    }

}