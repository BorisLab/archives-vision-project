<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251031123140 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE boite_physique (id INT AUTO_INCREMENT NOT NULL, code_boite VARCHAR(50) NOT NULL, libelle VARCHAR(255) NOT NULL, localisation VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, capacite_max INT DEFAULT NULL, statut TINYINT(1) NOT NULL, date_creation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', date_modif DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_E75FCDD9B4DF4A3 (code_boite), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fichier ADD boite_physique_id INT DEFAULT NULL, DROP boite_physique');
        $this->addSql('ALTER TABLE fichier ADD CONSTRAINT FK_9B76551F7B76485F FOREIGN KEY (boite_physique_id) REFERENCES boite_physique (id)');
        $this->addSql('CREATE INDEX IDX_9B76551F7B76485F ON fichier (boite_physique_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fichier DROP FOREIGN KEY FK_9B76551F7B76485F');
        $this->addSql('DROP TABLE boite_physique');
        $this->addSql('DROP INDEX IDX_9B76551F7B76485F ON fichier');
        $this->addSql('ALTER TABLE fichier ADD boite_physique VARCHAR(100) DEFAULT NULL, DROP boite_physique_id');
    }
}
