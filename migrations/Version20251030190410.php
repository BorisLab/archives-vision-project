<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251030190410 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dossier ADD date_debut DATE DEFAULT NULL, ADD date_fin DATE DEFAULT NULL, ADD typologie_documentaire VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE fichier ADD date_debut DATE DEFAULT NULL, ADD date_fin DATE DEFAULT NULL, ADD typologie_documentaire VARCHAR(255) DEFAULT NULL, ADD boite_physique VARCHAR(100) DEFAULT NULL, ADD emplacement VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dossier DROP date_debut, DROP date_fin, DROP typologie_documentaire');
        $this->addSql('ALTER TABLE fichier DROP date_debut, DROP date_fin, DROP typologie_documentaire, DROP boite_physique, DROP emplacement');
    }
}
