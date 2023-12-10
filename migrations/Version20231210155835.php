<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231210155835 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE address ADD COLUMN first_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE address ADD COLUMN last_name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__address AS SELECT id, user_id_id, country_id_id, street, number, zip, city, telephone, email FROM address');
        $this->addSql('DROP TABLE address');
        $this->addSql('CREATE TABLE address (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id_id INTEGER DEFAULT NULL, country_id_id INTEGER DEFAULT NULL, street VARCHAR(255) NOT NULL, number VARCHAR(255) NOT NULL, zip VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, telephone VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_D4E6F819D86650F FOREIGN KEY (user_id_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D4E6F81D8A48BBD FOREIGN KEY (country_id_id) REFERENCES country (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO address (id, user_id_id, country_id_id, street, number, zip, city, telephone, email) SELECT id, user_id_id, country_id_id, street, number, zip, city, telephone, email FROM __temp__address');
        $this->addSql('DROP TABLE __temp__address');
        $this->addSql('CREATE INDEX IDX_D4E6F819D86650F ON address (user_id_id)');
        $this->addSql('CREATE INDEX IDX_D4E6F81D8A48BBD ON address (country_id_id)');
    }
}
