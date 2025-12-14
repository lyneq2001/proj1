<?php
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

try {
    $contextProvider = new DatabaseContextProvider($pdo);
    $snapshot = $contextProvider->getContextSnapshot();
    $databaseContext = $contextProvider->formatSnapshotForPrompt($snapshot);
} catch (\Throwable $e) {
    error_log('AI assistant DB context error: ' . $e->getMessage());
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
