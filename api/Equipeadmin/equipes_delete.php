<?php
require_once __DIR__ . "/../../cors.php";

session_start();

// ------------------------------------------------------------
// Vérification : seul un super_admin peut supprimer une équipe
// ------------------------------------------------------------
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'super_admin') {
    echo json_encode([
        "success" => false,
        "message" => "Accès refusé"
    ]);
    exit;
}

// ------------------------------------------------------------
// Chargement des classes nécessaires
// ------------------------------------------------------------
require_once __DIR__ . "/../../classes/Database.php";        // ✔ Utilisation de ta classe Database
require_once __DIR__ . "/../../classes/CEquipeAdmin.php";
require_once __DIR__ . "/../../classes/ControleurEquipeAdmin.php";

// ------------------------------------------------------------
// Lecture du JSON envoyé par le front
// ------------------------------------------------------------
$data = json_decode(file_get_contents("php://input"), true);
$id_equipe = $data['id_equipe'] ?? null;

// ------------------------------------------------------------
// Vérification du paramètre obligatoire
// ------------------------------------------------------------
if (!$id_equipe) {
    echo json_encode([
        "success" => false,
        "message" => "ID manquant"
    ]);
    exit;
}

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
    // Suppression orientée objet via ton contrôleur
    // ------------------------------------------------------------
    $result = $controleur->supprimerEquipe($id_equipe);

    echo json_encode($result);
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
