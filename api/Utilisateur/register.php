<?php
// ------------------------------------------------------------
// Active les règles CORS (autorise les requêtes venant du front)
// ------------------------------------------------------------
require_once __DIR__ . "/../../cors.php";

// ------------------------------------------------------------
// Configuration des cookies de session
// - httponly : empêche l'accès JavaScript → sécurité
// - samesite None : obligatoire si front et back ne sont pas sur le même domaine
// ------------------------------------------------------------
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => 'localhost',
    'secure' => false,    // mettre true si HTTPS
    'httponly' => false,  // false car le front doit lire le cookie (selon ton choix)
    'samesite' => 'None'
]);

// ------------------------------------------------------------
// Démarre la session (obligatoire pour stocker l'utilisateur)
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
    if (!isset($data->pseudo) || !isset($data->email) || !isset($data->mot_de_passe)) {
        echo json_encode(["success" => false, "message" => "Données manquantes"]);
        exit;
    }

    // ------------------------------------------------------------
    // Instanciation du contrôleur utilisateur
    // ------------------------------------------------------------
    $controleur = new ControleurUtilisateur($pdo);

    // ------------------------------------------------------------
    // Création d'un utilisateur standard (non admin)
    // ------------------------------------------------------------
    $resultat = $controleur->creerUtilisateur(
        $data->pseudo,
        $data->email,
        $data->mot_de_passe
    );

    // ------------------------------------------------------------
    // Réponse envoyée au front
    // ------------------------------------------------------------
    echo json_encode($resultat);
    exit;

} catch (PDOException $e) {

    // ------------------------------------------------------------
    // Gestion d'erreur SQL (ex: base inaccessible)
    // ------------------------------------------------------------
    echo json_encode(["success" => false, "message" => "Erreur serveur"]);
    exit;
}
