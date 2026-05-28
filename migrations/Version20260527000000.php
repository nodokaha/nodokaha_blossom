<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260527000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create EventPost and AssetFile tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE event_post (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(140) NOT NULL, content LONGTEXT NOT NULL, author_name VARCHAR(80) NOT NULL, published_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE asset_file (id INT AUTO_INCREMENT NOT NULL, storage_key VARCHAR(190) NOT NULL, original_name VARCHAR(255) NOT NULL, mime_type VARCHAR(120) NOT NULL, size INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_BAAE54E66A9B48A3 (storage_key), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
     }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE event_post');
        $this->addSql('DROP TABLE asset_file');
    }
}
