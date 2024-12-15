<?php


namespace pixelwhiz\resinapi\provider;


use pixelwhiz\resinapi\provider\Provider;
use pixelwhiz\resinapi\ResinAPI;

use pixelwhiz\resinapi\ResinTypes;
use pocketmine\utils\Config;

use SQLite3;
use Exception;
use RuntimeException;
use InvalidArgumentException;

class SqliteDataProvider implements Provider {


    private array $resin = [];
    private SQLite3 $data;
    private ResinAPI $plugin;
    private Config $config;

    public function __construct(ResinAPI $plugin)
    {
        $this->plugin = $plugin;
        $this->config = $plugin->config;

        $dbPath = $this->plugin->getDataFolder() . "database/data.sqlite";
        if (!is_dir(dirname($dbPath))) {
            mkdir(dirname($dbPath), 0777, true);
        }

        $this->data = new SQLite3($dbPath);
        $this->initializeTables();
    }

    public function initializeTables(): void
    {
        if (!$this->data->exec("
            CREATE TABLE IF NOT EXISTS resin (
                player_name TEXT PRIMARY KEY,
                original_resin INTEGER NOT NULL,
                condensed_resin INTEGER NOT NULL,
                fragile_resin INTEGER NOT NULL
            );
        ")) {
            throw new RuntimeException("Failed to create table ". $this->data->lastErrorMsg());
        }
    }

    public function getDefaultResin(): mixed {
        return $this->config->get("default-resin");
    }

    public function accountExists(string $playerName): bool {
        return isset($this->resin[$playerName]);
    }

    public function createAccount(string $playerName): bool
    {
        if (!isset($this->resin[$playerName])) {
            $this->resin[$playerName] = [
                ResinTypes::ORIGINAL_RESIN => $this->config->get("default-resin")[ResinTypes::ORIGINAL_RESIN],
                ResinTypes::FRAGILE_RESIN => $this->config->get("default-resin")[ResinTypes::FRAGILE_RESIN],
                ResinTypes::CONDENSED_RESIN => $this->config->get("default-resin")[ResinTypes::CONDENSED_RESIN],
            ];
            return true;
        }
        return false;
    }

    public function getResin(string $playerName, string $resinType): int
    {
        if (isset($this->resin[$playerName])) {
            return match ($resinType) {
                ResinTypes::ORIGINAL_RESIN => $this->resin[$playerName][ResinTypes::ORIGINAL_RESIN],
                ResinTypes::FRAGILE_RESIN => $this->resin[$playerName][ResinTypes::FRAGILE_RESIN],
                ResinTypes::CONDENSED_RESIN => $this->resin[$playerName][ResinTypes::CONDENSED_RESIN],
                default => throw new InvalidArgumentException("ResinType {$resinType} not found!")
            };
        }
        return false;
    }

    public function getAllResin(string $playerName): array {
        return $this->resin[$playerName];
    }

    public function addResin(string $playerName, int $amount, string $resinType): bool {
        if (isset($this->resin[$playerName])) {
            $this->resin[$playerName][$resinType] += $amount;
            return true;
        }
        return false;
    }

    public function setResin(string $playerName, int $amount, string $resinType): bool {
        if (isset($this->resin[$playerName])) {
            $this->resin[$playerName][$resinType] = $amount;
            return true;
        }
        return false;
    }

    public function reduceResin(string $playerName, int $amount, string $resinType): bool {
        if (isset($this->resin[$playerName])) {
            $this->resin[$playerName][$resinType] -= $amount;
            return true;
        }
        return false;
    }


    public function getAll(): array {
        return array_keys($this->resin);
    }

    public function save(): void {
        foreach ($this->resin as $playerName => $resinData) {
            $stmt = $this->data->prepare("
                INSERT OR REPLACE INTO resin (player_name, original_resin, condensed_resin, fragile_resin)
                VALUES (:player_name, :original_resin, :condensed_resin, :fragile_resin)
            ");
            $stmt->bindValue(":player_name", $playerName, SQLITE3_TEXT);
            $stmt->bindValue(':original_resin', $resinData[ResinTypes::ORIGINAL_RESIN], SQLITE3_INTEGER);
            $stmt->bindValue(':condensed_resin', $resinData[ResinTypes::CONDENSED_RESIN], SQLITE3_INTEGER);
            $stmt->bindValue(':fragile_resin', $resinData[ResinTypes::FRAGILE_RESIN], SQLITE3_INTEGER);
            $stmt->execute();
        }
    }

    public function open(): void {
        $result = $this->data->query("SELECT * FROM resin");
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $this->resin[$row['player_name']] = [
                ResinTypes::ORIGINAL_RESIN => $row['original_resin'],
                ResinTypes::CONDENSED_RESIN => $row['condensed_resin'],
                ResinTypes::FRAGILE_RESIN => $row['fragile_resin'],
            ];
        }
    }


}