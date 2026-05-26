<?php

namespace App\Service;

class StoryboardService
{
    public function buildChallenge(string $period, string $title, string $objective, ?\DateTimeImmutable $now = null): array
    {
        $now ??= new \DateTimeImmutable('now');

        $normalizedPeriod = trim($period);
        $normalizedTitle = trim($title);
        $normalizedObjective = trim($objective);

        return [
            'period' => $normalizedPeriod !== '' ? $normalizedPeriod : $this->defaultPeriod($now),
            'title' => $normalizedTitle !== '' ? $normalizedTitle : '無題チャプター',
            'objective' => $normalizedObjective !== '' ? $normalizedObjective : '目的未設定',
        ];
    }

    public function applyChallengeToWorld(array $world, array $challenge): array
    {
        $world['chapter'] = (string) ($challenge['title'] ?? '無題チャプター');
        $world['objective'] = (string) ($challenge['objective'] ?? '目的未設定');

        $challenges = is_array($world['semiannual_challenges'] ?? null) ? $world['semiannual_challenges'] : [];
        $challenges[] = [
            'period' => (string) ($challenge['period'] ?? ''),
            'title' => (string) ($challenge['title'] ?? '無題チャプター'),
            'objective' => (string) ($challenge['objective'] ?? '目的未設定'),
        ];

        $world['semiannual_challenges'] = array_slice($challenges, -12);

        return $world;
    }

    private function defaultPeriod(\DateTimeImmutable $now): string
    {
        $year = $now->format('Y');
        $month = (int) $now->format('n');

        return sprintf('%s-H%d', $year, $month <= 6 ? 1 : 2);
    }
}
