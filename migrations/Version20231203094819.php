<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231203094819 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shopping_cart ADD COLUMN session_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__shopping_cart AS SELECT id, user_id, state FROM shopping_cart');
        $this->addSql('DROP TABLE shopping_cart');
        $this->addSql('CREATE TABLE shopping_cart (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER DEFAULT NULL, state VARCHAR(255) NOT NULL, CONSTRAINT FK_72AAD4F69D86650F FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO shopping_cart (id, user_id, state) SELECT id, user_id, state FROM __temp__shopping_cart');
        $this->addSql('DROP TABLE __temp__shopping_cart');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_72AAD4F69D86650F ON shopping_cart (user_id)');
    }
}
