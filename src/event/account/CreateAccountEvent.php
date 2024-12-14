<?php


namespace pixelwhiz\resinapi\event\account;

use pixelwhiz\resinapi\event\ResinAPIEvent;
use pixelwhiz\resinapi\ResinAPI;

class CreateAccountEvent extends ResinAPIEvent {


    public function __construct(ResinAPI $plugin)
    {
        parent::__construct($plugin);
    }

}