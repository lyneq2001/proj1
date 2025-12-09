<?php
return [
    // Wybierz sterownik LLM: 'ollama' (zalecane) lub 'binary'
    'driver' => 'ollama',

    // Konfiguracja dla Ollama (lokalny serwer HTTP)
    // - Zainstaluj Ollama: https://ollama.com/download
    // - Upewnij się, że działa na porcie 11434 (domyślnie `ollama serve`)
    // - Model pobierzesz poleceniem: `ollama run llama3`
    'ollamaApiUrl' => 'http://127.0.0.1:11434/api/generate',
    'model' => 'llama3',

    // Konfiguracja dla trybu "binary" (np. lokalny llama.cpp)
    // Podaj pełną ścieżkę do pliku wykonywalnego LLM (nie do pliku .gguf z modelem)
    'binaryPath' => '/usr/local/bin/llama',

    // Podaj pełną ścieżkę do modelu LLM w formacie .gguf
    'modelPath' => '/path/to/model.gguf',

    // Opcje wspólne
    'maxTokens' => 512,
    'temperature' => 0.7,
    'maxOffersForAiScoring' => 100,
];
