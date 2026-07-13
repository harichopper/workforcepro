<?php
declare(strict_types=1);

class Setting extends BaseModel
{
    protected string $table = 'settings';

    private array $cache = [];

    public function get(string $key, string $default = ''): string
    {
        if (!isset($this->cache[$key])) {
            $row = $this->fetchOne('SELECT setting_value FROM settings WHERE setting_key = :k', ['k' => $key]);
            $this->cache[$key] = $row ? ($row['setting_value'] ?? $default) : $default;
        }
        return $this->cache[$key];
    }

    public function set(string $key, string $value): void
    {
        $this->execute(
            'INSERT INTO settings (setting_key, setting_value) VALUES (:k, :v)
             ON DUPLICATE KEY UPDATE setting_value = :v',
            ['k' => $key, 'v' => $value]
        );
        $this->cache[$key] = $value;
    }

    public function getAll(): array
    {
        $rows   = $this->fetchAll('SELECT setting_key, setting_value FROM settings');
        $result = [];
        foreach ($rows as $row) {
            $result[$row['setting_key']] = $row['setting_value'];
        }
        return $result;
    }

    public function saveMany(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->set($key, (string) $value);
        }
    }
}
