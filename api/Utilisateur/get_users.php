<?php
// ------------------------------------------------------------
// Active les règles CORS (autorise les requêtes venant du front)
// ------------------------------------------------------------
require_once __DIR__ . "/../../cors.php";

// ------------------------------------------------------------
// Démarre la session pour vérifier le rôle de l'utilisateur connecté
// ------------------------------------------------------------
session_start();

// ------------------------------------------------------------
// Vérification : seul un super_admin peut accéder à la liste complète
// ------------------------------------------------------------
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'super_admin') {
    echo json_encode(["success" => false, "message" => "Accès refusé"]);
    exit;
}

// ------------------------------------------------------------
// Chargement des classes nécessaires
// CUtilisateur = modèle utilisateur
// ControleurUtilisateur = logique métier (CRUD, gestion des rôles, etc.)
// Database = connexion centralisée
// ------------------------------------------------------------
require_once __DIR__ . "/../../classes/CUtilisateur.php";
require_once __DIR__ . "/../../classes/ControleurUtilisateur.php";
require_once __DIR__ . "/../../classes/Database.php";

try {
    // ------------------------------------------------------------
    // Connexion à la base via ta classe Database (plus propre)
    // ------------------------------------------------------------
    $pdo = Database::connect();

    // ------------------------------------------------------------
    // Instanciation du contrôleur utilisateur
    // ------------------------------------------------------------
    $controleur = new ControleurUtilisateur($pdo);

    // ------------------------------------------------------------
    // Récupération de tous les utilisateurs
    // Retourne un tableau associatif contenant :
    // - id_utilisateur
    // - pseudo
    // - email
    // - role
    // - statut
    // - id_equipe
    // - permissions
    // - ban_expire
    // ------------------------------------------------------------
    $users = $controleur->getTousLesUtilisateurs();

    // ------------------------------------------------------------
    // Réponse JSON envoyée au front
    // ------------------------------------------------------------
    echo json_encode([
        "success" => true,
        "users" => $users
    ]);
    exit;

} catch (PDOException $e) {

    // ------------------------------------------------------------
    // Gestion d'erreur SQL (ex: base inaccessible)
    // ------------------------------------------------------------
    echo json_encode(["success" => false, "message" => "Erreur serveur"]);
    exit;
}
