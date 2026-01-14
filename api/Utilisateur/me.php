<?php
// ------------------------------------------------------------
// Active les règles CORS (autorise les requêtes venant du front)
// ------------------------------------------------------------
require_once __DIR__ . "/../../cors.php";

// ------------------------------------------------------------
// Configuration des cookies de session
// - httponly : empêche l'accès JavaScript → sécurité
// - samesite Lax : limite les attaques CSRF
// - secure : à mettre sur true si HTTPS
// ------------------------------------------------------------
session_set_cookie_params([
    'lifetime' => 0,      // session détruite à la fermeture du navigateur
    'path' => '/',
    'domain' => 'localhost',
    'secure' => false,    // mettre true si HTTPS
    'httponly' => true,   // empêche JS d'accéder au cookie
    'samesite' => 'Lax'   // limite les attaques CSRF
]);

// ------------------------------------------------------------
// Démarre la session pour accéder aux informations utilisateur
// ------------------------------------------------------------
session_start();

// ------------------------------------------------------------
// Vérifie si un utilisateur est connecté
// ------------------------------------------------------------
if (!isset($_SESSION['user'])) {
    echo json_encode([
        "success" => false,
        "message" => "Non connecté"
    ]);
    exit;
}

// ------------------------------------------------------------
// Si on arrive ici : un utilisateur est connecté
// On renvoie toutes ses informations stockées en session
// ------------------------------------------------------------
echo json_encode([
    "success" => true,
    "user" => $_SESSION['user']
]);
exit;
