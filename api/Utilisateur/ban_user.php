<?php
// ------------------------------------------------------------
// Active les règles CORS (autorise les requêtes venant du front)
// ------------------------------------------------------------
require_once __DIR__ . "/../../cors.php";

// ------------------------------------------------------------
// Connexion centralisée via ta classe Database
// ------------------------------------------------------------
require_once __DIR__ . "/../../classes/Database.php";

// ------------------------------------------------------------
// Démarre la session pour vérifier le rôle de l'utilisateur connecté
// ------------------------------------------------------------
session_start();

// ------------------------------------------------------------
// Vérification : seul un super_admin peut bannir un utilisateur
// ------------------------------------------------------------
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'super_admin') {
    echo json_encode(["success" => false, "message" => "Accès refusé"]);
    exit;
}

// ------------------------------------------------------------
// Chargement des classes nécessaires
// ------------------------------------------------------------
require_once __DIR__ . "/../../classes/CUtilisateur.php";
require_once __DIR__ . "/../../classes/ControleurUtilisateur.php";

// ------------------------------------------------------------
// Lecture du JSON envoyé par le front (React)
// ------------------------------------------------------------
$data = json_decode(file_get_contents("php://input"), true);

// ------------------------------------------------------------
// Récupération des paramètres envoyés
// ------------------------------------------------------------
$id = $data['id'] ?? null;                 // ID de l'utilisateur à bannir
$duration = $data['duration'] ?? null;     // Durée du ban (1d, 7d, permanent, custom…)
$customDate = $data['customDate'] ?? null; // Date personnalisée si duration = custom
$permissions = $data['permissions'] ?? null; // Permissions restreintes pendant le ban

// ------------------------------------------------------------
// Correction : permissions peut être un tableau vide → []
// Donc on vérifie uniquement si c'est null
// ------------------------------------------------------------
if (!$id || !$duration || $permissions === null) {
    echo json_encode(["success" => false, "message" => "Paramètres manquants"]);
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
    // Bannissement via la méthode orientée objet
    // - calcule la date de fin
    // - met à jour statut + permissions
    // ------------------------------------------------------------
    $result = $controleur->bannirUtilisateur($id, $duration, $customDate, $permissions);

    // ------------------------------------------------------------
    // Réponse JSON envoyée au front
    // ------------------------------------------------------------
    echo json_encode($result);
    exit;

} catch (PDOException $e) {

    // ------------------------------------------------------------
    // Gestion d'erreur SQL (ex: base inaccessible)
    // ------------------------------------------------------------
    echo json_encode(["success" => false, "message" => "Erreur serveur"]);
    exit;
}
