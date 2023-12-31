<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231201094356 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE address (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id_id INTEGER DEFAULT NULL, country_id_id INTEGER DEFAULT NULL, street VARCHAR(255) NOT NULL, number VARCHAR(255) NOT NULL, zip VARCHAR(255) NOT NULL, CONSTRAINT FK_D4E6F819D86650F FOREIGN KEY (user_id_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_D4E6F81D8A48BBD FOREIGN KEY (country_id_id) REFERENCES country (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_D4E6F819D86650F ON address (user_id_id)');
        $this->addSql('CREATE INDEX IDX_D4E6F81D8A48BBD ON address (country_id_id)');
        $this->addSql('CREATE TABLE country (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, eu BOOLEAN NOT NULL, iso VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE "order" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id_id INTEGER DEFAULT NULL, date DATE NOT NULL, status VARCHAR(255) NOT NULL, CONSTRAINT FK_F52993989D86650F FOREIGN KEY (user_id_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_F52993989D86650F ON "order" (user_id_id)');
        $this->addSql('CREATE TABLE order_product (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, purchaseorder_id INTEGER DEFAULT NULL, product_id INTEGER DEFAULT NULL, quantity INTEGER NOT NULL, CONSTRAINT FK_2530ADE6E20D463C FOREIGN KEY (purchaseorder_id) REFERENCES "order" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2530ADE64584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_2530ADE6E20D463C ON order_product (purchaseorder_id)');
        $this->addSql('CREATE INDEX IDX_2530ADE64584665A ON order_product (product_id)');
        $this->addSql('CREATE TABLE product (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, stock INTEGER NOT NULL, price NUMERIC(10, 2) NOT NULL, description VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE TABLE shopping_cart (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id_id INTEGER DEFAULT NULL, state VARCHAR(255) NOT NULL, CONSTRAINT FK_72AAD4F69D86650F FOREIGN KEY (user_id_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_72AAD4F69D86650F ON shopping_cart (user_id_id)');
        $this->addSql('CREATE TABLE shopping_cart_product (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, shoppingcart_id INTEGER DEFAULT NULL, product_id INTEGER DEFAULT NULL, quantity INTEGER NOT NULL, CONSTRAINT FK_FA1F5E6C685930AE FOREIGN KEY (shoppingcart_id) REFERENCES shopping_cart (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_FA1F5E6C4584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_FA1F5E6C685930AE ON shopping_cart_product (shoppingcart_id)');
        $this->addSql('CREATE INDEX IDX_FA1F5E6C4584665A ON shopping_cart_product (product_id)');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON user (email)');
        $this->addSql('CREATE TABLE messenger_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, body CLOB NOT NULL, headers CLOB NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , available_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , delivered_at DATETIME DEFAULT NULL --(DC2Type:datetime_immutable)
        )');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE address');
        $this->addSql('DROP TABLE country');
        $this->addSql('DROP TABLE "order"');
        $this->addSql('DROP TABLE order_product');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE shopping_cart');
        $this->addSql('DROP TABLE shopping_cart_product');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
