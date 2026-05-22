<?php

namespace App\Service;

use App\Entity\Garden;

final class GardenBalanceService
{
    /**
     * @return array{population:int, food:int, treasury:int, prosperity:string, danger:string}
     */
    public function calculateStatus(Garden $garden, float $interference = 0): array
    {
        $namePower = mb_strlen($garden->getName());
        $descriptionPower = mb_strlen($garden->getDescription());

        $basePopulation = 2200 + ($namePower * 90) + ($descriptionPower * 35);
        $population = (int) round($basePopulation * (1 + ($interference * 0.02)));

        $food = max(0, (int) round(($population * 0.72) - (350 + ($interference * 40))));
        $treasury = max(0, (int) round(($population * 0.65) + ($food * 0.25) - ($interference * 120)));

        $balance = $food - (int) round($population * 0.4);
        $danger = $balance < 0 ? '食糧不足注意' : ($interference >= 6 ? '怪獣警報' : '安定');

        $prosperity = match (true) {
            $population >= 10000 && $treasury >= 7000 => '超繁栄',
            $population >= 7000 && $treasury >= 5000 => '繁栄',
            $population >= 4500 => '発展中',
            default => '開拓期',
        };

        return [
            'population' => $population,
            'food' => $food,
            'treasury' => $treasury,
            'prosperity' => $prosperity,
            'danger' => $danger,
        ];
    }
}
