<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/AI/LLMClient.php';

use App\AI\LLMClient;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method.']);
    exit;
}

$message = trim($_POST['message'] ?? '');
if ($message === '') {
    echo json_encode(['error' => 'Message cannot be empty.']);
    exit;
}

$systemPrompt = "Jesteś pomocnym asystentem AI na portalu z ogłoszeniami mieszkaniowymi. " .
    "Najpierw wykryj, czy użytkownik pisze po polsku czy po angielsku i odpowiadaj wyłącznie w wykrytym języku. " .
    "Odpowiadasz zwięźle, pomagając w wyszukiwaniu mieszkań, korzystaniu z formularzy, kontakcie z właścicielami i nawigacji " .
    "po serwisie. Jeśli użytkownik prosi o działania spoza funkcji serwisu lub dane wrażliwe, grzecznie odmów.\n\nPytanie użytkownika: " . $message;

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
