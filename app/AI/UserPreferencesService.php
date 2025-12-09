<?php

namespace App\AI;

use PDO;

class UserPreferencesService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->ensureHistoryTable();
    }

    public function recordAction(int $userId, int $offerId, string $actionType): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO user_offer_history (user_id, offer_id, action_type) VALUES (?, ?, ?)');
        $stmt->execute([$userId, $offerId, $actionType]);
    }

    public function getPreferences(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT o.id, o.price, o.city, o.rooms FROM user_offer_history h JOIN offers o ON h.offer_id = o.id WHERE h.user_id = ? ORDER BY h.created_at DESC LIMIT 200'
        );
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            return [];
        }

        $prices = array_column($rows, 'price');
        $rooms = array_column($rows, 'rooms');
        $districtCounts = [];
        foreach ($rows as $row) {
            $district = $row['city'] ?? 'unknown';
            $districtCounts[$district] = ($districtCounts[$district] ?? 0) + 1;
        }

        $avgPrice = array_sum($prices) / count($prices);
        $avgRooms = array_sum($rooms) / max(count($rooms), 1);
        arsort($districtCounts);
        $topDistricts = array_slice(array_keys($districtCounts), 0, 3);

        $boosts = [];
        foreach ($rows as $row) {
            if (!isset($row['id'])) {
                continue;
            }
            if (in_array($row['city'], $topDistricts, true)) {
                $boosts[(int)$row['id']] = 0.05;
            }
        }

        return [
            'average_price' => $avgPrice,
            'typical_rooms' => $avgRooms,
            'top_districts' => $topDistricts,
            'boosts' => $boosts,
        ];
    }

    private function ensureHistoryTable(): void
    {
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS user_offer_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                offer_id INT NOT NULL,
                action_type VARCHAR(50) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user (user_id),
                INDEX idx_offer (offer_id),
                INDEX idx_action (action_type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }
}
