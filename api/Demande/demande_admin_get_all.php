<?php
// ------------------------------------------------------------
// Active les règles CORS (autorise les requêtes venant du front)
// ------------------------------------------------------------
require_once __DIR__ . "/../../cors.php";

// ------------------------------------------------------------
// Chargement des classes nécessaires
// - Database : connexion centralisée
// - ControleurDemandeAdmin : logique métier des demandes admin
// ------------------------------------------------------------
require_once "../../classes/Database.php";
require_once "../../classes/ControleurDemandeAdmin.php";

// ------------------------------------------------------------
// Démarre la session pour vérifier le rôle de l'utilisateur connecté
// ------------------------------------------------------------
session_start();

// ------------------------------------------------------------
// Format de réponse JSON
// ------------------------------------------------------------
header("Content-Type: application/json");

// ------------------------------------------------------------
// Vérification : seul un super_admin peut consulter toutes les demandes
// ------------------------------------------------------------
if (!isset($_SESSION["user"]) || $_SESSION["user"]["role"] !== "super_admin") {
    echo json_encode([
        "success" => false,
        "message" => "Accès refusé"
    ]);
    exit;
}

// ------------------------------------------------------------
// Connexion à la base via ta classe Database (plus propre)
// ------------------------------------------------------------
$pdo = Database::connect();

// ------------------------------------------------------------
// Instanciation du contrôleur métier des demandes admin
// ------------------------------------------------------------
$ctrl = new ControleurDemandeAdmin($pdo);

// ------------------------------------------------------------
// Récupération de toutes les demandes via le contrôleur
// ------------------------------------------------------------
$demandes = $ctrl->getAll();

// ------------------------------------------------------------
// Réponse envoyée au front
// ------------------------------------------------------------
echo json_encode([
    "success" => true,
    "demandes" => $demandes
]);
