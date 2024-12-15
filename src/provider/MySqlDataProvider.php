<?php

namespace pixelwhiz\resinapi\provider;

use pixelwhiz\resinapi\provider\Provider;
use pixelwhiz\resinapi\ResinAPI;
use pixelwhiz\resinapi\ResinTypes;
use pocketmine\utils\Config;

use mysqli;
use InvalidArgumentException;
use RuntimeException;

class MySqlDataProvider implements Provider {

    private array $resin = [];
    private mysqli $data;
    private ResinAPI $plugin;
    private Config $config;

    public function __construct(ResinAPI $plugin) {
        $this->plugin = $plugin;
        $this->config = $plugin->config;

        $config = $this->config->get("database")["mysql"];

        $host = $config["host"];
        $username = $config["username"];
        $password = $config["password"];
        $database = $config["schema"];
        $port = $config["port"];

        $this->data = new mysqli($host, $username, $password, $database, $port  );

        if ($this->data->connect_error) {
            throw new RuntimeException("Failed to conenct to MySQL: ". $this->data->connect_error);
        }

        $this->initializeTables();
    }

    public function initializeTables(): void {
        $query = "
            CREATE TABLE IF NOT EXISTS resin (
                player_name VARCHAR(255) PRIMARY KEY,
                original_resin INT NOT NULL,
                condensed_resin INT NOT NULL,
                fragile_resin INT NOT NULL
            );
        ";

        if (!$this->data->query($query)) {
            throw new RuntimeException("Failed to create table: ". $this->data->error);
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
            $query = "
                INSERT INTO resin (player_name, original_resin, condensed_resin, fragile_resin)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                                 original_resin = VALUES(original_resin),
                                 condensed_resin = VALUES(condensed_resin),
                                 fragile_resin = VALUES(fragile_resin)
            ";

            $stmt = $this->data->prepare($query);
            if ($stmt === false) {
                throw new RuntimeException("Failed to prepare statement: ". $this->data->error);
            }

            $stmt->bind_param(
                "siii",
                $playerName,
                $resinData[ResinTypes::ORIGINAL_RESIN],
                $resinData[ResinTypes::CONDENSED_RESIN],
                $resinData[ResinTypes::FRAGILE_RESIN],
            );

            if (!$stmt->execute()) {
                throw new RuntimeException("Failed to execute statement: ". $stmt->error);
            }

            $stmt->close();
        }
    }

    public function open(): void {
        $query = "SELECT player_name, original_resin, condensed_resin, fragile_resin FROM resin";
        $result = $this->data->query($query);

        if ($result === false) {
            throw new RuntimeException("Failed to execute query: ". $this->data->error);
        }

        while ($row = $result->fetch_assoc()) {
            $this->resin[$row['player_name']] = [
                ResinTypes::ORIGINAL_RESIN => (int) $row['original_resin'],
                ResinTypes::CONDENSED_RESIN => (int) $row['condensed_resin'],
                ResinTypes::FRAGILE_RESIN => (int) $row['fragile_resin'],
            ];
        }

        $result->free();
    }

}