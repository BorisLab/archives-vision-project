<?php

/**
 * Script de test pour vérifier la mise à jour automatique du statut
 * lors de la création de mouvements
 * 
 * Usage: php tests/test_mouvement_statut.php
 */

require __DIR__ . '/../vendor/autoload.php';

use App\Kernel;
use App\Entity\Mouvement;
use Symfony\Component\Dotenv\Dotenv;

// Charger les variables d'environnement
(new Dotenv())->bootEnv(__DIR__ . '/../.env');

// Créer le kernel Symfony
$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();

// Récupérer les services nécessaires
$entityManager = $container->get('doctrine')->getManager();
$fichierRepo = $entityManager->getRepository(\App\Entity\Fichier::class);
$dossierRepo = $entityManager->getRepository(\App\Entity\Dossier::class);
$utilisateurRepo = $entityManager->getRepository(\App\Entity\Utilisateur::class);

echo "=== TEST MISE À JOUR AUTOMATIQUE DU STATUT ===\n\n";

// Récupérer un fichier de test
$fichier = $fichierRepo->findOneBy([]);
if (!$fichier) {
    echo "❌ Aucun fichier trouvé dans la base\n";
    exit(1);
}

echo "📄 Fichier testé : #{$fichier->getFichierId()} - {$fichier->getLibelleFichier()}\n";
echo "   Statut initial : " . ($fichier->isStatut() ? '✅ Disponible' : '❌ Indisponible') . "\n\n";

// Récupérer un utilisateur archiviste
$archiviste = $utilisateurRepo->createQueryBuilder('u')
    ->where('u.roles LIKE :role')
    ->setParameter('role', '%ROLE_ARCHIVIST%')
    ->setMaxResults(1)
    ->getQuery()
    ->getOneOrNullResult();

if (!$archiviste) {
    echo "❌ Aucun archiviste trouvé dans la base\n";
    exit(1);
}

// TEST 1: Création d'un mouvement de PRÊT (doit mettre statut à false)
echo "TEST 1: Création d'un mouvement de PRÊT\n";
echo "----------------------------------------\n";

$mouvement1 = new Mouvement();
$mouvement1->setTypeMouvement('pret');
$mouvement1->setDateMouvement(new \DateTime());
$mouvement1->setUtilisateur($archiviste);
$mouvement1->setFichier($fichier);
$mouvement1->setEmprunteurNom('Test Auto - Jean Dupont');
$mouvement1->setDateRetourPrevue(new \DateTime('+30 days'));
$mouvement1->setObservations('Test automatique mise à jour statut');

// Appliquer la logique de mise à jour du statut (comme dans le controller)
if ($mouvement1->getFichier()) {
    $nouveauStatut = match($mouvement1->getTypeMouvement()) {
        'arrivage', 'reintegration' => true,
        'pret', 'consultation' => false,
        'deplacement' => null,
        default => null
    };
    
    if ($nouveauStatut !== null) {
        $mouvement1->getFichier()->setStatut($nouveauStatut);
    }
}

$entityManager->persist($mouvement1);
$entityManager->flush();

echo "✅ Mouvement de prêt créé (ID: {$mouvement1->getId()})\n";
echo "   Statut du fichier après prêt : " . ($fichier->isStatut() ? '✅ Disponible' : '❌ Indisponible') . "\n";

if ($fichier->isStatut() === false) {
    echo "   ✅ TEST RÉUSSI : Le fichier est bien marqué comme indisponible\n\n";
} else {
    echo "   ❌ TEST ÉCHOUÉ : Le fichier devrait être indisponible\n\n";
}

// TEST 2: Création d'un mouvement de RÉINTÉGRATION (doit remettre statut à true)
echo "TEST 2: Création d'un mouvement de RÉINTÉGRATION\n";
echo "------------------------------------------------\n";

$mouvement2 = new Mouvement();
$mouvement2->setTypeMouvement('reintegration');
$mouvement2->setDateMouvement(new \DateTime());
$mouvement2->setUtilisateur($archiviste);
$mouvement2->setFichier($fichier);
$mouvement2->setObservations('Test automatique réintégration suite au prêt #' . $mouvement1->getId());
$mouvement2->setStatut('termine');

// Marquer le prêt comme terminé
$mouvement1->setStatut('termine');
$mouvement1->setDateRetourEffective(new \DateTime());

// Appliquer la logique de mise à jour du statut
if ($mouvement2->getFichier()) {
    $nouveauStatut = match($mouvement2->getTypeMouvement()) {
        'arrivage', 'reintegration' => true,
        'pret', 'consultation' => false,
        'deplacement' => null,
        default => null
    };
    
    if ($nouveauStatut !== null) {
        $mouvement2->getFichier()->setStatut($nouveauStatut);
    }
}

$entityManager->persist($mouvement2);
$entityManager->flush();

echo "✅ Mouvement de réintégration créé (ID: {$mouvement2->getId()})\n";
echo "   Statut du fichier après réintégration : " . ($fichier->isStatut() ? '✅ Disponible' : '❌ Indisponible') . "\n";

if ($fichier->isStatut() === true) {
    echo "   ✅ TEST RÉUSSI : Le fichier est bien marqué comme disponible\n\n";
} else {
    echo "   ❌ TEST ÉCHOUÉ : Le fichier devrait être disponible\n\n";
}

// TEST 3: Création d'un mouvement de DÉPLACEMENT (ne doit pas changer le statut)
echo "TEST 3: Création d'un mouvement de DÉPLACEMENT\n";
echo "----------------------------------------------\n";

$statutAvantDeplacement = $fichier->isStatut();

$mouvement3 = new Mouvement();
$mouvement3->setTypeMouvement('deplacement');
$mouvement3->setDateMouvement(new \DateTime());
$mouvement3->setUtilisateur($archiviste);
$mouvement3->setFichier($fichier);
$mouvement3->setObservations('Test automatique déplacement');
$mouvement3->setStatut('termine');

// Appliquer la logique (déplacement ne change pas le statut)
if ($mouvement3->getFichier()) {
    $nouveauStatut = match($mouvement3->getTypeMouvement()) {
        'arrivage', 'reintegration' => true,
        'pret', 'consultation' => false,
        'deplacement' => null,
        default => null
    };
    
    if ($nouveauStatut !== null) {
        $mouvement3->getFichier()->setStatut($nouveauStatut);
    }
}

$entityManager->persist($mouvement3);
$entityManager->flush();

echo "✅ Mouvement de déplacement créé (ID: {$mouvement3->getId()})\n";
echo "   Statut avant déplacement : " . ($statutAvantDeplacement ? '✅ Disponible' : '❌ Indisponible') . "\n";
echo "   Statut après déplacement : " . ($fichier->isStatut() ? '✅ Disponible' : '❌ Indisponible') . "\n";

if ($fichier->isStatut() === $statutAvantDeplacement) {
    echo "   ✅ TEST RÉUSSI : Le statut n'a pas changé lors du déplacement\n\n";
} else {
    echo "   ❌ TEST ÉCHOUÉ : Le statut ne devrait pas changer lors d'un déplacement\n\n";
}

echo "=== RÉSUMÉ DES TESTS ===\n";
echo "Mouvements créés : {$mouvement1->getId()}, {$mouvement2->getId()}, {$mouvement3->getId()}\n";
echo "Fichier testé : #{$fichier->getFichierId()}\n";
echo "Statut final : " . ($fichier->isStatut() ? '✅ Disponible' : '❌ Indisponible') . "\n";
echo "\n✅ Tous les tests sont terminés !\n";
