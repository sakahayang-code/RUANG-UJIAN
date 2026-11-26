<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;
    private static ?string $customDbPath = null;

    public static function setPath(string $path): void
    {
        self::$customDbPath = $path;
        self::$instance = null; // reset instance for testing or alternative db
    }

    public static function getDatabasePath(): string
    {
        if (self::$customDbPath) {
            return self::$customDbPath;
        }
        $config = require dirname(__DIR__, 2) . '/config/database.php';
        return $config['database'];
    }

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dbPath = self::getDatabasePath();
            $dbDir = dirname($dbPath);
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0755, true);
            }

            $isNew = !file_exists($dbPath);

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 5,
            ];

            try {
                self::$instance = new PDO("sqlite:" . $dbPath, null, null, $options);
                self::$instance->exec("PRAGMA foreign_keys = ON;");
                self::$instance->exec("PRAGMA journal_mode = WAL;");
                self::$instance->exec("PRAGMA busy_timeout = 5000;");
            } catch (PDOException $e) {
                die("Koneksi Database SQLite Gagal: " . $e->getMessage());
            }
        }

        return self::$instance;
    }

    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function fetch(string $sql, array $params = []): ?array
    {
        $result = self::query($sql, $params)->fetch();
        return $result ?: null;
    }

    public static function fetchColumn(string $sql, array $params = [], int $columnNumber = 0)
    {
        $stmt = self::query($sql, $params);
        return $stmt->fetchColumn($columnNumber);
    }

    public static function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        $fields = implode(', ', $columns);
        $placeholders = ':' . implode(', :', $columns);

        $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})";
        self::query($sql, $data);
        return (int) self::getConnection()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $setClauses = [];
        $params = [];

        foreach ($data as $col => $val) {
            $setClauses[] = "{$col} = :set_{$col}";
            $params["set_{$col}"] = $val;
        }

        // Support both named (:where_id) and positional (?) parameters in $where
        if (!empty($whereParams)) {
            $isPositional = false;
            foreach ($whereParams as $k => $v) {
                if (is_int($k)) {
                    $isPositional = true;
                    break;
                }
            }

            if ($isPositional) {
                $idx = 0;
                $where = preg_replace_callback('/\?/', function () use (&$idx, $whereParams, &$params) {
                    $paramName = "where_pos_{$idx}";
                    $params[$paramName] = $whereParams[$idx] ?? null;
                    $idx++;
                    return ":{$paramName}";
                }, $where);
            } else {
                $params = array_merge($params, $whereParams);
            }
        }

        $setStr = implode(', ', $setClauses);

        $sql = "UPDATE {$table} SET {$setStr} WHERE {$where}";
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }

    public static function delete(string $table, string $where, array $whereParams = []): int
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = self::query($sql, $whereParams);
        return $stmt->rowCount();
    }

    public static function beginTransaction(): bool
    {
        return self::getConnection()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::getConnection()->commit();
    }

    public static function rollBack(): bool
    {
        return self::getConnection()->rollBack();
    }

    public static function inTransaction(): bool
    {
        return self::getConnection()->inTransaction();
    }

    public static function lastInsertId(): string
    {
        return self::getConnection()->lastInsertId();
    }
}
