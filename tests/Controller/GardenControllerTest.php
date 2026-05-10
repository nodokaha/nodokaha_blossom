<?php

namespace App\Tests\Controller;

use App\Entity\Garden;
use App\Entity\User;
use App\Repository\GardenRepository;
use Doctrine\ORM\EntityManagerInterface;
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

        $owner = $this->createPersistedUser('bob@example.com');
        $ownerId = (int) $owner->getId();
        $garden = (new Garden())
            ->setOwner($owner)
            ->setName('専用箱庭')
            ->setDescription('自分の箱庭です');

        $gardenRepository = $this->createMock(GardenRepository::class);
        $gardenRepository->method('findByOwnerId')->with($ownerId)->willReturn([$garden]);

        static::getContainer()->set(GardenRepository::class, $gardenRepository);
        $client->loginUser($owner, 'main');

        $client->request('GET', sprintf('/my-garden/%d', $ownerId));

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'bob@example.comさん専用の箱庭管理画面');
        $this->assertSelectorTextContains('body', '専用箱庭');
    }

    public function testGardenDashboardRejectsAccessToAnotherUsersGarden(): void
    {
        $client = static::createClient();

        $currentUser = $this->createPersistedUser('charlie@example.com');
        $currentUserId = (int) $currentUser->getId();

        $client->loginUser($currentUser, 'main');
        $client->request('GET', sprintf('/my-garden/%d', $currentUserId + 1));

        $this->assertResponseStatusCodeSame(403);
    }

    private function createPersistedUser(string $email): User
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $user = (new User())
            ->setEmail($email)
            ->setPassword('test-password');

        $entityManager->persist($user);
        $entityManager->flush();

        return $user;
    }
}
