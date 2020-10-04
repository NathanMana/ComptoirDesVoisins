<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200531172650 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE offer_city (offer_id INT NOT NULL, city_id INT NOT NULL, INDEX IDX_D3A32C2353C674EE (offer_id), INDEX IDX_D3A32C238BAC62AF (city_id), PRIMARY KEY(offer_id, city_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE offer_city ADD CONSTRAINT FK_D3A32C2353C674EE FOREIGN KEY (offer_id) REFERENCES offer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE offer_city ADD CONSTRAINT FK_D3A32C238BAC62AF FOREIGN KEY (city_id) REFERENCES city (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE advert ADD city_id INT NOT NULL, DROP city, DROP code_city');
        $this->addSql('ALTER TABLE advert ADD CONSTRAINT FK_54F1F40B8BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('CREATE INDEX IDX_54F1F40B8BAC62AF ON advert (city_id)');
        $this->addSql('ALTER TABLE offer DROP cities_delivery');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE offer_city');
        $this->addSql('ALTER TABLE advert DROP FOREIGN KEY FK_54F1F40B8BAC62AF');
        $this->addSql('DROP INDEX IDX_54F1F40B8BAC62AF ON advert');
        $this->addSql('ALTER TABLE advert ADD city VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD code_city VARCHAR(5) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, DROP city_id');
        $this->addSql('ALTER TABLE offer ADD cities_delivery VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
