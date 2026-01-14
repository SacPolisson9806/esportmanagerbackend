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
require_once __DIR__ . "/../../classes/Database.php";
require_once __DIR__ . "/../../classes/ControleurDemandeAdmin.php";

// ------------------------------------------------------------
// Démarre la session pour vérifier le rôle de l'utilisateur connecté
// ------------------------------------------------------------
session_start();

// ------------------------------------------------------------
// Format de réponse JSON
// ------------------------------------------------------------
header("Content-Type: application/json");

// ------------------------------------------------------------
// Vérification : seul un super_admin peut accepter une demande
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
// Lecture du JSON envoyé par le front (React)
// ------------------------------------------------------------
$data = json_decode(file_get_contents("php://input"), true);

// ------------------------------------------------------------
// Récupération des paramètres obligatoires
// ------------------------------------------------------------
$id_demande = $data["id_demande"] ?? null;       // ID de la demande à accepter
$id_utilisateur = $data["id_utilisateur"] ?? null; // ID de l'utilisateur concerné

// ------------------------------------------------------------
// Vérification des paramètres
// ------------------------------------------------------------
if (!$id_demande || !$id_utilisateur) {
    echo json_encode([
        "success" => false,
        "message" => "Paramètres manquants"
    ]);
    exit;
}

// ------------------------------------------------------------
// Appel métier : accepter la demande
// Workflow interne (dans ControleurDemandeAdmin) :
// 1. Marquer la demande comme acceptée
// 2. Donner le rôle admin_equipe à l'utilisateur
// 3. Valider l'admin (admin_valide = 1)
// 4. Supprimer les autres demandes de cet utilisateur
// ------------------------------------------------------------
$ctrl->accepter($id_demande, $id_utilisateur);

// ------------------------------------------------------------
// Réponse envoyée au front
// ------------------------------------------------------------
echo json_encode([
    "success" => true,
    "message" => "Demande acceptée"
]);
