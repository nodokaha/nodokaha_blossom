<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260601000100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add asset_type field to asset_file table.';
    }

    public function up(Schema $schema): void
    {
        // Existing rows are migrated as prop by using a NOT NULL column with a default value.
        $this->addSql("ALTER TABLE asset_file ADD asset_type VARCHAR(20) NOT NULL DEFAULT 'prop'");
        $this->addSql("ALTER TABLE asset_file ADD CONSTRAINT CHK_asset_file_asset_type CHECK (asset_type IN ('prop', 'world', 'avatar'))");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE asset_file DROP CHECK CHK_asset_file_asset_type');
        $this->addSql('ALTER TABLE asset_file DROP COLUMN asset_type');
    }
}
