<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260821213506 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the vending_machine table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE vending_machine (id INT NOT NULL, maintenance TINYINT NOT NULL, customer_id VARCHAR(255) DEFAULT NULL, inserted_coins JSON NOT NULL, change_coins JSON NOT NULL, stock JSON NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE vending_machine');
    }
}
