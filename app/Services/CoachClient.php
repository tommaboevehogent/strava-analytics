<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Thin wrapper around the Claude API (Anthropic) — turns a features array
 * into a short, structured training insight. Same shape as StravaClient:
 * a small HTTP-wrapper service around one external API, nothing fancier.
 */
class CoachClient
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';

    public function weeklyInsight(array $features): array
    {
        $response = Http::withHeaders([
            'x-api-key' => config('services.anthropic.api_key'),
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post(self::API_URL, [
            'model' => config('services.anthropic.model', 'claude-sonnet-4-6'),
            'max_tokens' => 400,
            'messages' => [
                ['role' => 'user', 'content' => $this->buildPrompt($features)],
            ],
        ])->throw();

        return $this->parseResponse($response->json());
    }

    private function buildPrompt(array $features): string
    {
        $json = json_encode($features);

        return <<<PROMPT
            Je bent een trainingscoach voor een duursporter. Hier zijn deze week's
            trainingscijfers, berekend uit hun Strava-data:

            {$json}

            Betekenis van de velden:
            - acwr: acute:chronic workload ratio. 0.8-1.3 is een gezonde zone,
              boven 1.5 is een verhoogd blessurerisico, onder 0.8 kan wijzen op
              te weinig opbouw.
            - efficiency_trend_pct: % verandering in snelheid-per-hartslag
              t.o.v. vier weken geleden. Positief is beter (conditieverbetering).
            - rest_days_last_7 en longest_current_rest_streak_days: rustpatroon.

            Geef antwoord als STRIKT JSON, geen andere tekst eromheen, geen
            markdown-codeblok (geen ```), in dit formaat:
            {"summary": "één zin samenvatting", "risk_level": "low|medium|high", "recommendation": "concreet advies, max 2 zinnen"}
            PROMPT;
    }

    private function parseResponse(array $payload): array
    {
        $text = $payload['content'][0]['text'] ?? null;

        $decoded = $text ? json_decode($this->stripCodeFence($text), true) : null;

        if (! is_array($decoded) || ! isset($decoded['summary'])) {
            throw new RuntimeException('Coach response was not valid JSON: '.($text ?? 'empty'));
        }

        return $decoded;
    }

    /**
     * Claude sometimes wraps its JSON answer in a markdown code fence
     * (```json ... ```) despite being told not to — strip that off before
     * decoding, rather than relying purely on prompt instructions.
     */
    private function stripCodeFence(string $text): string
    {
        $text = trim($text);

        if (str_starts_with($text, '```')) {
            $text = preg_replace('/^```[a-zA-Z]*\n?/', '', $text);
            $text = preg_replace('/```$/', '', $text);
        }

        return trim($text);
    }
}
