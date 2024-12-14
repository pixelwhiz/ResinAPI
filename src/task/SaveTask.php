<?php


namespace pixelwhiz\resinapi\task;

use pixelwhiz\resinapi\ResinAPI;
use pocketmine\scheduler\Task;

class SaveTask extends Task {

    private ResinAPI $plugin;

    public function __construct(ResinAPI $plugin) {
        $this->plugin = $plugin;
    }

    public function onRun(): void
    {
        $this->plugin->saveAll();
    }

}