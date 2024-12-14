<?php

namespace pixelwhiz\resinapi\event;

use pixelwhiz\resinapi\ResinAPI;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\plugin\PluginEvent;

class ResinAPIEvent extends PluginEvent implements Cancellable {

    use CancellableTrait;

    public function __construct(ResinAPI $plugin) {
        parent::__construct($plugin);
    }

}