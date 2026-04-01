<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251027124903 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE demande_destruction (id INT AUTO_INCREMENT NOT NULL, demandeur_id INT NOT NULL, approbateur_id INT DEFAULT NULL, type_entite VARCHAR(50) NOT NULL, entite_id INT NOT NULL, libelle_entite LONGTEXT NOT NULL, statut VARCHAR(50) NOT NULL, justification LONGTEXT NOT NULL, motif_rejet LONGTEXT DEFAULT NULL, fichier_preuve VARCHAR(255) DEFAULT NULL, date_demande DATETIME NOT NULL, date_traitement DATETIME DEFAULT NULL, date_execution DATETIME DEFAULT NULL, INDEX IDX_E9E908D295A6EE59 (demandeur_id), INDEX IDX_E9E908D29FBB267B (approbateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE regle_retention (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, duree_conservation INT NOT NULL, base_legale LONGTEXT NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE demande_destruction ADD CONSTRAINT FK_E9E908D295A6EE59 FOREIGN KEY (demandeur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE demande_destruction ADD CONSTRAINT FK_E9E908D29FBB267B FOREIGN KEY (approbateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE dossier ADD regle_retention_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE dossier ADD CONSTRAINT FK_3D48E0376299B144 FOREIGN KEY (regle_retention_id) REFERENCES regle_retention (id)');
        $this->addSql('CREATE INDEX IDX_3D48E0376299B144 ON dossier (regle_retention_id)');
        $this->addSql('ALTER TABLE fichier ADD regle_retention_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE fichier ADD CONSTRAINT FK_9B76551F6299B144 FOREIGN KEY (regle_retention_id) REFERENCES regle_retention (id)');
        $this->addSql('CREATE INDEX IDX_9B76551F6299B144 ON fichier (regle_retention_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dossier DROP FOREIGN KEY FK_3D48E0376299B144');
        $this->addSql('ALTER TABLE fichier DROP FOREIGN KEY FK_9B76551F6299B144');
        $this->addSql('ALTER TABLE demande_destruction DROP FOREIGN KEY FK_E9E908D295A6EE59');
        $this->addSql('ALTER TABLE demande_destruction DROP FOREIGN KEY FK_E9E908D29FBB267B');
        $this->addSql('DROP TABLE demande_destruction');
        $this->addSql('DROP TABLE regle_retention');
        $this->addSql('DROP INDEX IDX_3D48E0376299B144 ON dossier');
        $this->addSql('ALTER TABLE dossier DROP regle_retention_id');
        $this->addSql('DROP INDEX IDX_9B76551F6299B144 ON fichier');
        $this->addSql('ALTER TABLE fichier DROP regle_retention_id');
    }
}
