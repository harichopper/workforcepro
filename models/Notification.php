<?php
declare(strict_types=1);

class Notification extends BaseModel
{
    protected string $table = 'notifications';

    public function forUser(int $userId, int $limit = 10): array
    {
        return $this->fetchAll(
            'SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC LIMIT :limit',
            ['uid' => $userId, 'limit' => $limit]
        );
    }

    public function unreadCount(int $userId): int
    {
        $row = $this->fetchOne(
            'SELECT COUNT(*) AS total FROM notifications WHERE user_id = :uid AND read_at IS NULL',
            ['uid' => $userId]
        );
        return (int) ($row['total'] ?? 0);
    }

    public function markRead(int $id): bool
    {
        return $this->execute(
            'UPDATE notifications SET read_at = NOW() WHERE id = :id',
            ['id' => $id]
        );
    }

    public function markAllRead(int $userId): bool
    {
        return $this->execute(
            'UPDATE notifications SET read_at = NOW() WHERE user_id = :uid AND read_at IS NULL',
            ['uid' => $userId]
        );
    }

    public function push(int $userId, string $title, string $message, string $type = 'info'): void
    {
        $this->insert([
            'user_id' => $userId,
            'title'   => $title,
            'message' => $message,
            'type'    => $type,
        ]);
    }
}
