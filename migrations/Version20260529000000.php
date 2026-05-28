<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260529000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add encryption_key field to asset_file table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE asset_file ADD encryption_key VARCHAR(64) NOT NULL DEFAULT \'\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE asset_file DROP COLUMN encryption_key');
    }
}
