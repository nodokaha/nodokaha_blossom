<?php

namespace App\Tests\Controller;

use App\Entity\Garden;
use App\Entity\User;
use App\Repository\GardenRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class GardenControllerTest extends WebTestCase
{
    public function testGardenListDisplaysOtherUsersGardens(): void
    {
        $client = static::createClient();

        $owner = (new User())->setEmail('alice@example.com');
        $garden = (new Garden())
            ->setOwner($owner)
            ->setName('共有箱庭')
            ->setDescription('公開中の箱庭です');

        $gardenRepository = $this->createMock(GardenRepository::class);
        $gardenRepository->method('findBy')->willReturn([$garden]);

        static::getContainer()->set(GardenRepository::class, $gardenRepository);

        $client->request('GET', '/gardens');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'みんなの箱庭一覧');
        $this->assertSelectorTextContains('body', '共有箱庭');
    }

    public function testGardenDashboardDisplaysUsersGarden(): void
    {
        $client = static::createClient();

        $owner = (new User())->setEmail('bob@example.com');
        $garden = (new Garden())
            ->setOwner($owner)
            ->setName('専用箱庭')
            ->setDescription('自分の箱庭です');

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('find')->with(1)->willReturn($owner);

        $gardenRepository = $this->createMock(GardenRepository::class);
        $gardenRepository->method('findByOwnerId')->with(1)->willReturn([$garden]);

        static::getContainer()->set(UserRepository::class, $userRepository);
        static::getContainer()->set(GardenRepository::class, $gardenRepository);

        $client->request('GET', '/my-garden/1');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'bob@example.comさん専用の箱庭管理画面');
        $this->assertSelectorTextContains('body', '専用箱庭');
    }
}
