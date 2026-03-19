<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260313142934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pari (id INT AUTO_INCREMENT NOT NULL, equipe VARCHAR(255) DEFAULT NULL, cote DOUBLE PRECISION DEFAULT NULL, mise DOUBLE PRECISION DEFAULT NULL, gains DOUBLE PRECISION DEFAULT NULL, statut VARCHAR(50) DEFAULT NULL, created_at DATETIME DEFAULT NULL, user_id INT DEFAULT NULL, INDEX IDX_2A091C1FA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) DEFAULT NULL, montant DOUBLE PRECISION DEFAULT NULL, created_at DATETIME DEFAULT NULL, user_id INT DEFAULT NULL, INDEX IDX_723705D1A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE pari ADD CONSTRAINT FK_2A091C1FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pari DROP FOREIGN KEY FK_2A091C1FA76ED395');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1A76ED395');
        $this->addSql('DROP TABLE pari');
        $this->addSql('DROP TABLE transaction');
    }
}
