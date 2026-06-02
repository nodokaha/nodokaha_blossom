<?php

namespace App\Tests\Entity;

use App\Entity\Event;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    public function testEventGettersAndSetters(): void
    {
        $event = new Event();
        $startAt = new \DateTimeImmutable('2026-06-10 10:00:00');
        $endAt = new \DateTimeImmutable('2026-06-10 12:00:00');

        $event->setTitle('テストイベント');
        $event->setDescription('説明');
        $event->setLocation('会場');
        $event->setStartAt($startAt);
        $event->setEndAt($endAt);
        $event->setAllDay(true);
        $event->setUpdatedAt(new \DateTimeImmutable('2026-06-01 09:00:00'));

        $this->assertSame('テストイベント', $event->getTitle());
        $this->assertSame('説明', $event->getDescription());
        $this->assertSame('会場', $event->getLocation());
        $this->assertSame($startAt, $event->getStartAt());
        $this->assertSame($endAt, $event->getEndAt());
        $this->assertTrue($event->isAllDay());
        $this->assertInstanceOf(\DateTimeImmutable::class, $event->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $event->getUpdatedAt());
    }
}
