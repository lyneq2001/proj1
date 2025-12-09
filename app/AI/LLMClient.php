<?php

namespace App\AI;

use RuntimeException;

class LLMClient
{
    private string $binaryPath;
    private string $modelPath;
    private int $maxTokens;
    private float $temperature;
    private int $maxOffersForAiScoring;

    public function __construct()
    {
        $configPath = __DIR__ . '/../../config/llm-config.php';
        if (!file_exists($configPath)) {
            throw new RuntimeException('Missing LLM configuration file at ' . $configPath);
        }

        $config = require $configPath;
        $this->binaryPath = (string)($config['binaryPath'] ?? '');
        $this->modelPath = (string)($config['modelPath'] ?? '');
        $this->maxTokens = (int)($config['maxTokens'] ?? 256);
        $this->temperature = (float)($config['temperature'] ?? 0.7);
        $this->maxOffersForAiScoring = (int)($config['maxOffersForAiScoring'] ?? 100);

        if ($this->binaryPath === '' || $this->modelPath === '') {
            throw new RuntimeException('binaryPath and modelPath must be provided in config/llm-config.php');
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

    public function getMaxOffersForAiScoring(): int
    {
        return $this->maxOffersForAiScoring;
    }
}
