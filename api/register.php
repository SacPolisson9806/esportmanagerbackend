<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => 'localhost',
    'secure' => false,
    'httponly' => false,
    'samesite' => 'None'
]);

session_start();

// Chargement des classes
require_once __DIR__ . "/../classes/CUtilisateur.php";
require_once __DIR__ . "/../classes/ControleurUtilisateur.php";

$host = "localhost"; 
$dbname = "esport_manager";
$username = "root"; 
$password = ""; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $raw = file_get_contents("php://input");
    $data = json_decode($raw);

    if (!isset($data->pseudo) || !isset($data->email) || !isset($data->mot_de_passe)) {
        echo json_encode(["success" => false, "message" => "Données manquantes"]);
        exit;
    }

    $controleur = new ControleurUtilisateur($pdo);

    $resultat = $controleur->creerUtilisateur(
        $data->pseudo,
        $data->email,
        $data->mot_de_passe
    );

    echo json_encode($resultat);
    exit;

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erreur serveur"]);
    exit;
}
