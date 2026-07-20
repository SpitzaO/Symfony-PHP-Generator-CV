<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260719094217 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make competency a group of items: add competency_item table, drop competency.description.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE competency_item (
              id INT AUTO_INCREMENT NOT NULL,
              name VARCHAR(255) NOT NULL,
              description LONGTEXT DEFAULT NULL,
              competency_id INT NOT NULL,
              INDEX IDX_2C97EDE5FB9F58C (competency_id),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              competency_item
            ADD
              CONSTRAINT FK_2C97EDE5FB9F58C FOREIGN KEY (competency_id) REFERENCES competency (id)
        SQL);
        $this->addSql('ALTER TABLE competency DROP description');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE competency_item DROP FOREIGN KEY FK_2C97EDE5FB9F58C');
        $this->addSql('DROP TABLE competency_item');
        $this->addSql('ALTER TABLE competency ADD description LONGTEXT DEFAULT NULL');
    }
}
