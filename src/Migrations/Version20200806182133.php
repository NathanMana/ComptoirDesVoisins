<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200806182133 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE help (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, deliverer_id INT DEFAULT NULL, city_id INT NOT NULL, title VARCHAR(255) NOT NULL, message LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, date_help DATETIME NOT NULL, is_cancel TINYINT(1) NOT NULL, is_delivered TINYINT(1) NOT NULL, INDEX IDX_8875CACA76ED395 (user_id), INDEX IDX_8875CACB6A6A3F4 (deliverer_id), INDEX IDX_8875CAC8BAC62AF (city_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE help ADD CONSTRAINT FK_8875CACA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE help ADD CONSTRAINT FK_8875CACB6A6A3F4 FOREIGN KEY (deliverer_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE help ADD CONSTRAINT FK_8875CAC8BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE help');
    }
}
