<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Garden;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

final class UserGardenRelationTest extends TestCase
{
    public function testAddGardenSetsOwningSide(): void
    {
        $user = new User();
        $garden = new Garden();

        $user->addGarden($garden);

        $this->assertCount(1, $user->getGardens());
        $this->assertSame($user, $garden->getOwner());
    }
}
