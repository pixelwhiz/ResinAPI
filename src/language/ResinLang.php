<?php

/*
 *   _____           _                _____ _____
 *  |  __ \         (_)         /\   |  __ \_   _|
 *  | |__) |___  ___ _ _ __    /  \  | |__) || |
 *  |  _  // _ \/ __| | '_ \  / /\ \ |  ___/ | |
 *  | | \ \  __/\__ \ | | | |/ ____ \| |    _| |_
 *  |_|  \_\___||___/_|_| |_/_/    \_\_|   |_____|
 *
 * ResinAPI - Advanced Resin Economy System for PocketMine-MP
 * Copyright (C) 2024 pixelwhiz
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace pixelwhiz\resinapi\language;

use pixelwhiz\resinapi\ResinAPI;
use pocketmine\lang\Language;
use pocketmine\lang\Translatable;
use pocketmine\utils\Config;
use InvalidArgumentException;

/**
 * ResinLang - Custom language handler for ResinAPI
 *
 * Extends PocketMine's Language system to provide resin-specific translations
 * with additional formatting capabilities. Handles loading and processing
 * of language files from the plugin's languages directory.
 *
 * @package pixelwhiz\resinapi\language
 * @extends Language
 */
class ResinLang extends Language {

    /**
     * Main plugin instance reference
     * @var ResinAPI
     */
    private ResinAPI $plugin;

    /**
     * Plugin configuration container
     * @var Config
     */
    private Config $config;

    /**
     * Constructor - Initializes language system
     *
     * @param ResinAPI $plugin Main plugin instance
     * @throws InvalidArgumentException If specified language file is not found
     */
    public function __construct(ResinAPI $plugin) {
        $this->plugin = $plugin;
        $this->config = $plugin->config;

        $language = $this->config->get('default-lang', 'en-US');
        $languagePath = $this->plugin->getDataFolder() . "languages/";

        if (!file_exists($languagePath . $language . ".ini")) {
            throw new InvalidArgumentException(
                "Language file '{$language}.ini' not found in directory '{$languagePath}'"
            );
        }

        parent::__construct($language, $languagePath, "en-US");
    }

    /**
     * Translates a message with variable substitution
     *
     * @param string $message The message key or raw message to translate
     * @param array $variables Associative array of [placeholder => value] for substitution
     * @return string The translated message with variables replaced
     *
     * @example
     * $lang->translateToString("resin.added", [
     *     TranslationKeys::RESIN_TYPE => "Original",
     *     TranslationKeys::AMOUNT => 40
     * ]);
     */
    public function translateToString(string $message, array $variables = []): string {
        $translated = $this->translate(new Translatable($message));
        return str_replace(
            array_keys($variables),
            array_values($variables),
            $translated
        );
    }

    /**
     * Gets the currently loaded language code
     *
     * @return string The current language code (e.g. "en-US")
     */
    public function getCurrentLanguage(): string {
        return $this->getLang();
    }
}