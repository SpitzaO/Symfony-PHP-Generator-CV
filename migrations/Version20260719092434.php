<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260719092434 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add location to experience & education, add competency table, seed Gallup & IP121.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE competency (
              id INT AUTO_INCREMENT NOT NULL,
              name VARCHAR(255) NOT NULL,
              description LONGTEXT DEFAULT NULL,
              profile_id INT NOT NULL,
              INDEX IDX_80D53430CCFA12B8 (profile_id),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              competency
            ADD
              CONSTRAINT FK_80D53430CCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id)
        SQL);
        $this->addSql('ALTER TABLE education ADD location VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE experience ADD location VARCHAR(255) DEFAULT NULL');

        // Seed the two requested competency assessments for any existing profile.
        // Descriptions are left empty on purpose — fill them in from the profile edit page.
        $this->addSql("INSERT INTO competency (name, description, profile_id) SELECT 'Gallup', NULL, id FROM profile");
        $this->addSql("INSERT INTO competency (name, description, profile_id) SELECT 'IP121', NULL, id FROM profile");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE competency DROP FOREIGN KEY FK_80D53430CCFA12B8');
        $this->addSql('DROP TABLE competency');
        $this->addSql('ALTER TABLE education DROP location');
        $this->addSql('ALTER TABLE experience DROP location');
    }
}
