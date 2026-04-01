<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251031125414 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mouvement (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, fichier_id INT DEFAULT NULL, dossier_id INT DEFAULT NULL, boite_destination_id INT DEFAULT NULL, type_mouvement VARCHAR(50) NOT NULL, date_mouvement DATETIME NOT NULL, observations LONGTEXT DEFAULT NULL, emprunteur_nom VARCHAR(255) DEFAULT NULL, date_retour_prevue DATE DEFAULT NULL, date_retour_effective DATE DEFAULT NULL, statut VARCHAR(20) NOT NULL, date_creation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_modif DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_5B51FC3EFB88E14F (utilisateur_id), INDEX IDX_5B51FC3EF915CFE (fichier_id), INDEX IDX_5B51FC3E611C0C56 (dossier_id), INDEX IDX_5B51FC3E3A64756 (boite_destination_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mouvement ADD CONSTRAINT FK_5B51FC3EFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE mouvement ADD CONSTRAINT FK_5B51FC3EF915CFE FOREIGN KEY (fichier_id) REFERENCES fichier (id)');
        $this->addSql('ALTER TABLE mouvement ADD CONSTRAINT FK_5B51FC3E611C0C56 FOREIGN KEY (dossier_id) REFERENCES dossier (id)');
        $this->addSql('ALTER TABLE mouvement ADD CONSTRAINT FK_5B51FC3E3A64756 FOREIGN KEY (boite_destination_id) REFERENCES boite_physique (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mouvement DROP FOREIGN KEY FK_5B51FC3EFB88E14F');
        $this->addSql('ALTER TABLE mouvement DROP FOREIGN KEY FK_5B51FC3EF915CFE');
        $this->addSql('ALTER TABLE mouvement DROP FOREIGN KEY FK_5B51FC3E611C0C56');
        $this->addSql('ALTER TABLE mouvement DROP FOREIGN KEY FK_5B51FC3E3A64756');
        $this->addSql('DROP TABLE mouvement');
    }
}
