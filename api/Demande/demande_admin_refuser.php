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
// Vérification : seul un super_admin peut refuser une demande
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
$id_demande = $data["id_demande"] ?? null;   // ID de la demande à refuser
$raisons = $data["raisons_refus"] ?? [];     // Tableau des raisons sélectionnées

// ------------------------------------------------------------
// Vérification des paramètres
// ------------------------------------------------------------
if (!$id_demande) {
    echo json_encode([
        "success" => false,
        "message" => "Paramètres manquants"
    ]);
    exit;
}

// ------------------------------------------------------------
// Fusion des raisons en une seule chaîne
// Exemple : ["Trop vague", "Incomplet"] → "Trop vague, Incomplet"
// ------------------------------------------------------------
$raison = implode(", ", $raisons);

// ------------------------------------------------------------
// Appel métier : refuser la demande
// - Met statut = "refuse"
// - Enregistre la raison
// - Remet vue_par_utilisateur = 0 pour notifier l'utilisateur
// ------------------------------------------------------------
$ctrl->refuser($id_demande, $raison);

// ------------------------------------------------------------
// Réponse envoyée au front
// ------------------------------------------------------------
echo json_encode([
    "success" => true,
    "message" => "Demande refusée"
]);
