<?php


namespace pixelwhiz\resinapi\event\resin;


use pixelwhiz\resinapi\event\ResinAPIEvent;
use pixelwhiz\resinapi\ResinAPI;

class AddResinEvent extends ResinAPIEvent {

    public function __construct(ResinAPI $plugin, string $playerName, int $amount) {
        parent::__construct($plugin);
        $this->playerName = $playerName;
        $this->amount = $amount;
    }

}