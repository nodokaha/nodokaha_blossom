<?php

namespace App\Tests\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Test fixtures for development and testing
 */
class TestFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Sample data for testing
        // Once entities are defined, add fixture data here
        
        $manager->flush();
    }
}
