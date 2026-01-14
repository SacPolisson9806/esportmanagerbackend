<?php
// ------------------------------------------------------------
// Active les règles CORS (autorise les requêtes venant du front)
// ------------------------------------------------------------
require_once __DIR__ . "/../../cors.php";

// ------------------------------------------------------------
// Active l'affichage des erreurs (utile en développement)
// ------------------------------------------------------------
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ------------------------------------------------------------
// Configuration des cookies de session
// - httponly : empêche l'accès JS → sécurité
// - samesite Lax : empêche CSRF basique
// ------------------------------------------------------------
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => 'localhost',
    'secure' => false,   // mettre true si HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

// ------------------------------------------------------------
// Démarre la session (obligatoire pour stocker l'utilisateur connecté)
// ------------------------------------------------------------
session_start();

// ------------------------------------------------------------
// Chargement des classes nécessaires
// ------------------------------------------------------------
require_once __DIR__ . "/../../classes/CUtilisateur.php";
require_once __DIR__ . "/../../classes/ControleurUtilisateur.php";
require_once __DIR__ . "/../../classes/Database.php"; // 🔥 Utilisation de ta classe Database

try {
    // ------------------------------------------------------------
    // Connexion à la base via ta classe Database (plus propre)
    // ------------------------------------------------------------
    $pdo = Database::connect();

    // ------------------------------------------------------------
    // Lecture du JSON envoyé par le front (React)
    // ------------------------------------------------------------
    $raw = file_get_contents("php://input");
    $data = json_decode($raw);

    // ------------------------------------------------------------
    // Vérification des champs obligatoires
    // ------------------------------------------------------------
    if (!isset($data->pseudo) || !isset($data->mot_de_passe)) {
        echo json_encode(["success" => false, "message" => "Données manquantes"]);
        exit;
    }

    $pseudo = $data->pseudo;
    $mot_de_passe = $data->mot_de_passe;

    // ------------------------------------------------------------
    // Instanciation du contrôleur utilisateur
    // ------------------------------------------------------------
    $controleur = new ControleurUtilisateur($pdo);

    // ------------------------------------------------------------
    // Vérification des identifiants
    // - pseudo incorrect → erreur
    // - mot de passe incorrect → erreur
    // - compte banni → erreur
    // ------------------------------------------------------------
    $resultat = $controleur->verifierConnexion($pseudo, $mot_de_passe);

    if (!$resultat["success"]) {
        echo json_encode($resultat);
        exit;
    }

    /** @var CUtilisateur $user */
    $user = $resultat["user"];

    // ------------------------------------------------------------
    // Création de la session utilisateur
    // On stocke toutes les infos nécessaires pour le front
    // ------------------------------------------------------------
    $_SESSION['user'] = [
        "id_utilisateur" => $user->id,
        "pseudo" => $user->pseudo,
        "role" => $user->role,
        "statut" => $user->statut,
        "permissions" => $user->permissions,
        "ban_expire" => $user->ban_expire,
        "id_equipe" => $user->id_equipe,
        "admin_valide" => $user->admin_valide   // 🔥 indispensable pour le salon admin
    ];

    // ------------------------------------------------------------
    // Réponse envoyée au front
    // ------------------------------------------------------------
    echo json_encode([
        "success" => true,
        "message" => "Connexion réussie",
        "user" => $_SESSION['user']
    ]);
    exit;

} catch (PDOException $e) {

    // ------------------------------------------------------------
    // Gestion d'erreur SQL (ex: base inaccessible)
    // ------------------------------------------------------------
    echo json_encode(["success" => false, "message" => "Erreur SQL : " . $e->getMessage()]);
    exit;
}
