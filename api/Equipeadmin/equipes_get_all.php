<?php
require_once __DIR__ . "/../../cors.php";

session_start();

// ------------------------------------------------------------
// Chargement des classes nécessaires
// ------------------------------------------------------------
require_once __DIR__ . "/../../classes/Database.php";        // ✔ Utilisation de ta classe Database
require_once __DIR__ . "/../../classes/CEquipeAdmin.php";
require_once __DIR__ . "/../../classes/ControleurEquipeAdmin.php";

// ------------------------------------------------------------
// Connexion BDD via ta classe Database (plus propre)
// ------------------------------------------------------------
$pdo = Database::connect();  // ✔ Remplace le PDO brut

// ------------------------------------------------------------
// Instanciation du contrôleur
// ------------------------------------------------------------
$controleur = new ControleurEquipeAdmin($pdo);

// ------------------------------------------------------------
// Récupération orientée objet
// ------------------------------------------------------------
$equipes = $controleur->getToutesLesEquipes();

// ------------------------------------------------------------
// Conversion objets → tableaux pour JSON
// ------------------------------------------------------------
$equipesArray = array_map(fn($e) => get_object_vars($e), $equipes);

// ------------------------------------------------------------
// Réponse JSON
// ------------------------------------------------------------
echo json_encode([
    "success" => true,
    "equipes" => $equipesArray
]);
exit;
