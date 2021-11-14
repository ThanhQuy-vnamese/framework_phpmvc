<?php

namespace App\Core\Database;

use App\Core\Application;

class Database
{
    public $mysql;

    public function __construct(array $config)
    {
        $host = $config['host'] ?? '';
        $username = $config['username'] ?? '';
        $password = $config['password'] ?? '';
        $database = $config['database'] ?? '';
        $this->mysql = mysqli_connect($host, $username, $password, $database);
        $this->mysql->set_charset('utf8');
    }

    public function applyMigrations()
    {
        $this->createMigrationsTable();
        $appliedMigrations = $this->getApplyMigrations();
        $migrationConverted = $this->covertMigrations($appliedMigrations);
        $newMigrations = [];
        $files = scandir(Application::$ROOT_DIR . '/migrations');
        $toAppliedMigrations = array_diff($files, $migrationConverted);
        foreach ($toAppliedMigrations as $migration) {
            if ($migration === '.' || $migration === '..') {
                continue;
            }

            require_once Application::$ROOT_DIR . '/migrations/' . $migration;
            $classname = pathinfo($migration, PATHINFO_FILENAME);
            $instance = new $classname();
            $this->log("Apply migration $migration");
            $instance->up();
            $this->log("Apply migration $migration");
            $newMigrations[] = $migration;
        }

        if (!empty($newMigrations)) {
            $this->saveMigration($newMigrations);
        } else {
            $this->log('All migration are applied');
        }
    }

    public function covertMigrations(array $migrations): array
    {
        $result = [];
        foreach ($migrations as $migration) {
            $result[] = $migration[0];
        }

        return array_unique($result);
    }

    public function createMigrationsTable()
    {
        $query = "CREATE TABLE IF NOT EXISTS migrations(
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    migration VARCHAR(255),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=INNODB;";
        $this->mysql->query($query);
    }

    public function getApplyMigrations(): ?array
    {
        $query = "SELECT migration FROM migrations";
        $result = $this->mysql->query($query);

        return $result->fetch_all();
    }

    public function saveMigration(array $migrations)
    {
        $migrations = array_map(fn($m) => "('$m')", $migrations);
        $values = implode(',', $migrations);
        $query = "INSERT INTO migrations (migration) VALUES $values";
        $this->mysql->query($query);
    }

    protected function log(string $message)
    {
        echo '[' . date('Y-m-d H:i:s') . '] - ' . $message . PHP_EOL;
    }

//    public function query(string $query) {
//        return $this->query($query);
//    }
}
