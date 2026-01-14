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
// Démarre la session pour vérifier l'utilisateur connecté
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
// Lecture du JSON envoyé par le front (React)
// ------------------------------------------------------------
$data = json_decode(file_get_contents("php://input"), true);

// ------------------------------------------------------------
// Récupération des champs du formulaire
// ------------------------------------------------------------
$nom = $data["nom_equipe"] ?? null;
$desc = $data["description"] ?? null;

// ------------------------------------------------------------
// Vérification des champs obligatoires
// ------------------------------------------------------------
if (!$nom || !$desc) {
    echo json_encode([
        "success" => false,
        "message" => "Champs manquants"
    ]);
    exit;
}

// ------------------------------------------------------------
// ID de l'utilisateur connecté (celui qui fait la demande)
// ------------------------------------------------------------
$id_utilisateur = $_SESSION["user"]["id_utilisateur"];

// ------------------------------------------------------------
// Vérifier si une demande existe déjà pour cet utilisateur
// ------------------------------------------------------------
$demandes = $ctrl->getAll();

foreach ($demandes as $d) {

    if ($d["id_utilisateur"] == $id_utilisateur) {

        // ------------------------------------------------------------
        // Cas 1 : une demande est déjà en attente → on bloque
        // ------------------------------------------------------------
        if ($d["statut"] === "en_attente") {
            echo json_encode([
                "success" => false,
                "message" => "Vous avez déjà une demande en cours"
            ]);
            exit;
        }

        // ------------------------------------------------------------
        // Cas 2 : demande refusée mais pas encore vue → on bloque
        // L'utilisateur doit d'abord lire la raison du refus
        // ------------------------------------------------------------
        if ($d["statut"] === "refuse" && (int)$d["vue_par_utilisateur"] === 0) {
            echo json_encode([
                "success" => false,
                "message" => "Votre demande a été refusée. Veuillez lire la raison."
            ]);
            exit;
        }

        // ------------------------------------------------------------
        // Cas 3 : refusée + vue → OK, il peut refaire une demande
        // ------------------------------------------------------------
        if ($d["statut"] === "refuse" && (int)$d["vue_par_utilisateur"] === 1) {
            break; // on autorise la création
        }

        // ------------------------------------------------------------
        // Cas 4 : déjà acceptée → OK, il peut refaire une demande
        // (ex : changement d'équipe)
        // ------------------------------------------------------------
        if ($d["statut"] === "accepte") {
            break;
        }
    }
}

// ------------------------------------------------------------
// Création de la demande via le contrôleur métier
// ------------------------------------------------------------
$ok = $ctrl->create($id_utilisateur, $nom, $desc);

// ------------------------------------------------------------
// Réponse envoyée au front
// ------------------------------------------------------------
echo json_encode([
    "success" => $ok,
    "message" => $ok ? "Demande envoyée avec succès" : "Erreur lors de l'envoi"
]);
