<?php

namespace App\Service;

class NumberGeneratorService
{
    public function generateRandomNumber(int $min = 0, int $max = 100): int
    {
        return random_int($min, $max);
    }
}