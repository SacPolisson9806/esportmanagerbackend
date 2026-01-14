<?php
// ------------------------------------------------------------
// Active les règles CORS (autorise les requêtes venant du front)
// ------------------------------------------------------------
require_once __DIR__ . "/../../cors.php";

// ------------------------------------------------------------
// Chargement du contrôleur utilisateur
// ------------------------------------------------------------
require_once __DIR__ . "/../../classes/ControleurUtilisateur.php";
require_once __DIR__ . "/../../classes/Database.php"; // 🔥 Connexion centralisée

// ------------------------------------------------------------
// Démarre la session pour vérifier le rôle de l'utilisateur connecté
// ------------------------------------------------------------
session_start();

// ------------------------------------------------------------
// Vérification : seul un super_admin peut valider un admin
// ------------------------------------------------------------
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'super_admin') {
    echo json_encode(["success" => false, "message" => "Accès refusé"]);
    exit;
}

// ------------------------------------------------------------
// Lecture du JSON envoyé par le front (React)
// ------------------------------------------------------------
$data = json_decode(file_get_contents("php://input"), true);

// ------------------------------------------------------------
// Vérification du paramètre obligatoire : id de l'utilisateur
// ------------------------------------------------------------
$id = $data['id'] ?? null;

if (!$id) {
    echo json_encode(["success" => false, "message" => "ID manquant"]);
    exit;
}

try {
    // ------------------------------------------------------------
    // Connexion à la base via ta classe Database (plus propre)
    // ------------------------------------------------------------
    $pdo = Database::connect();

    // ------------------------------------------------------------
    // Instanciation du contrôleur utilisateur
    // ------------------------------------------------------------
    $controleur = new ControleurUtilisateur($pdo);

    // ------------------------------------------------------------
    // Validation de l'admin via la méthode orientée objet
    // ------------------------------------------------------------
    $result = $controleur->validerAdmin($id);

    // ------------------------------------------------------------
    // Réponse JSON envoyée au front
    // ------------------------------------------------------------
    echo json_encode($result);

} catch (PDOException $e) {

    // ------------------------------------------------------------
    // Gestion d'erreur SQL (ex: base inaccessible)
    // ------------------------------------------------------------
    echo json_encode(["success" => false, "message" => "Erreur serveur"]);
}
