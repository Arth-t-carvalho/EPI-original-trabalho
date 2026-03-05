<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Model\CollectedItem;
use App\Domain\Repository\CollectedItemRepository;

class SQLiteCollectedItemRepository implements CollectedItemRepository
{
    private \PDO $pdo;

    public function __construct()
    {
        $dbPath = __DIR__ . '/../../../database/database.sqlite';
        $this->pdo = new \PDO("sqlite:" . $dbPath);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->initialize();
    }

    private function initialize(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS collected_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT UNIQUE NOT NULL,
                timestamp DATETIME NOT NULL
            )
        ");
    }

    public function save(CollectedItem $item): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO collected_items (code, timestamp) VALUES (?, ?)");
        $stmt->execute([
            $item->getCode(),
            $item->getTimestamp()->format('Y-m-d H:i:s')
        ]);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM collected_items ORDER BY timestamp DESC");
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $items = [];
        foreach ($results as $row) {
            $items[] = new CollectedItem(
                $row['code'],
                (int)$row['id'],
                new \DateTimeImmutable($row['timestamp'])
            );
        }

        return $items;
    }

    public function deleteAll(): void
    {
        $this->pdo->exec("DELETE FROM collected_items");
    }

    public function deleteById(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM collected_items WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function existsByCode(string $code): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM collected_items WHERE code = ?");
        $stmt->execute([$code]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
