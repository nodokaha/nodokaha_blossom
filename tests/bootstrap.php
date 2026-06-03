<?php

use App\Kernel;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

if (($_SERVER['APP_ENV'] ?? 'test') === 'test') {
    $kernel = new Kernel('test', (bool) ($_SERVER['APP_DEBUG'] ?? false));
    $kernel->boot();

    $entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    $metadata = $entityManager->getMetadataFactory()->getAllMetadata();

    if ($metadata !== []) {
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->updateSchema($metadata);
    }

    $kernel->shutdown();
}
