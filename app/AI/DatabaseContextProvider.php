<?php
declare(strict_types=1);

namespace App\AI;

use PDO;
use PDOException;
use RuntimeException;

class DatabaseContextProvider
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getContextSnapshot(int $latestOffersLimit = 5): array
    {
        if ($latestOffersLimit < 1) {
            throw new RuntimeException('latestOffersLimit must be a positive integer');
        }

        $counts = $this->buildCounts();
        $latestOffers = $this->getLatestOffers($latestOffersLimit);

        return [
            'counts' => $counts,
            'latest_offers' => $latestOffers,
            'generated_at' => date('c'),
        ];
    }

    public function formatSnapshotForPrompt(array $snapshot): string
    {
        $lines = [];

        if (!empty($snapshot['counts']) && is_array($snapshot['counts'])) {
            $lines[] = 'Statystyki:';
            foreach ($snapshot['counts'] as $key => $value) {
                $label = ucwords(str_replace('_', ' ', (string)$key));
                $lines[] = "- {$label}: {$value}";
            }
        }

        if (!empty($snapshot['latest_offers']) && is_array($snapshot['latest_offers'])) {
            $lines[] = 'Najnowsze oferty (tylko dane niezbędne do odpowiedzi):';
            foreach ($snapshot['latest_offers'] as $offer) {
                $parts = [];
                if (isset($offer['id'])) {
                    $parts[] = 'ID ' . $offer['id'];
                }
                if (!empty($offer['title'])) {
                    $parts[] = (string)$offer['title'];
                }
                if (!empty($offer['city'])) {
                    $parts[] = 'Miasto: ' . $offer['city'];
                }
                if (isset($offer['price'])) {
                    $parts[] = 'Cena: ' . $offer['price'] . ' PLN';
                }
                if (isset($offer['rooms'])) {
                    $parts[] = 'Pokoje: ' . $offer['rooms'];
                }
                if (isset($offer['size'])) {
                    $parts[] = 'Metraż: ' . $offer['size'] . ' m2';
                }
                if (!empty($offer['status'])) {
                    $parts[] = 'Status: ' . $offer['status'];
                }
                $lines[] = '- ' . implode(', ', $parts);
            }
        }

        if (!empty($snapshot['generated_at'])) {
            $lines[] = 'Źródło: bieżąca baza danych, stan na ' . $snapshot['generated_at'];
        }

        return implode("\n", $lines);
    }

    private function buildCounts(): array
    {
        $counts = [];

        if ($this->tableExists('offers')) {
            $counts['offers_total'] = $this->safeCount('SELECT COUNT(*) FROM offers');

            if ($this->columnExists('offers', 'status')) {
                $counts['offers_active'] = $this->safeCount("SELECT COUNT(*) FROM offers WHERE status = 'active'");
                $counts['offers_pending'] = $this->safeCount("SELECT COUNT(*) FROM offers WHERE status != 'active'");
            }
        }

        if ($this->tableExists('users')) {
            $counts['users_total'] = $this->safeCount('SELECT COUNT(*) FROM users');
        }

        if ($this->tableExists('messages')) {
            $counts['messages_total'] = $this->safeCount('SELECT COUNT(*) FROM messages');
        }

        if ($this->tableExists('reports')) {
            if ($this->columnExists('reports', 'status')) {
                $counts['reports_pending'] = $this->safeCount("SELECT COUNT(*) FROM reports WHERE status = 'pending'");
            } else {
                $counts['reports_total'] = $this->safeCount('SELECT COUNT(*) FROM reports');
            }
        }

        return $counts;
    }

    private function getLatestOffers(int $limit): array
    {
        if (!$this->tableExists('offers')) {
            return [];
        }

        $columns = array_filter([
            'id',
            'title',
            'city',
            'price',
            'rooms',
            'size',
            $this->columnExists('offers', 'status') ? 'status' : null,
            $this->columnExists('offers', 'created_at') ? 'created_at' : null,
        ]);

        if (empty($columns)) {
            return [];
        }

        $selectColumns = implode(', ', array_map(fn(string $column) => "`{$column}`", $columns));
        $orderBy = $this->columnExists('offers', 'created_at') ? 'created_at' : 'id';

        $stmt = $this->pdo->prepare("SELECT {$selectColumns} FROM offers ORDER BY {$orderBy} DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function tableExists(string $table): bool
    {
        $table = $this->sanitizeIdentifier($table);

        try {
            $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

            if ($driver === 'sqlite') {
                $stmt = $this->pdo->prepare("SELECT name FROM sqlite_master WHERE type = 'table' AND name = ? LIMIT 1");
                $stmt->execute([$table]);
                return (bool)$stmt->fetchColumn();
            }

            // Default to MySQL-compatible lookup
            $stmt = $this->pdo->prepare('SHOW TABLES LIKE ?');
            $stmt->execute([$table]);
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('AI DatabaseContextProvider tableExists error: ' . $e->getMessage());
            return false;
        }
    }

    private function columnExists(string $table, string $column): bool
    {
        $table = $this->sanitizeIdentifier($table);
        $column = $this->sanitizeIdentifier($column);

        try {
            $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

            if ($driver === 'sqlite') {
                $stmt = $this->pdo->prepare("PRAGMA table_info(`{$table}`)");
                $stmt->execute();

                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    if (($row['name'] ?? '') === $column) {
                        return true;
                    }
                }

                return false;
            }

            // Default to MySQL-compatible lookup
            $stmt = $this->pdo->prepare(
                'SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? LIMIT 1'
            );
            $stmt->execute([$table, $column]);
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('AI DatabaseContextProvider columnExists error: ' . $e->getMessage());
            return false;
        }
    }

    private function safeCount(string $query): int
    {
        try {
            $value = $this->pdo->query($query)->fetchColumn();
            return (int)$value;
        } catch (PDOException $e) {
            error_log('AI DatabaseContextProvider count error: ' . $e->getMessage());
            return 0;
        }
    }

    private function sanitizeIdentifier(string $identifier): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $identifier) ?? '';
    }
}
