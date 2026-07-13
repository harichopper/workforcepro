<?php
declare(strict_types=1);

/**
 * Database - Singleton PDO connection using environment variables.
 * Credentials should be set via environment variables or config.
 */
class Database
{
    private static ?PDO $connection = null;

    private static function env(string $key, string $default = ''): string
    {
        $v = getenv($key);
        return $v !== false ? $v : $default;
    }

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $host    = self::env('DB_HOST', '127.0.0.1');
            $port    = self::env('DB_PORT', '3306');
            $name    = self::env('DB_NAME', 'workforcepro');
            $user    = self::env('DB_USER', 'root');
            $pass    = self::env('DB_PASS', '');
            $charset = 'utf8mb4';

            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";

            self::$connection = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$connection;
    }
}
