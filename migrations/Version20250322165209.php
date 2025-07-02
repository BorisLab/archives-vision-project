<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250322165209 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX archiviste_id ON demande_acces');
        $this->addSql('ALTER TABLE fichier CHANGE chemin_acces chemin_acces VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE message ADD statut VARCHAR(255) DEFAULT \'unread\' NOT NULL, ADD date_creation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD date_modif DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX archiviste_id ON demande_acces (archiviste_id)');
        $this->addSql('ALTER TABLE fichier CHANGE chemin_acces chemin_acces VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE message DROP statut, DROP date_creation, DROP date_modif');
    }
}
