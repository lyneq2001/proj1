<?php

namespace App\AI;

use RuntimeException;

class AISortingService
{
    private LLMClient $llmClient;

    public function __construct(LLMClient $llmClient)
    {
        $this->llmClient = $llmClient;
    }

    /**
     * @param array $offers Raw offers (associative arrays)
     * @param array $filters Filters interpreted from AI chat
     * @param array $weights User-provided weights
     * @param array $userPreferences Preferences calculated from history
     * @return array Sorted offers
     */
    public function sortOffers(array $offers, array $filters = [], array $weights = [], array $userPreferences = []): array
    {
        if (empty($offers)) {
            return $offers;
        }

        $limitedOffers = array_slice($offers, 0, $this->llmClient->getMaxOffersForAiScoring());

        $prompt = $this->buildPrompt($limitedOffers, $filters, $weights, $userPreferences);

        try {
            $response = $this->llmClient->chat($prompt);
        } catch (RuntimeException $e) {
            error_log('AISortingService LLM error: ' . $e->getMessage());
            return $this->fallbackSort($offers);
        }

        if (!isset($response['scores']) || !is_array($response['scores'])) {
            error_log('AISortingService received unexpected response: ' . print_r($response, true));
            return $this->fallbackSort($offers);
        }

        $scores = [];
        foreach ($response['scores'] as $scoreEntry) {
            if (!isset($scoreEntry['offer_id'], $scoreEntry['score'])) {
                continue;
            }
            $scores[(int)$scoreEntry['offer_id']] = (float)$scoreEntry['score'];
        }

        if (!empty($userPreferences['boosts']) && is_array($userPreferences['boosts'])) {
            foreach ($userPreferences['boosts'] as $offerId => $boost) {
                $scores[$offerId] = ($scores[$offerId] ?? 0) + $boost;
            }
        }

        if (empty($scores)) {
            return $this->fallbackSort($offers);
        }

        usort($offers, function (array $a, array $b) use ($scores) {
            $scoreA = $scores[(int)($a['id'] ?? 0)] ?? -INF;
            $scoreB = $scores[(int)($b['id'] ?? 0)] ?? -INF;
            if ($scoreA === $scoreB) {
                return 0;
            }
            return ($scoreA > $scoreB) ? -1 : 1;
        });

        return $offers;
    }

    private function buildPrompt(array $offers, array $filters, array $weights, array $userPreferences): string
    {
        $offersDescription = array_map(function ($offer) {
            $parts = [];
            $parts[] = 'ID:' . ($offer['id'] ?? '');
            $parts[] = 'price:' . ($offer['price'] ?? '');
            $parts[] = 'size:' . ($offer['size'] ?? '');
            $parts[] = 'rooms:' . ($offer['rooms'] ?? '');
            $parts[] = 'floor:' . ($offer['floor'] ?? '');
            $parts[] = 'city:' . ($offer['city'] ?? '');
            $parts[] = 'street:' . ($offer['street'] ?? '');
            return implode(', ', $parts);
        }, $offers);

        $instruction = "Act as an assistant that scores apartment offers. Return JSON only with a `scores` array of objects {\"offer_id\": ID, \"score\": float 0-1}.";

        $filtersText = json_encode($filters, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $weightsText = json_encode($weights, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $preferencesText = json_encode($userPreferences, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return $instruction
            . "\nUser filters: " . $filtersText
            . "\nUser weights: " . $weightsText
            . "\nUser history/preferences: " . $preferencesText
            . "\nOffers to score:\n" . implode("\n", $offersDescription)
            . "\nRespond strictly in JSON.";
    }

    private function fallbackSort(array $offers): array
    {
        usort($offers, function ($a, $b) {
            $priceA = $a['price'] ?? PHP_INT_MAX;
            $priceB = $b['price'] ?? PHP_INT_MAX;
            return $priceA <=> $priceB;
        });
        return $offers;
    }
}
