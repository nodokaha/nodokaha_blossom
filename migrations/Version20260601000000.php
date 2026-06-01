<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260601000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update event posts for BasisVR content submissions.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event_post CHANGE content description LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE event_post ADD content_type VARCHAR(20) NOT NULL DEFAULT \'prop\'');
        $this->addSql('ALTER TABLE event_post ADD related_assets JSON DEFAULT NULL, ADD tags JSON DEFAULT NULL');
        $this->addSql('UPDATE event_post SET related_assets = JSON_ARRAY() WHERE related_assets IS NULL');
        $this->addSql('UPDATE event_post SET tags = JSON_ARRAY() WHERE tags IS NULL');
        $this->addSql('ALTER TABLE event_post MODIFY related_assets JSON NOT NULL, MODIFY tags JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event_post CHANGE description content LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE event_post DROP COLUMN content_type, DROP COLUMN related_assets, DROP COLUMN tags');
    }
}
