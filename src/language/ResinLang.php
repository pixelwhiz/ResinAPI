<?php


namespace pixelwhiz\resinapi\language;


use pixelwhiz\resinapi\ResinAPI;
use pocketmine\lang\Language;

use pocketmine\lang\Translatable;
use pocketmine\utils\Config;

use InvalidArgumentException;

class ResinLang extends Language {

    private ResinAPI $plugin;
    private Config $config;

    public function __construct(ResinAPI $plugin)
    {
        $this->plugin = $plugin;
        $this->config = $plugin->config;
        $language = $this->config->get('default-lang');
        $languagePath = $this->plugin->getDataFolder() . "languages/";

        if (!file_exists($languagePath . $language . ".ini")) {
            throw new InvalidArgumentException("Language file for '{$language}' not found in  '{$languagePath}'");
        }

        parent::__construct($language, $languagePath, "en-US");
    }


    public function translateToString(string $message): string {
        return $this->translate(new Translatable($message));
    }

}