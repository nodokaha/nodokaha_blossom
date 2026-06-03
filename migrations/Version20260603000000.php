<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260603000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create assets and events tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE assets (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            type VARCHAR(50) NOT NULL,
            filename VARCHAR(255) NOT NULL,
            file_size INT NOT NULL,
            mime_type VARCHAR(50) NOT NULL,
            uploaded_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            uploaded_by VARCHAR(255) DEFAULT NULL,
            thumbnail_path VARCHAR(255) DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");

        $this->addSql("CREATE TABLE events (
            id INT AUTO_INCREMENT NOT NULL,
            title VARCHAR(255) NOT NULL,
            description LONGTEXT DEFAULT NULL,
            start_at DATETIME NOT NULL,
            end_at DATETIME DEFAULT NULL,
            all_day TINYINT(1) NOT NULL,
            location VARCHAR(255) DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE assets');
        $this->addSql('DROP TABLE events');
    }
}
