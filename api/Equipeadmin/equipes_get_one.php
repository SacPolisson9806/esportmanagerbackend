<?php
require_once __DIR__ . "/../../cors.php";

session_start();

// ------------------------------------------------------------
// Vérification du paramètre GET
// ------------------------------------------------------------
if (!isset($_GET['id'])) {
    echo json_encode([
        "success" => false,
        "message" => "ID manquant"
    ]);
    exit;
}

$id_equipe = (int) $_GET['id'];

// ------------------------------------------------------------
// Chargement des classes nécessaires
// ------------------------------------------------------------
require_once __DIR__ . "/../../classes/Database.php";        // ✔ Utilisation de ta classe Database
require_once __DIR__ . "/../../classes/CEquipeAdmin.php";    // ✔ Correction du nom du fichier
require_once __DIR__ . "/../../classes/ControleurEquipeAdmin.php";

try {

    // ------------------------------------------------------------
    // Connexion BDD via ta classe Database (plus propre)
    // ------------------------------------------------------------
    $pdo = Database::connect();

    // ------------------------------------------------------------
    // Instanciation du contrôleur
    // ------------------------------------------------------------
    $controleur = new ControleurEquipeAdmin($pdo);

    // ------------------------------------------------------------
    // Récupération orientée objet
    // ------------------------------------------------------------
    $equipe = $controleur->getEquipeById($id_equipe);

    if (!$equipe) {
        echo json_encode([
            "success" => false,
            "message" => "Équipe introuvable"
        ]);
        exit;
    }

    // ------------------------------------------------------------
    // Conversion objet → tableau pour JSON
    // ------------------------------------------------------------
    echo json_encode([
        "success" => true,
        "equipe" => get_object_vars($equipe)
    ]);
    exit;

} catch (Exception $e) {

    // ------------------------------------------------------------
    // Gestion propre des erreurs serveur
    // ------------------------------------------------------------
    echo json_encode([
        "success" => false,
        "message" => "Erreur serveur"
    ]);
    exit;
}
