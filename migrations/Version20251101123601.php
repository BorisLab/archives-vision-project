<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251101123601 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX idx_audit_entity_id ON audit_log (entity_id)');
        $this->addSql('CREATE INDEX idx_audit_action_date ON audit_log (action, created_at)');
        $this->addSql('ALTER TABLE demande_acces DROP FOREIGN KEY FK_262AB839F915CFE');
        $this->addSql('ALTER TABLE demande_acces DROP FOREIGN KEY FK_262AB839FB88E14F');
        $this->addSql('ALTER TABLE demande_acces DROP FOREIGN KEY FK_262AB839611C0C56');
        $this->addSql('CREATE INDEX idx_demande_acces_statut ON demande_acces (statut)');
        $this->addSql('CREATE INDEX idx_demande_acces_date ON demande_acces (date_creation)');
        $this->addSql('DROP INDEX idx_262ab839fb88e14f ON demande_acces');
        $this->addSql('CREATE INDEX idx_demande_acces_utilisateur ON demande_acces (utilisateur_id)');
        $this->addSql('DROP INDEX idx_262ab839611c0c56 ON demande_acces');
        $this->addSql('CREATE INDEX idx_demande_acces_dossier ON demande_acces (dossier_id)');
        $this->addSql('DROP INDEX idx_262ab839f915cfe ON demande_acces');
        $this->addSql('CREATE INDEX idx_demande_acces_fichier ON demande_acces (fichier_id)');
        $this->addSql('ALTER TABLE demande_acces ADD CONSTRAINT FK_262AB839F915CFE FOREIGN KEY (fichier_id) REFERENCES fichier (id)');
        $this->addSql('ALTER TABLE demande_acces ADD CONSTRAINT FK_262AB839FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE demande_acces ADD CONSTRAINT FK_262AB839611C0C56 FOREIGN KEY (dossier_id) REFERENCES dossier (id)');
        $this->addSql('ALTER TABLE dossier DROP FOREIGN KEY FK_3D48E037CCF9E01E');
        $this->addSql('ALTER TABLE dossier DROP FOREIGN KEY FK_3D48E037FB88E14F');
        $this->addSql('CREATE INDEX idx_dossier_libelle ON dossier (libelle_dossier)');
        $this->addSql('CREATE INDEX idx_dossier_tags ON dossier (tags)');
        $this->addSql('CREATE INDEX idx_dossier_date_creation ON dossier (date_creation)');
        $this->addSql('CREATE INDEX idx_dossier_format ON dossier (format)');
        $this->addSql('CREATE INDEX idx_dossier_statut ON dossier (statut)');
        $this->addSql('CREATE INDEX idx_dossier_date_debut ON dossier (date_debut)');
        $this->addSql('CREATE INDEX idx_dossier_date_fin ON dossier (date_fin)');
        $this->addSql('CREATE INDEX idx_dossier_typologie ON dossier (typologie_documentaire)');
        $this->addSql('DROP INDEX idx_3d48e037ccf9e01e ON dossier');
        $this->addSql('CREATE INDEX idx_dossier_departement ON dossier (departement_id)');
        $this->addSql('DROP INDEX idx_3d48e037fb88e14f ON dossier');
        $this->addSql('CREATE INDEX idx_dossier_utilisateur ON dossier (utilisateur_id)');
        $this->addSql('ALTER TABLE dossier ADD CONSTRAINT FK_3D48E037CCF9E01E FOREIGN KEY (departement_id) REFERENCES departement (id)');
        $this->addSql('ALTER TABLE dossier ADD CONSTRAINT FK_3D48E037FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE fichier DROP FOREIGN KEY FK_9B76551F611C0C56');
        $this->addSql('ALTER TABLE fichier DROP FOREIGN KEY FK_9B76551F7B76485F');
        $this->addSql('CREATE INDEX idx_fichier_libelle ON fichier (libelle_fichier)');
        $this->addSql('CREATE INDEX idx_fichier_type ON fichier (type)');
        $this->addSql('CREATE INDEX idx_fichier_format ON fichier (format)');
        $this->addSql('CREATE INDEX idx_fichier_date_creation ON fichier (date_creation)');
        $this->addSql('CREATE INDEX idx_fichier_statut ON fichier (statut)');
        $this->addSql('CREATE INDEX idx_fichier_tags ON fichier (tags)');
        $this->addSql('CREATE INDEX idx_fichier_date_debut ON fichier (date_debut)');
        $this->addSql('CREATE INDEX idx_fichier_date_fin ON fichier (date_fin)');
        $this->addSql('CREATE INDEX idx_fichier_typologie ON fichier (typologie_documentaire)');
        $this->addSql('DROP INDEX idx_9b76551f611c0c56 ON fichier');
        $this->addSql('CREATE INDEX idx_fichier_dossier ON fichier (dossier_id)');
        $this->addSql('DROP INDEX idx_9b76551f7b76485f ON fichier');
        $this->addSql('CREATE INDEX idx_fichier_boite ON fichier (boite_physique_id)');
        $this->addSql('ALTER TABLE fichier ADD CONSTRAINT FK_9B76551F611C0C56 FOREIGN KEY (dossier_id) REFERENCES dossier (id)');
        $this->addSql('ALTER TABLE fichier ADD CONSTRAINT FK_9B76551F7B76485F FOREIGN KEY (boite_physique_id) REFERENCES boite_physique (id)');
        $this->addSql('ALTER TABLE mouvement DROP FOREIGN KEY FK_5B51FC3EFB88E14F');
        $this->addSql('ALTER TABLE mouvement DROP FOREIGN KEY FK_5B51FC3E611C0C56');
        $this->addSql('ALTER TABLE mouvement DROP FOREIGN KEY FK_5B51FC3EF915CFE');
        $this->addSql('CREATE INDEX idx_mouvement_type ON mouvement (type_mouvement)');
        $this->addSql('CREATE INDEX idx_mouvement_date ON mouvement (date_mouvement)');
        $this->addSql('CREATE INDEX idx_mouvement_date_creation ON mouvement (date_creation)');
        $this->addSql('DROP INDEX idx_5b51fc3efb88e14f ON mouvement');
        $this->addSql('CREATE INDEX idx_mouvement_utilisateur ON mouvement (utilisateur_id)');
        $this->addSql('DROP INDEX idx_5b51fc3ef915cfe ON mouvement');
        $this->addSql('CREATE INDEX idx_mouvement_fichier ON mouvement (fichier_id)');
        $this->addSql('DROP INDEX idx_5b51fc3e611c0c56 ON mouvement');
        $this->addSql('CREATE INDEX idx_mouvement_dossier ON mouvement (dossier_id)');
        $this->addSql('ALTER TABLE mouvement ADD CONSTRAINT FK_5B51FC3EFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE mouvement ADD CONSTRAINT FK_5B51FC3E611C0C56 FOREIGN KEY (dossier_id) REFERENCES dossier (id)');
        $this->addSql('ALTER TABLE mouvement ADD CONSTRAINT FK_5B51FC3EF915CFE FOREIGN KEY (fichier_id) REFERENCES fichier (id)');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B3CCF9E01E');
        $this->addSql('CREATE INDEX idx_utilisateur_email ON utilisateur (email)');
        $this->addSql('CREATE INDEX idx_utilisateur_statut ON utilisateur (statut)');
        $this->addSql('CREATE INDEX idx_utilisateur_nom ON utilisateur (nom)');
        $this->addSql('DROP INDEX idx_1d1c63b3ccf9e01e ON utilisateur');
        $this->addSql('CREATE INDEX idx_utilisateur_departement ON utilisateur (departement_id)');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B3CCF9E01E FOREIGN KEY (departement_id) REFERENCES departement (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_audit_entity_id ON audit_log');
        $this->addSql('DROP INDEX idx_audit_action_date ON audit_log');
        $this->addSql('DROP INDEX idx_demande_acces_statut ON demande_acces');
        $this->addSql('DROP INDEX idx_demande_acces_date ON demande_acces');
        $this->addSql('ALTER TABLE demande_acces DROP FOREIGN KEY FK_262AB839611C0C56');
        $this->addSql('ALTER TABLE demande_acces DROP FOREIGN KEY FK_262AB839F915CFE');
        $this->addSql('ALTER TABLE demande_acces DROP FOREIGN KEY FK_262AB839FB88E14F');
        $this->addSql('DROP INDEX idx_demande_acces_dossier ON demande_acces');
        $this->addSql('CREATE INDEX IDX_262AB839611C0C56 ON demande_acces (dossier_id)');
        $this->addSql('DROP INDEX idx_demande_acces_fichier ON demande_acces');
        $this->addSql('CREATE INDEX IDX_262AB839F915CFE ON demande_acces (fichier_id)');
        $this->addSql('DROP INDEX idx_demande_acces_utilisateur ON demande_acces');
        $this->addSql('CREATE INDEX IDX_262AB839FB88E14F ON demande_acces (utilisateur_id)');
        $this->addSql('ALTER TABLE demande_acces ADD CONSTRAINT FK_262AB839611C0C56 FOREIGN KEY (dossier_id) REFERENCES dossier (id)');
        $this->addSql('ALTER TABLE demande_acces ADD CONSTRAINT FK_262AB839F915CFE FOREIGN KEY (fichier_id) REFERENCES fichier (id)');
        $this->addSql('ALTER TABLE demande_acces ADD CONSTRAINT FK_262AB839FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('DROP INDEX idx_dossier_libelle ON dossier');
        $this->addSql('DROP INDEX idx_dossier_tags ON dossier');
        $this->addSql('DROP INDEX idx_dossier_date_creation ON dossier');
        $this->addSql('DROP INDEX idx_dossier_format ON dossier');
        $this->addSql('DROP INDEX idx_dossier_statut ON dossier');
        $this->addSql('DROP INDEX idx_dossier_date_debut ON dossier');
        $this->addSql('DROP INDEX idx_dossier_date_fin ON dossier');
        $this->addSql('DROP INDEX idx_dossier_typologie ON dossier');
        $this->addSql('ALTER TABLE dossier DROP FOREIGN KEY FK_3D48E037CCF9E01E');
        $this->addSql('ALTER TABLE dossier DROP FOREIGN KEY FK_3D48E037FB88E14F');
        $this->addSql('DROP INDEX idx_dossier_departement ON dossier');
        $this->addSql('CREATE INDEX IDX_3D48E037CCF9E01E ON dossier (departement_id)');
        $this->addSql('DROP INDEX idx_dossier_utilisateur ON dossier');
        $this->addSql('CREATE INDEX IDX_3D48E037FB88E14F ON dossier (utilisateur_id)');
        $this->addSql('ALTER TABLE dossier ADD CONSTRAINT FK_3D48E037CCF9E01E FOREIGN KEY (departement_id) REFERENCES departement (id)');
        $this->addSql('ALTER TABLE dossier ADD CONSTRAINT FK_3D48E037FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('DROP INDEX idx_fichier_libelle ON fichier');
        $this->addSql('DROP INDEX idx_fichier_type ON fichier');
        $this->addSql('DROP INDEX idx_fichier_format ON fichier');
        $this->addSql('DROP INDEX idx_fichier_date_creation ON fichier');
        $this->addSql('DROP INDEX idx_fichier_statut ON fichier');
        $this->addSql('DROP INDEX idx_fichier_tags ON fichier');
        $this->addSql('DROP INDEX idx_fichier_date_debut ON fichier');
        $this->addSql('DROP INDEX idx_fichier_date_fin ON fichier');
        $this->addSql('DROP INDEX idx_fichier_typologie ON fichier');
        $this->addSql('ALTER TABLE fichier DROP FOREIGN KEY FK_9B76551F611C0C56');
        $this->addSql('ALTER TABLE fichier DROP FOREIGN KEY FK_9B76551F7B76485F');
        $this->addSql('DROP INDEX idx_fichier_boite ON fichier');
        $this->addSql('CREATE INDEX IDX_9B76551F7B76485F ON fichier (boite_physique_id)');
        $this->addSql('DROP INDEX idx_fichier_dossier ON fichier');
        $this->addSql('CREATE INDEX IDX_9B76551F611C0C56 ON fichier (dossier_id)');
        $this->addSql('ALTER TABLE fichier ADD CONSTRAINT FK_9B76551F611C0C56 FOREIGN KEY (dossier_id) REFERENCES dossier (id)');
        $this->addSql('ALTER TABLE fichier ADD CONSTRAINT FK_9B76551F7B76485F FOREIGN KEY (boite_physique_id) REFERENCES boite_physique (id)');
        $this->addSql('DROP INDEX idx_mouvement_type ON mouvement');
        $this->addSql('DROP INDEX idx_mouvement_date ON mouvement');
        $this->addSql('DROP INDEX idx_mouvement_date_creation ON mouvement');
        $this->addSql('ALTER TABLE mouvement DROP FOREIGN KEY FK_5B51FC3EFB88E14F');
        $this->addSql('ALTER TABLE mouvement DROP FOREIGN KEY FK_5B51FC3EF915CFE');
        $this->addSql('ALTER TABLE mouvement DROP FOREIGN KEY FK_5B51FC3E611C0C56');
        $this->addSql('DROP INDEX idx_mouvement_utilisateur ON mouvement');
        $this->addSql('CREATE INDEX IDX_5B51FC3EFB88E14F ON mouvement (utilisateur_id)');
        $this->addSql('DROP INDEX idx_mouvement_fichier ON mouvement');
        $this->addSql('CREATE INDEX IDX_5B51FC3EF915CFE ON mouvement (fichier_id)');
        $this->addSql('DROP INDEX idx_mouvement_dossier ON mouvement');
        $this->addSql('CREATE INDEX IDX_5B51FC3E611C0C56 ON mouvement (dossier_id)');
        $this->addSql('ALTER TABLE mouvement ADD CONSTRAINT FK_5B51FC3EFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE mouvement ADD CONSTRAINT FK_5B51FC3EF915CFE FOREIGN KEY (fichier_id) REFERENCES fichier (id)');
        $this->addSql('ALTER TABLE mouvement ADD CONSTRAINT FK_5B51FC3E611C0C56 FOREIGN KEY (dossier_id) REFERENCES dossier (id)');
        $this->addSql('DROP INDEX idx_utilisateur_email ON utilisateur');
        $this->addSql('DROP INDEX idx_utilisateur_statut ON utilisateur');
        $this->addSql('DROP INDEX idx_utilisateur_nom ON utilisateur');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B3CCF9E01E');
        $this->addSql('DROP INDEX idx_utilisateur_departement ON utilisateur');
        $this->addSql('CREATE INDEX IDX_1D1C63B3CCF9E01E ON utilisateur (departement_id)');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B3CCF9E01E FOREIGN KEY (departement_id) REFERENCES departement (id)');
    }
}
