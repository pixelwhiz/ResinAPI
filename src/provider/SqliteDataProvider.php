<?php


namespace pixelwhiz\resinapi\provider;


use pixelwhiz\resinapi\provider\Provider;
use pixelwhiz\resinapi\ResinAPI;

class SqliteDataProvider implements Provider {

    public function __construct(ResinAPI $plugin) {
        $this->plugin = $plugin;
    }

}