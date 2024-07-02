<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240624192010 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dossier (id INT AUTO_INCREMENT NOT NULL, departement_id INT NOT NULL, utilisateur_id INT NOT NULL, dossier_parent INT DEFAULT NULL, libelle_dossier VARCHAR(255) NOT NULL, format JSON NOT NULL COMMENT \'(DC2Type:json)\', tags VARCHAR(255) DEFAULT NULL, parent TINYINT(1) NOT NULL, INDEX IDX_3D48E037CCF9E01E (departement_id), INDEX IDX_3D48E037FB88E14F (utilisateur_id), INDEX IDX_3D48E037BC336E0D (dossier_parent), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fichier (id INT AUTO_INCREMENT NOT NULL, dossier_id INT NOT NULL, libelle_fichier VARCHAR(255) NOT NULL, chemin_acces VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, tags VARCHAR(255) DEFAULT NULL, date_creation DATETIME NOT NULL, date_maj DATETIME NOT NULL, INDEX IDX_9B76551F611C0C56 (dossier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dossier ADD CONSTRAINT FK_3D48E037CCF9E01E FOREIGN KEY (departement_id) REFERENCES departement (id)');
        $this->addSql('ALTER TABLE dossier ADD CONSTRAINT FK_3D48E037FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE dossier ADD CONSTRAINT FK_3D48E037BC336E0D FOREIGN KEY (dossier_parent) REFERENCES dossier (id)');
        $this->addSql('ALTER TABLE fichier ADD CONSTRAINT FK_9B76551F611C0C56 FOREIGN KEY (dossier_id) REFERENCES dossier (id)');
        $this->addSql('ALTER TABLE utilisateur CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B3CCF9E01E FOREIGN KEY (departement_id) REFERENCES departement (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dossier DROP FOREIGN KEY FK_3D48E037CCF9E01E');
        $this->addSql('ALTER TABLE dossier DROP FOREIGN KEY FK_3D48E037FB88E14F');
        $this->addSql('ALTER TABLE dossier DROP FOREIGN KEY FK_3D48E037BC336E0D');
        $this->addSql('ALTER TABLE fichier DROP FOREIGN KEY FK_9B76551F611C0C56');
        $this->addSql('DROP TABLE dossier');
        $this->addSql('DROP TABLE fichier');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B3CCF9E01E');
        $this->addSql('ALTER TABLE utilisateur CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
    }
}
