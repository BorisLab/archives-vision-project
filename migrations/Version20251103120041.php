<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251103120041 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demande_acces ADD approbateur_id INT DEFAULT NULL, ADD date_traitement DATETIME DEFAULT NULL, ADD bordereau_pret VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE demande_acces ADD CONSTRAINT FK_262AB8399FBB267B FOREIGN KEY (approbateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE INDEX IDX_262AB8399FBB267B ON demande_acces (approbateur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE demande_acces DROP FOREIGN KEY FK_262AB8399FBB267B');
        $this->addSql('DROP INDEX IDX_262AB8399FBB267B ON demande_acces');
        $this->addSql('ALTER TABLE demande_acces DROP approbateur_id, DROP date_traitement, DROP bordereau_pret');
    }
}
