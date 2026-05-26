<?php

namespace App\Tests\Service;

use App\Service\StoryboardService;
use PHPUnit\Framework\TestCase;

class StoryboardServiceTest extends TestCase
{
    public function testBuildChallengeUsesFallbackValues(): void
    {
        $service = new StoryboardService();

        $challenge = $service->buildChallenge(' ', ' ', ' ', new \DateTimeImmutable('2026-08-10'));

        $this->assertSame('2026-H2', $challenge['period']);
        $this->assertSame('無題チャプター', $challenge['title']);
        $this->assertSame('目的未設定', $challenge['objective']);
    }

    public function testBuildChallengeUsesSubmittedValues(): void
    {
        $service = new StoryboardService();

        $challenge = $service->buildChallenge('2026-H1', 'CHAPTER X', '開花率を40%にする');

        $this->assertSame('2026-H1', $challenge['period']);
        $this->assertSame('CHAPTER X', $challenge['title']);
        $this->assertSame('開花率を40%にする', $challenge['objective']);
    }

    public function testApplyChallengeToWorldUpdatesCurrentAndKeepsLatest12(): void
    {
        $service = new StoryboardService();

        $world = [
            'chapter' => 'old',
            'objective' => 'old',
            'semiannual_challenges' => array_map(
                static fn (int $i): array => ['period' => sprintf('2024-H%d', ($i % 2) + 1), 'title' => 't'.$i, 'objective' => 'o'.$i],
                range(1, 12)
            ),
        ];

        $updated = $service->applyChallengeToWorld($world, [
            'period' => '2026-H2',
            'title' => 'CHAPTER NEW',
            'objective' => 'new objective',
        ]);

        $this->assertSame('CHAPTER NEW', $updated['chapter']);
        $this->assertSame('new objective', $updated['objective']);
        $this->assertCount(12, $updated['semiannual_challenges']);
        $this->assertSame('t2', $updated['semiannual_challenges'][0]['title']);
        $this->assertSame('CHAPTER NEW', $updated['semiannual_challenges'][11]['title']);
    }
}
