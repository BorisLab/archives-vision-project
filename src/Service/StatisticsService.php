<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class StatisticsService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Récupère les statistiques globales
     */
    public function getGlobalStatistics(): array
    {
        $conn = $this->entityManager->getConnection();

        // Total dossiers
        $totalDossiers = $conn->fetchOne('SELECT COUNT(*) FROM dossier');
        
        // Total fichiers
        $totalFichiers = $conn->fetchOne('SELECT COUNT(*) FROM fichier');
        
        // Total utilisateurs
        $totalUtilisateurs = $conn->fetchOne('SELECT COUNT(*) FROM utilisateur');
        
        // Total départements
        $totalDepartements = $conn->fetchOne('SELECT COUNT(*) FROM departement');

        // Dossiers actifs
        $dossiersActifs = $conn->fetchOne('SELECT COUNT(*) FROM dossier WHERE statut = 1');

        // Fichiers physiques vs numériques
        $fichiersPhysiques = $conn->fetchOne("SELECT COUNT(*) FROM fichier WHERE format = 'Physique'");
        $fichiersNumeriques = $conn->fetchOne("SELECT COUNT(*) FROM fichier WHERE format = 'Numérique'");

        return [
            'total_dossiers' => (int) $totalDossiers,
            'total_fichiers' => (int) $totalFichiers,
            'total_utilisateurs' => (int) $totalUtilisateurs,
            'total_departements' => (int) $totalDepartements,
            'dossiers_actifs' => (int) $dossiersActifs,
            'fichiers_physiques' => (int) $fichiersPhysiques,
            'fichiers_numeriques' => (int) $fichiersNumeriques,
        ];
    }

    /**
     * Récupère les statistiques par département
     */
    public function getStatisticsByDepartement(): array
    {
        $query = $this->entityManager->createQuery('
            SELECT d.libelle_dep as departement, 
                   COUNT(dos.id) as total_dossiers,
                   COUNT(f.id) as total_fichiers
            FROM App\Entity\Departement d
            LEFT JOIN d.dossiers dos
            LEFT JOIN dos.fichiers f
            GROUP BY d.id, d.libelle_dep
            ORDER BY total_dossiers DESC
        ');

        return $query->getResult();
    }

    /**
     * Récupère l'évolution des créations par mois (6 derniers mois)
     */
    public function getCreationsByMonth(int $months = 6): array
    {
        $conn = $this->entityManager->getConnection();
        
        $sql = "
            SELECT 
                DATE_FORMAT(date_creation, '%Y-%m') as mois,
                COUNT(*) as total
            FROM dossier
            WHERE date_creation >= DATE_SUB(NOW(), INTERVAL ? MONTH)
            GROUP BY DATE_FORMAT(date_creation, '%Y-%m')
            ORDER BY mois ASC
        ";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([$months]);

        return $result->fetchAllAssociative();
    }

    /**
     * Récupère les consultations récentes (via audit_log)
     */
    public function getRecentConsultations(int $limit = 10): array
    {
        $query = $this->entityManager->createQuery('
            SELECT a.id, a.action, a.entity_type, a.entity_id, a.created_at,
                   a.user_email, a.user_id
            FROM App\Entity\AuditLog a
            WHERE a.action = :action
            ORDER BY a.created_at DESC
        ')
        ->setParameter('action', 'Consultation')
        ->setMaxResults($limit);

        return $query->getResult();
    }

    /**
     * Récupère les alertes de destruction à venir (30 prochains jours)
     */
    public function getUpcomingDestructions(): array
    {
        $query = $this->entityManager->createQuery('
            SELECT d.id, d.libelle_dossier, d.date_fin, r.duree_conservation_annees,
                   dept.libelle_dep as departement
            FROM App\Entity\Dossier d
            LEFT JOIN d.regle_retention r
            LEFT JOIN d.departement dept
            WHERE r.duree_conservation_annees IS NOT NULL
              AND DATE_ADD(d.date_fin, r.duree_conservation_annees, \'YEAR\') 
                  BETWEEN CURRENT_DATE() AND DATE_ADD(CURRENT_DATE(), 30, \'DAY\')
            ORDER BY d.date_fin ASC
        ')
        ->setMaxResults(10);

        try {
            return $query->getResult();
        } catch (\Exception $e) {
            // Si la requête échoue (ex: champs manquants), retourner tableau vide
            return [];
        }
    }

    /**
     * Récupère les statistiques des demandes d'accès
     */
    public function getDemandesAccessStatistics(): array
    {
        $conn = $this->entityManager->getConnection();

        $enAttente = $conn->fetchOne("SELECT COUNT(*) FROM demande_acces WHERE statut = 'en_attente'");
        $approuvees = $conn->fetchOne("SELECT COUNT(*) FROM demande_acces WHERE statut = 'approuvee'");
        $refusees = $conn->fetchOne("SELECT COUNT(*) FROM demande_acces WHERE statut = 'refusee'");

        return [
            'en_attente' => (int) $enAttente,
            'approuvees' => (int) $approuvees,
            'refusees' => (int) $refusees,
            'total' => (int) ($enAttente + $approuvees + $refusees),
        ];
    }

    /**
     * Récupère les utilisateurs les plus actifs (via audit_log)
     */
    public function getTopActiveUsers(int $limit = 5): array
    {
        $conn = $this->entityManager->getConnection();
        
        $sql = "
            SELECT u.id, u.nom, u.prenoms, u.email, COUNT(a.id) as total_actions
            FROM audit_log a
            INNER JOIN utilisateur u ON a.user_id = u.id
            WHERE a.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY u.id, u.nom, u.prenoms, u.email
            ORDER BY total_actions DESC
        ";

        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();
        $all = $result->fetchAllAssociative();

        return array_slice($all, 0, $limit);
    }

    /**
     * Récupère la distribution des formats de fichiers
     */
    public function getFileFormatDistribution(): array
    {
        $query = $this->entityManager->createQuery('
            SELECT f.format, COUNT(f.id) as total
            FROM App\Entity\Fichier f
            GROUP BY f.format
            ORDER BY total DESC
        ');

        return $query->getResult();
    }
}
