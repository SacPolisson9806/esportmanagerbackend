<?php
// ------------------------------------------------------------
// Active les règles CORS (autorise les requêtes venant du front)
// ------------------------------------------------------------
require_once __DIR__ . "/../../cors.php";

// ------------------------------------------------------------
// Démarre la session pour vérifier le rôle de l'utilisateur connecté
// ------------------------------------------------------------
session_start();

// ------------------------------------------------------------
// Vérification : seul un super_admin peut créer un autre admin
// ------------------------------------------------------------
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'super_admin') {
    echo json_encode(["success" => false, "message" => "Accès interdit"]);
    exit;
}

// ------------------------------------------------------------
// Chargement des classes nécessaires
// CUtilisateur = modèle utilisateur
// ControleurUtilisateur = logique métier (CRUD, vérifications, etc.)
// ------------------------------------------------------------
require_once __DIR__ . "/../../classes/CUtilisateur.php";
require_once __DIR__ . "/../../classes/ControleurUtilisateur.php";
require_once __DIR__ . "/../../classes/Database.php"; // 🔥 Utilisation de ta classe Database

// ------------------------------------------------------------
// Lecture du JSON envoyé par le front (React)
// ------------------------------------------------------------
$data = json_decode(file_get_contents("php://input"), true);

// ------------------------------------------------------------
// Vérification des paramètres obligatoires
// ------------------------------------------------------------
if (
    !$data ||
    !isset($data["pseudo"], $data["email"], $data["mot_de_passe"], $data["role"])
) {
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
    // Appel orienté objet : création d'un utilisateur admin
    // - pseudo
    // - email
    // - mot de passe
    // - rôle (admin, modérateur, etc.)
    // - id_equipe (optionnel)
    // - permissions (optionnel)
    // ------------------------------------------------------------
    $result = $controleur->creerUtilisateurAdmin(
        $data["pseudo"],
        $data["email"],
        $data["mot_de_passe"],
        $data["role"],
        $data["id_equipe"] ?? null,
        $data["permissions"] ?? []
    );

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
