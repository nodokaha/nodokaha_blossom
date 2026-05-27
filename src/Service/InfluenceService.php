<?php

namespace App\Service;

use App\Entity\User;

final class InfluenceService
{
    public function __construct(
        private readonly WorldStateService $worldStateService,
    ) {
    }

    public function processBroadcast(string $channel, int $impact, ?User $broadcaster = null): void
    {
        $message = [
            'type' => 'broadcast',
            'channel' => $channel,
            'impact' => max(-5, min(5, $impact)),
        ];

        if ($broadcaster) {
            $message['broadcaster'] = $broadcaster->getEmail();
        }

        $this->worldStateService->addBroadcast($channel, $message);
        $this->worldStateService->addToGlobalStack($message);
    }

    public function processInfluence(string $target, int $impact, ?User $influencer = null): void
    {
        $message = [
            'type' => 'influence',
            'target' => mb_strtolower($target),
            'impact' => max(-5, min(5, $impact)),
        ];

        if ($influencer) {
            $message['influencer'] = $influencer->getEmail();
        }

        // Add to global stack for other users to reference
        $this->worldStateService->addToGlobalStack($message);

        // Also log as chronicle event
        $this->worldStateService->addChronicleEntry(
            sprintf('Influence on %s: %+d', $target, $impact),
            $message
        );
    }

    public function calculateCumulativeImpact(string $target, array $influences): int
    {
        $impact = 0;
        foreach ($influences as $inf) {
            if (
                isset($inf['type']) && $inf['type'] === 'influence' &&
                isset($inf['target']) && mb_strtolower($inf['target']) === mb_strtolower($target)
            ) {
                $impact += (int) ($inf['impact'] ?? 0);
            }
        }

        return max(-5, min(5, $impact));
    }

    public function getTargetedInfluence(string $target): array
    {
        $globalStack = $this->worldStateService->getGlobalStack();

        return array_filter(
            $globalStack,
            fn($item) => isset($item['type']) && $item['type'] === 'influence' &&
                isset($item['target']) && mb_strtolower($item['target']) === mb_strtolower($target)
        );
    }

    public function getSharedInfluence(): array
    {
        $globalStack = $this->worldStateService->getGlobalStack();

        return array_filter(
            $globalStack,
            fn($item) => isset($item['type']) && $item['type'] === 'broadcast'
        );
    }

    public function recordInfluenceEvent(string $event, array $details = []): void
    {
        $this->worldStateService->addChronicleEntry($event, array_merge(['event_type' => 'influence'], $details));
    }
}
