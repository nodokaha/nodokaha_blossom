<?php

namespace App\Tests\Controller;

use App\Entity\EventComment;
use App\Entity\EventPost;
use App\Repository\EventPostRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EventControllerTest extends WebTestCase
{
    public function testContentIndexDisplaysPosts(): void
    {
        $client = static::createClient();

        $post = (new EventPost())
            ->setTitle('水辺のランタンProp')
            ->setContentType(EventPost::CONTENT_TYPE_PROP)
            ->setAuthorName('admin')
            ->setDescription("Line1\nLine2")
            ->setRelatedAssets(['lantern.bee'])
            ->setTags(['fantasy', 'night']);
        $post->addComment((new EventComment())
            ->setAuthorName('guest')
            ->setContent('導入します'));

        $repo = $this->createStub(EventPostRepository::class);
        $repo->method('findLatest')->willReturn([$post]);
        static::getContainer()->set(EventPostRepository::class, $repo);

        $client->request('GET', '/basisvr/contents');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'BasisVR コンテンツ投稿');
        $this->assertSelectorTextContains('body', '水辺のランタンProp');
        $this->assertSelectorTextContains('body', 'Prop');
        $this->assertSelectorTextContains('body', '#fantasy');
        $this->assertSelectorExists('.post-list-item');
        $this->assertSelectorTextContains('.post-comment-count', '1 comments');
    }

    public function testContentNewSubmitsAndRedirects(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/basisvr/contents/new');
        $form = $crawler->selectButton('コンテンツを公開')->form([
            'event_post[title]' => 'Summer World',
            'event_post[contentType]' => EventPost::CONTENT_TYPE_WORLD,
            'event_post[authorName]' => 'staff',
            'event_post[description]' => 'World details',
            'event_post[relatedAssets]' => 'summer-world.bee, thumbnail.png',
            'event_post[tags]' => 'world, summer',
            'event_post[website]' => '',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/basisvr/contents');

        $client->followRedirect();
        $this->assertSelectorTextContains('body', 'Summer World');
        $this->assertSelectorTextContains('body', '#summer');
    }

    public function testContentNewRejectsHoneypotSubmission(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/basisvr/contents/new');
        $form = $crawler->selectButton('コンテンツを公開')->form([
            'event_post[title]' => 'Spam Avatar',
            'event_post[contentType]' => EventPost::CONTENT_TYPE_AVATAR,
            'event_post[authorName]' => 'bot',
            'event_post[description]' => 'Automated content',
            'event_post[relatedAssets]' => 'spam.bee',
            'event_post[tags]' => 'avatar',
            'event_post[website]' => 'https://spam.example',
        ]);

        $client->submit($form);

        $this->assertResponseRedirects('/basisvr/contents');

        $client->followRedirect();
        $this->assertSelectorTextNotContains('body', 'Spam Avatar');
    }
}
