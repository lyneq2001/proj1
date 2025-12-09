<?php

namespace App\AI;

use RuntimeException;

class LLMClient
{
    private string $driver;
    private string $binaryPath;
    private string $modelPath;
    private string $ollamaApiUrl;
    private string $modelName;
    private int $maxTokens;
    private float $temperature;
    private int $maxOffersForAiScoring;

    public function __construct()
    {
        $configPath = __DIR__ . '/../config/llm-config.php';
        if (!file_exists($configPath)) {
            throw new RuntimeException('Missing LLM configuration file at ' . $configPath);
        }

        $config = require $configPath;
        $this->driver = strtolower(trim((string)($config['driver'] ?? 'ollama')));
        $this->binaryPath = trim((string)($config['binaryPath'] ?? ''));
        $this->modelPath = trim((string)($config['modelPath'] ?? ''));
        $this->ollamaApiUrl = rtrim(trim((string)($config['ollamaApiUrl'] ?? 'http://127.0.0.1:11434/api/generate')), '/');
        $this->modelName = trim((string)($config['model'] ?? ''));
        $this->maxTokens = (int)($config['maxTokens'] ?? 256);
        $this->temperature = (float)($config['temperature'] ?? 0.7);
        $this->maxOffersForAiScoring = (int)($config['maxOffersForAiScoring'] ?? 100);

        if ($this->driver === 'binary') {
            $this->validateBinaryConfiguration();
        } else {
            $this->driver = 'ollama';
            $this->validateOllamaConfiguration();
        }
    }

    public function chat(string $prompt): array
    {
        // Keep execution time in check for long-running models.
        if (!function_exists('ini_set')) {
            @set_time_limit(30);
        } else {
            ini_set('max_execution_time', '30');
        }

        if ($this->driver === 'ollama') {
            return $this->chatWithOllama($prompt);
        }

        return $this->chatWithBinary($prompt);
    }

    public function getMaxOffersForAiScoring(): int
    {
        return $this->maxOffersForAiScoring;
    }

    private function validateBinaryConfiguration(): void
    {
        if ($this->binaryPath === '' || $this->modelPath === '') {
            throw new RuntimeException('binaryPath and modelPath must be provided in app/config/llm-config.php');
        }

        $binaryExtension = strtolower(pathinfo($this->binaryPath, PATHINFO_EXTENSION));
        if ($binaryExtension === 'gguf' || str_contains(strtolower($this->binaryPath), '.gguf')) {
            throw new RuntimeException('binaryPath should point to the LLM executable, not the .gguf model file.');
        }

        if (!is_file($this->binaryPath) || !is_executable($this->binaryPath)) {
            throw new RuntimeException('binaryPath must reference an existing executable file for the LLM binary.');
        }

        $modelExtension = strtolower(pathinfo($this->modelPath, PATHINFO_EXTENSION));
        if ($modelExtension !== 'gguf') {
            throw new RuntimeException('modelPath must reference a .gguf model file.');
        }
    }

    private function validateOllamaConfiguration(): void
    {
        if ($this->modelName === '') {
            throw new RuntimeException('model must be provided in app/config/llm-config.php when using the ollama driver.');
        }

        if ($this->ollamaApiUrl === '') {
            throw new RuntimeException('ollamaApiUrl must be configured when using the ollama driver.');
        }

        $parsed = parse_url($this->ollamaApiUrl);
        if ($parsed === false || !isset($parsed['scheme'], $parsed['host'])) {
            throw new RuntimeException('ollamaApiUrl must be a valid URL, e.g. http://127.0.0.1:11434/api/generate');
        }
    }

    private function chatWithBinary(string $prompt): array
    {
        $cmd = escapeshellcmd($this->binaryPath)
            . ' --model ' . escapeshellarg($this->modelPath)
            . ' --prompt ' . escapeshellarg($prompt)
            . ' --max-tokens ' . (int)$this->maxTokens
            . ' --temperature ' . escapeshellarg((string)$this->temperature)
            . ' --format json 2>&1';

        $output = shell_exec($cmd);
        if ($output === null || trim($output) === '') {
            throw new RuntimeException('No output received from LLM process. Command: ' . $cmd);
        }

        $decoded = json_decode($output, true);
        if ($decoded === null) {
            error_log('LLMClient failed to decode JSON. Raw output: ' . $output);
            throw new RuntimeException('Invalid JSON received from LLM.');
        }

        return $decoded;
    }

    private function chatWithOllama(string $prompt): array
    {
        $payload = json_encode([
            'model' => $this->modelName,
            'prompt' => $prompt,
            'stream' => false,
            'options' => [
                'temperature' => $this->temperature,
                'num_predict' => $this->maxTokens,
            ],
        ]);

        if ($payload === false) {
            throw new RuntimeException('Failed to encode request payload for Ollama.');
        }

        $ch = curl_init($this->ollamaApiUrl);
        if ($ch === false) {
            throw new RuntimeException('Unable to initialize cURL for Ollama request.');
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('Error communicating with Ollama: ' . $error);
        }

        $statusCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($statusCode !== 200) {
            throw new RuntimeException('Ollama returned status code ' . $statusCode . ' with body: ' . $response);
        }

        $decoded = json_decode($response, true);
        if ($decoded === null) {
            error_log('LLMClient failed to decode Ollama JSON. Raw output: ' . $response);
            throw new RuntimeException('Invalid JSON received from Ollama.');
        }

        return $decoded;
    }
}
