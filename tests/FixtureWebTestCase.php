<?php

namespace App\Tests;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

/**
 * Base test case that handles fixtures loading
 */
abstract class FixtureWebTestCase extends BaseWebTestCase
{
    private ?EntityManagerInterface $entityManager = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        if ($this->entityManager) {
            $this->entityManager->close();
        }
    }

    protected function loadFixtures(array $fixtureClasses = []): void
    {
        // If no specific fixtures provided, load all
        if (empty($fixtureClasses)) {
            $loader = self::getContainer()->get('doctrine.fixtures.loader');
            $fixtures = $loader->loadFromDirectory(__DIR__.'/../src/DataFixtures');
        } else {
            $fixtures = array_map(fn($class) => new $class(), $fixtureClasses);
        }

        $purger = new ORMPurger($this->entityManager);
        $purger->purge();

        $executor = new \Doctrine\Common\DataFixtures\Executor\ORMExecutor($this->entityManager, $purger);
        $executor->execute($fixtures);
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager ?? throw new \RuntimeException('EntityManager not available');
    }
}
