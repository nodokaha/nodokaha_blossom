<?php

namespace App\Tests\Service;

use App\Entity\Garden;
use App\Entity\User;
use App\Service\GardenBalanceService;
use PHPUnit\Framework\TestCase;

final class GardenBalanceServiceTest extends TestCase
{
    public function testCalculateStatusReturnsExpectedKeysAndStableMode(): void
    {
        $garden = (new Garden())
            ->setOwner((new User())->setEmail('owner@example.com'))
            ->setName('みどり島')
            ->setDescription('森林と農場を中心にした箱庭');

        $service = new GardenBalanceService();
        $status = $service->calculateStatus($garden, 0);

        $this->assertSame(['population', 'food', 'treasury', 'prosperity', 'danger'], array_keys($status));
        $this->assertGreaterThan(0, $status['population']);
        $this->assertSame('安定', $status['danger']);
    }

    public function testHighInterferenceTriggersKaijuAlert(): void
    {
        $garden = (new Garden())
            ->setOwner((new User())->setEmail('owner@example.com'))
            ->setName('高干渉島')
            ->setDescription('実験環境');

        $service = new GardenBalanceService();
        $status = $service->calculateStatus($garden, 8);

        $this->assertSame('怪獣警報', $status['danger']);
    }
    public function testVeryLowInterferenceDoesNotProduceNegativePopulation(): void
    {
        $garden = (new Garden())
            ->setOwner((new User())->setEmail('owner@example.com'))
            ->setName('低干渉島')
            ->setDescription('干渉が大きく負のケース');

        $service = new GardenBalanceService();
        $status = $service->calculateStatus($garden, -60);

        $this->assertSame(0, $status['population']);
    }

}
