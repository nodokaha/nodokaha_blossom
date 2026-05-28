<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260528000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Tile, CommandQueue, WeeklyExecution, WorldState, Challenge tables.';
    }

    public function up(Schema $schema): void
    {
        // Tile table
        $this->addSql('CREATE TABLE tile (id INT AUTO_INCREMENT NOT NULL, garden_id INT NOT NULL, x INT NOT NULL, y INT NOT NULL, role VARCHAR(50) NOT NULL, stack_data JSON NOT NULL, stack_state JSON NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_8FE38F7D1F55203D (garden_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // CommandQueue table
        $this->addSql('CREATE TABLE command_queue (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, garden_id INT NOT NULL, tile_id INT, command JSON NOT NULL, inserted_date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', execution_week INT, status VARCHAR(50) NOT NULL, execution_result JSON, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_F22D81D5A76ED395 (user_id), INDEX IDX_F22D81D51F55203D (garden_id), INDEX IDX_F22D81D5D40A2BD (tile_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // WeeklyExecution table
        $this->addSql('CREATE TABLE weekly_execution (id INT AUTO_INCREMENT NOT NULL, week_number INT NOT NULL, execution_date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', status VARCHAR(50) NOT NULL, execution_log JSON NOT NULL, network_effects JSON NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // WorldState table
        $this->addSql('CREATE TABLE world_state (id INT NOT NULL, calendar_start DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', current_day INT NOT NULL, current_week INT NOT NULL, chapter VARCHAR(255) NOT NULL, objective LONGTEXT NOT NULL, biome_data JSON NOT NULL, global_stack JSON NOT NULL, chronicle_log JSON NOT NULL, network_broadcast JSON NOT NULL, version INT NOT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Challenge table
        $this->addSql('CREATE TABLE challenge (id INT AUTO_INCREMENT NOT NULL, period VARCHAR(50) NOT NULL, title VARCHAR(255) NOT NULL, objective LONGTEXT NOT NULL, active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Foreign Keys
        $this->addSql('ALTER TABLE tile ADD CONSTRAINT FK_8FE38F7D1F55203D FOREIGN KEY (garden_id) REFERENCES garden (id)');
        $this->addSql('ALTER TABLE command_queue ADD CONSTRAINT FK_F22D81D5A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE command_queue ADD CONSTRAINT FK_F22D81D51F55203D FOREIGN KEY (garden_id) REFERENCES garden (id)');
        $this->addSql('ALTER TABLE command_queue ADD CONSTRAINT FK_F22D81D5D40A2BD FOREIGN KEY (tile_id) REFERENCES tile (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE command_queue DROP FOREIGN KEY FK_F22D81D5D40A2BD');
        $this->addSql('ALTER TABLE tile DROP FOREIGN KEY FK_8FE38F7D1F55203D');
        $this->addSql('DROP TABLE command_queue');
        $this->addSql('DROP TABLE tile');
        $this->addSql('DROP TABLE weekly_execution');
        $this->addSql('DROP TABLE world_state');
        $this->addSql('DROP TABLE challenge');
    }
}
