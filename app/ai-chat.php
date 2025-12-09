<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/AI/LLMClient.php';
require_once __DIR__ . '/AI/AISortingService.php';
require_once __DIR__ . '/AI/UserPreferencesService.php';

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

$systemPrompt = "Jesteś asystentem do wyszukiwania mieszkań. Użytkownik opisuje swoje wymagania. Zwróć w JSON pola:\n" .
    "filters: { max_price, min_price, min_rooms, max_rooms, min_floor, max_floor, preferred_districts[] itp. }\n" .
    "weights: { price, location, area, rooms }\n" .
    "explanation: krótki opis tekstowy.";

try {
    $client = new LLMClient();
    $response = $client->chat($systemPrompt . "\nZapytanie użytkownika: " . $message);

    $filters = $response['filters'] ?? [];
    $weights = $response['weights'] ?? [];
    $_SESSION['ai_filters'] = $filters;
    $_SESSION['ai_weights'] = $weights;

    echo json_encode([
        'reply' => $response['explanation'] ?? ($response['reply'] ?? ''),
        'filters' => $filters,
        'weights' => $weights,
    ]);
} catch (\Throwable $e) {
    error_log('AI chat error: ' . $e->getMessage());
    echo json_encode([
        'error' => 'AI niedostępne. Spróbuj ponownie później.',
        'filters' => [],
        'weights' => [],
    ]);
}
