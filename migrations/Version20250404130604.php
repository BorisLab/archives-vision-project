<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250404130604 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE departement ADD departement_parent_id INT DEFAULT NULL, ADD parent TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE departement ADD CONSTRAINT FK_C1765B63F564079D FOREIGN KEY (departement_parent_id) REFERENCES departement (id)');
        $this->addSql('CREATE INDEX IDX_C1765B63F564079D ON departement (departement_parent_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE departement DROP FOREIGN KEY FK_C1765B63F564079D');
        $this->addSql('DROP INDEX IDX_C1765B63F564079D ON departement');
        $this->addSql('ALTER TABLE departement DROP departement_parent_id, DROP parent');
    }
}
