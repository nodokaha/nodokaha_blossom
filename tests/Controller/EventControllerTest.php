<?php

namespace App\Tests\Controller;

use App\Entity\EventPost;
use App\Repository\EventPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EventControllerTest extends WebTestCase
{
    public function testEventIndexDisplaysPosts(): void
    {
        $client = static::createClient();

        $post = (new EventPost())
            ->setTitle('BasisVR Meetup')
            ->setAuthorName('admin')
            ->setContent("Line1\nLine2");

        $repo = $this->createMock(EventPostRepository::class);
        $repo->method('findLatest')->willReturn([$post]);
        static::getContainer()->set(EventPostRepository::class, $repo);

        $client->request('GET', '/basisvr/events');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'BasisVR Event Board');
        $this->assertSelectorTextContains('body', 'BasisVR Meetup');
    }

    public function testEventNewSubmitsAndRedirects(): void
    {
        $client = static::createClient();

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('persist');
        $entityManager->expects($this->once())->method('flush');
        static::getContainer()->set(EntityManagerInterface::class, $entityManager);

        $crawler = $client->request('GET', '/basisvr/events/new');
        $form = $crawler->selectButton('保存')->form([
            'event_post[title]' => 'Summer Event',
            'event_post[authorName]' => 'staff',
            'event_post[content]' => 'Event details',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/basisvr/events');
    }
}
