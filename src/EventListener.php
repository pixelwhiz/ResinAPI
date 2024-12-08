<?php

namespace pixelwhiz\resinapi;

use pixelwhiz\resinapi\provider\Provider;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

use RuntimeException;

class EventListener implements Listener {

    private ResinAPI $plugin;
    private Provider $provider;

    public function __construct(ResinAPI $plugin) {
        $this->plugin = $plugin;
        $this->provider = $plugin->provider;
    }

    public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();

        if (!$this->provider->accountExists($player)) {
            $this->provider->createAccount($player);
        }
    }

}