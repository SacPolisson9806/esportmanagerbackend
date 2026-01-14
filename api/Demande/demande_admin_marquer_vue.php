<?php
// ------------------------------------------------------------
// Active les règles CORS (autorise les requêtes venant du front)
// ------------------------------------------------------------
require_once __DIR__ . "/../../cors.php";

// ------------------------------------------------------------
// Chargement de la classe Database (connexion centralisée)
// ------------------------------------------------------------
require_once "../../classes/Database.php";

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
// ID de l'utilisateur connecté
// ------------------------------------------------------------
$id_utilisateur = $_SESSION["user"]["id_utilisateur"];

// ------------------------------------------------------------
// Mise à jour : marquer la demande refusée comme "vue"
// ------------------------------------------------------------
// Objectif : lorsque l'utilisateur ouvre la modale de refus,
//            on met vue_par_utilisateur = 1 pour indiquer
//            qu'il a bien pris connaissance de la raison du refus.
//
// Cela permet ensuite de lui autoriser une nouvelle demande.
//
$sql = "
    UPDATE demandes_admin_equipe
    SET vue_par_utilisateur = 1
    WHERE id_utilisateur = ? 
      AND statut = 'refuse'
";

// Préparation et exécution de la requête
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_utilisateur]);

// ------------------------------------------------------------
// Réponse envoyée au front
// ------------------------------------------------------------
echo json_encode([
    "success" => true
]);
