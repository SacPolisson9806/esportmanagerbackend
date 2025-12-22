<?php
// CORS pour toutes les requêtes, y compris OPTIONS
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => 'localhost',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

// Chargement des classes
require_once __DIR__ . "/../classes/CUtilisateur.php";
require_once __DIR__ . "/../classes/ControleurUtilisateur.php";

// Connexion BDD
$host = "localhost"; 
$dbname = "esport_manager";   
$username = "root"; 
$password = ""; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupération JSON
    $raw = file_get_contents("php://input");
    $data = json_decode($raw);

    if (!isset($data->pseudo) || !isset($data->mot_de_passe)) {
        echo json_encode(["success" => false, "message" => "Données manquantes"]);
        exit;
    }

    $pseudo = $data->pseudo;
    $mot_de_passe = $data->mot_de_passe;

    // Contrôleur
    $controleur = new ControleurUtilisateur($pdo);

    // Vérification connexion
    $resultat = $controleur->verifierConnexion($pseudo, $mot_de_passe);

    if (!$resultat["success"]) {
        echo json_encode($resultat);
        exit;
    }

    /** @var CUtilisateur $user */
    $user = $resultat["user"];

    // Création session
    $_SESSION['user'] = [
        "id_utilisateur" => $user->id,
        "pseudo" => $user->pseudo,
        "role" => $user->role,
        "statut" => $user->statut,
        "permissions" => $user->permissions,
        "ban_expire" => $user->ban_expire,
        "id_equipe" => $user->id_equipe
    ];

    echo json_encode([
        "success" => true,
        "message" => "Connexion réussie",
        "user" => $_SESSION['user']
    ]);
    exit;

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erreur SQL : " . $e->getMessage()]);
    exit;
}
