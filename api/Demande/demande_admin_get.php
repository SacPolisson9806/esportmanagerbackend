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
// Démarre la session pour identifier l'utilisateur connecté
// ------------------------------------------------------------
session_start();

// ------------------------------------------------------------
// Format de réponse JSON
// ------------------------------------------------------------
header("Content-Type: application/json");

// ------------------------------------------------------------
// Vérification : l'utilisateur doit être connecté
// ------------------------------------------------------------
if (!isset($_SESSION["user"])) {
    echo json_encode([
        "success" => false,
        "message" => "Non connecté"
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
// ID de l'utilisateur connecté
// ------------------------------------------------------------
$id_utilisateur = $_SESSION["user"]["id_utilisateur"];

// ------------------------------------------------------------
// Récupération de toutes les demandes existantes
// ------------------------------------------------------------
$demandes = $ctrl->getAll();

// ------------------------------------------------------------
// Recherche de la demande de l'utilisateur connecté
// ------------------------------------------------------------
$maDemande = null;

foreach ($demandes as $d) {

    // On ne garde que les demandes appartenant à l'utilisateur
    if ($d["id_utilisateur"] == $id_utilisateur) {

        // ------------------------------------------------------------
        // Cas particulier : demande refusée mais déjà vue
        // → On l'ignore complètement (l'utilisateur peut refaire une demande)
        // ------------------------------------------------------------
        if ($d["statut"] === "refuse" && (int)$d["vue_par_utilisateur"] === 1) {
            $maDemande = null;
            break;
        }

        // ------------------------------------------------------------
        // Sinon, on garde la demande trouvée
        // ------------------------------------------------------------
        $maDemande = $d;
        break;
    }
}

// ------------------------------------------------------------
// Construction de la réponse JSON
// ------------------------------------------------------------
$response = [
    "success" => true,
    "demande" => $maDemande
];

// ------------------------------------------------------------
// Si la demande est refusée → afficher la modale + raison du refus
// ------------------------------------------------------------
if ($maDemande && $maDemande["statut"] === "refuse") {
    $response["show_refus_modal"] = true;              // Le front doit afficher la modale
    $response["raison_refus"] = $maDemande["raison_refus"]; // Message de refus
    $response["can_send_request"] = true;              // L'utilisateur peut refaire une demande
}

// ------------------------------------------------------------
// Si la demande est en attente → l'utilisateur ne peut pas en envoyer une nouvelle
// ------------------------------------------------------------
if ($maDemande && $maDemande["statut"] === "en_attente") {
    $response["can_send_request"] = false;
}

// ------------------------------------------------------------
// Envoi de la réponse JSON au front
// ------------------------------------------------------------
echo json_encode($response);
