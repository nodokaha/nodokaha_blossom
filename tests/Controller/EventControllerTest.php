<?php

namespace App\Tests\Controller;

use App\Entity\EventComment;
use App\Entity\EventPost;
use App\Repository\EventPostRepository;
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
        $post->addComment((new EventComment())
            ->setAuthorName('guest')
            ->setContent('参加します'));

        $repo = $this->createStub(EventPostRepository::class);
        $repo->method('findLatest')->willReturn([$post]);
        static::getContainer()->set(EventPostRepository::class, $repo);

        $client->request('GET', '/basisvr/events');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'BasisVR イベントブログ');
        $this->assertSelectorTextContains('body', 'BasisVR Meetup');
        $this->assertSelectorExists('.post-list-item');
        $this->assertSelectorTextContains('.post-comment-count', '1 comments');
    }

    public function testEventNewSubmitsAndRedirects(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/basisvr/events/new');
        $form = $crawler->selectButton('投稿を公開')->form([
            'event_post[title]' => 'Summer Event',
            'event_post[authorName]' => 'staff',
            'event_post[content]' => 'Event details',
            'event_post[website]' => '',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/basisvr/events');

        $client->followRedirect();
        $this->assertSelectorTextContains('body', 'Summer Event');
    }

    public function testEventNewRejectsHoneypotSubmission(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/basisvr/events/new');
        $form = $crawler->selectButton('投稿を公開')->form([
            'event_post[title]' => 'Spam Event',
            'event_post[authorName]' => 'bot',
            'event_post[content]' => 'Automated content',
            'event_post[website]' => 'https://spam.example',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/basisvr/events');

        $client->followRedirect();
        $this->assertSelectorTextNotContains('body', 'Spam Event');
    }
}
