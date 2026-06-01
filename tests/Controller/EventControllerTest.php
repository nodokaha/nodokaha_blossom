<?php

namespace App\Tests\Controller;

use App\Entity\EventComment;
use App\Entity\EventPost;
use App\Repository\EventPostRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EventControllerTest extends WebTestCase
{
    public function testReviewIndexDisplaysPosts(): void
    {
        $client = static::createClient();

        $post = (new EventPost())
            ->setTitle('検索体験レビュー')
            ->setAuthorName('admin')
            ->setContent("Line1\nLine2");
        $post->addComment((new EventComment())
            ->setAuthorName('guest')
            ->setContent('補足します'));

        $repo = $this->createStub(EventPostRepository::class);
        $repo->method('findLatest')->willReturn([$post]);
        static::getContainer()->set(EventPostRepository::class, $repo);

        $client->request('GET', '/reviews');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'プロジェクト見直しブログ');
        $this->assertSelectorTextContains('body', '検索体験レビュー');
        $this->assertSelectorExists('.post-list-item');
        $this->assertSelectorTextContains('.post-comment-count', '1 comments');
    }

    public function testReviewNewSubmitsAndRedirects(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/reviews/new');
        $form = $crawler->selectButton('レビューを公開')->form([
            'event_post[title]' => 'Summer Review',
            'event_post[authorName]' => 'staff',
            'event_post[content]' => 'Review details',
            'event_post[website]' => '',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/reviews');

        $client->followRedirect();
        $this->assertSelectorTextContains('body', 'Summer Review');
    }

    public function testReviewNewRejectsHoneypotSubmission(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/reviews/new');
        $form = $crawler->selectButton('レビューを公開')->form([
            'event_post[title]' => 'Spam Review',
            'event_post[authorName]' => 'bot',
            'event_post[content]' => 'Automated review',
            'event_post[website]' => 'https://spam.example',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/reviews');

        $client->followRedirect();
        $this->assertSelectorTextNotContains('body', 'Spam Review');
    }
}
