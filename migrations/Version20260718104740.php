<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260718104740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE experience DROP FOREIGN KEY `FK_590C1033256915B`');
        $this->addSql('DROP INDEX IDX_590C1033256915B ON experience');
        $this->addSql('ALTER TABLE experience CHANGE relation_id profile_id INT NOT NULL');
        $this->addSql('ALTER TABLE experience ADD CONSTRAINT FK_590C103CCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id)');
        $this->addSql('CREATE INDEX IDX_590C103CCFA12B8 ON experience (profile_id)');
        $this->addSql('ALTER TABLE profile CHANGE about about LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE experience DROP FOREIGN KEY FK_590C103CCFA12B8');
        $this->addSql('DROP INDEX IDX_590C103CCFA12B8 ON experience');
        $this->addSql('ALTER TABLE experience CHANGE profile_id relation_id INT NOT NULL');
        $this->addSql('ALTER TABLE experience ADD CONSTRAINT `FK_590C1033256915B` FOREIGN KEY (relation_id) REFERENCES profile (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_590C1033256915B ON experience (relation_id)');
        $this->addSql('ALTER TABLE profile CHANGE about about VARCHAR(255) DEFAULT NULL');
    }
}
