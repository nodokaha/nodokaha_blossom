<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Garden;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

final class GardenTest extends TestCase
{
    public function testGardenPropertiesCanBeSetAndRetrieved(): void
    {
        $owner = (new User())->setEmail('owner@example.com');

        $garden = new Garden();
        $garden->setOwner($owner);
        $garden->setName('  テスト箱庭  ');
        $garden->setDescription('  説明文  ');

        $this->assertSame($owner, $garden->getOwner());
        $this->assertSame('テスト箱庭', $garden->getName());
        $this->assertSame('説明文', $garden->getDescription());
    }
}
