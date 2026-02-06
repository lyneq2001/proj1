<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/AI/LLMClient.php';
require_once __DIR__ . '/AI/DatabaseContextProvider.php';

use App\AI\LLMClient;
use App\AI\DatabaseContextProvider;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method.']);
    exit;
}

$message = trim($_POST['message'] ?? '');
if ($message === '') {
    echo json_encode(['error' => 'Message cannot be empty.']);
    exit;
}

$databaseContext = 'Dane z bazy są tymczasowo niedostępne. Jeśli użytkownik pyta o liczby lub dane, poinformuj o braku dostępu.';
$snapshot = [];

try {
    $contextProvider = new DatabaseContextProvider($pdo);
    $snapshot = $contextProvider->getContextSnapshot();
    $databaseContext = $contextProvider->formatSnapshotForPrompt($snapshot);
} catch (\Throwable $e) {
    error_log('AI assistant DB context error: ' . $e->getMessage());
}

function detectLanguage(string $message): string
{
    if (preg_match('/[ąćęłńóśżź]/i', $message)) {
        return 'pl';
    }

    if (preg_match('/\b(ile|ofert|mieszkan|mieszkaniowych|mieszkania|oferty)\b/i', $message)) {
        return 'pl';
    }

    return 'en';
}

function isOfferCountQuestion(string $message): bool
{
    $hasCountKeyword = preg_match('/\b(ile|liczba|count|how many|number of)\b/i', $message);
    $hasOfferKeyword = preg_match('/\b(ofert|oferty|offer|offers|listings|listing|mieszkan|mieszkania|mieszkaniowych|apartment|apartments)\b/i', $message);

    return (bool)($hasCountKeyword && $hasOfferKeyword);
}

function buildOfferCountReply(array $counts, ?int $userOfferCount, string $language): ?string
{
    $total = $counts['offers_total'] ?? null;
    $active = $counts['offers_active'] ?? null;
    $pending = $counts['offers_pending'] ?? null;

    if ($total === null && $userOfferCount === null) {
        return null;
    }

    if ($language === 'pl') {
        $parts = [];

        if ($userOfferCount !== null) {
            $parts[] = "Na Twoim koncie jest {$userOfferCount} ofert.";
        }

        if ($total !== null) {
            $parts[] = "Łącznie na stronie jest {$total} ofert.";
        }

        if ($active !== null && $pending !== null) {
            $parts[] = "Aktywne: {$active}, oczekujące/nieaktywne: {$pending}.";
        }

        return implode(' ', $parts);
    }

    $parts = [];

    if ($userOfferCount !== null) {
        $parts[] = "You have {$userOfferCount} offers in your account.";
    }

    if ($total !== null) {
        $parts[] = "There are {$total} offers on the site.";
    }

    if ($active !== null && $pending !== null) {
        $parts[] = "Active: {$active}, pending/inactive: {$pending}.";
    }

    return implode(' ', $parts);
}

if (isOfferCountQuestion($message)) {
    $language = detectLanguage($message);
    $userOfferCount = null;

    if (isset($_SESSION['user_id'])) {
        try {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM offers WHERE user_id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            $userOfferCount = (int)$stmt->fetchColumn();
        } catch (\Throwable $e) {
            error_log('AI assistant offer count error: ' . $e->getMessage());
        }
    }

    $reply = buildOfferCountReply($snapshot['counts'] ?? [], $userOfferCount, $language);

    if ($reply !== null) {
        echo json_encode(['reply' => $reply]);
        exit;
    }

    $fallback = $language === 'pl'
        ? 'Nie mam teraz dostępu do liczby ofert w bazie. Spróbuj ponownie za chwilę.'
        : 'I cannot access the offer counts right now. Please try again later.';

    echo json_encode(['reply' => $fallback]);
    exit;
}

$systemPrompt = "Jesteś pomocnym asystentem AI na portalu z ogłoszeniami mieszkaniowymi. " .
    "Najpierw wykryj, czy użytkownik pisze po polsku czy po angielsku i odpowiadaj wyłącznie w wykrytym języku. " .
    "Masz dostęp do świeżych danych z bazy (statystyki oraz najnowsze oferty) i używasz ich do precyzyjnych odpowiedzi. " .
    "Podawaj liczby na podstawie kontekstu bazy, a jeśli danych brakuje, powiedz o tym. " .
    "Odpowiadasz zwięźle, pomagając w wyszukiwaniu mieszkań, korzystaniu z formularzy, kontakcie z właścicielami i nawigacji " .
    "po serwisie. Jeśli użytkownik prosi o działania spoza funkcji serwisu lub dane wrażliwe, grzecznie odmów.\n\n" .
    "Aktualne dane z bazy:\n" . $databaseContext . "\n\nPytanie użytkownika: " . $message;

try {
    $client = new LLMClient();
    $response = $client->chat($systemPrompt);

    $reply = $response['reply']
        ?? $response['content']
        ?? $response['text']
        ?? $response['response']
        ?? ($response['message']['content'] ?? '')
        ?? '';

    echo json_encode([
        'reply' => $reply !== '' ? $reply : 'Przepraszam, nie udało mi się teraz udzielić odpowiedzi. Spróbuj ponownie.',
    ]);
} catch (\Throwable $e) {
    error_log('AI assistant chat error: ' . $e->getMessage());
    echo json_encode([
        'error' => 'AI niedostępne. Spróbuj ponownie później.',
    ]);
}
