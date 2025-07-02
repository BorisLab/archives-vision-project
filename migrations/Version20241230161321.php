<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241230161321 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE demande_acces (id INT AUTO_INCREMENT NOT NULL, dossier_id INT DEFAULT NULL, fichier_id INT DEFAULT NULL, utilisateur_id INT NOT NULL, statut VARCHAR(255) DEFAULT \'pending\' NOT NULL, date_creation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_modif DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_262AB839611C0C56 (dossier_id), INDEX IDX_262AB839F915CFE (fichier_id), INDEX IDX_262AB839FB88E14F (utilisateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE departement (id INT AUTO_INCREMENT NOT NULL, libelle_dep VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dossier (id INT AUTO_INCREMENT NOT NULL, departement_id INT NOT NULL, utilisateur_id INT NOT NULL, dossier_parent_id INT DEFAULT NULL, libelle_dossier VARCHAR(255) NOT NULL, format VARCHAR(255) NOT NULL, tags VARCHAR(255) DEFAULT NULL, parent TINYINT(1) NOT NULL, statut TINYINT(1) NOT NULL, date_creation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_modif DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_3D48E037CCF9E01E (departement_id), INDEX IDX_3D48E037FB88E14F (utilisateur_id), INDEX IDX_3D48E037BC336E0D (dossier_parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fichier (id INT AUTO_INCREMENT NOT NULL, dossier_id INT NOT NULL, libelle_fichier VARCHAR(255) NOT NULL, chemin_acces VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, tags VARCHAR(255) DEFAULT NULL, format VARCHAR(255) NOT NULL, statut TINYINT(1) NOT NULL, date_creation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_modif DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_9B76551F611C0C56 (dossier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, sender_id INT NOT NULL, receiver_id INT NOT NULL, content VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT DEFAULT NULL, demande_acces_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, statut VARCHAR(255) DEFAULT \'unread\' NOT NULL, motif_rejet LONGTEXT DEFAULT NULL, niveau_acces VARCHAR(255) NOT NULL, date_creation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_modif DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_BF5476CAFB88E14F (utilisateur_id), INDEX IDX_BF5476CABA5CF66F (demande_acces_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, departement_id INT NOT NULL, nom VARCHAR(255) NOT NULL, prenoms VARCHAR(255) NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_1D1C63B3E7927C74 (email), INDEX IDX_1D1C63B3CCF9E01E (departement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE demande_acces ADD CONSTRAINT FK_262AB839611C0C56 FOREIGN KEY (dossier_id) REFERENCES dossier (id)');
        $this->addSql('ALTER TABLE demande_acces ADD CONSTRAINT FK_262AB839F915CFE FOREIGN KEY (fichier_id) REFERENCES fichier (id)');
        $this->addSql('ALTER TABLE demande_acces ADD CONSTRAINT FK_262AB839FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE dossier ADD CONSTRAINT FK_3D48E037CCF9E01E FOREIGN KEY (departement_id) REFERENCES departement (id)');
        $this->addSql('ALTER TABLE dossier ADD CONSTRAINT FK_3D48E037FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE dossier ADD CONSTRAINT FK_3D48E037BC336E0D FOREIGN KEY (dossier_parent_id) REFERENCES dossier (id)');
        $this->addSql('ALTER TABLE fichier ADD CONSTRAINT FK_9B76551F611C0C56 FOREIGN KEY (dossier_id) REFERENCES dossier (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CABA5CF66F FOREIGN KEY (demande_acces_id) REFERENCES demande_acces (id)');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B3CCF9E01E FOREIGN KEY (departement_id) REFERENCES departement (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demande_acces DROP FOREIGN KEY FK_262AB839611C0C56');
        $this->addSql('ALTER TABLE demande_acces DROP FOREIGN KEY FK_262AB839F915CFE');
        $this->addSql('ALTER TABLE demande_acces DROP FOREIGN KEY FK_262AB839FB88E14F');
        $this->addSql('ALTER TABLE dossier DROP FOREIGN KEY FK_3D48E037CCF9E01E');
        $this->addSql('ALTER TABLE dossier DROP FOREIGN KEY FK_3D48E037FB88E14F');
        $this->addSql('ALTER TABLE dossier DROP FOREIGN KEY FK_3D48E037BC336E0D');
        $this->addSql('ALTER TABLE fichier DROP FOREIGN KEY FK_9B76551F611C0C56');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAFB88E14F');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CABA5CF66F');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B3CCF9E01E');
        $this->addSql('DROP TABLE demande_acces');
        $this->addSql('DROP TABLE departement');
        $this->addSql('DROP TABLE dossier');
        $this->addSql('DROP TABLE fichier');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE utilisateur');
    }
}
