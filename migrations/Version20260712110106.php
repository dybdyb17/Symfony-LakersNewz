<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260712110106 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE selection ADD match_nba_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE selection ADD CONSTRAINT FK_96A50CD7DB3B9EE1 FOREIGN KEY (match_nba_id) REFERENCES match_nba (id)');
        $this->addSql('CREATE INDEX IDX_96A50CD7DB3B9EE1 ON selection (match_nba_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE selection DROP FOREIGN KEY FK_96A50CD7DB3B9EE1');
        $this->addSql('DROP INDEX IDX_96A50CD7DB3B9EE1 ON selection');
        $this->addSql('ALTER TABLE selection DROP match_nba_id');
    }
}
