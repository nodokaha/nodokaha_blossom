<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260510000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create garden table and relation to user.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE garden (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, name VARCHAR(120) NOT NULL, description LONGTEXT NOT NULL, INDEX IDX_4A2E9A2D7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE garden ADD CONSTRAINT FK_4A2E9A2D7E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE garden DROP FOREIGN KEY FK_4A2E9A2D7E3C61F9');
        $this->addSql('DROP TABLE garden');
    }
}
