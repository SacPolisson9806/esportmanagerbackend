<?php
// Autoriser CORS avec credentials
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'super_admin') {
    echo json_encode(["success" => false, "message" => "Accès refusé"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'] ?? null;
$statut = $data['statut'] ?? null;

if (!$id || !$statut) {
    echo json_encode(["success" => false, "message" => "Paramètres manquants"]);
    exit;
}

if (!in_array($statut, ["actif", "restreint"])) {
    echo json_encode(["success" => false, "message" => "Statut invalide"]);
    exit;
}

$host = "localhost";
$dbname = "esport_manager";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("UPDATE utilisateur SET statut = :statut, ban_expire = NULL WHERE id_utilisateur = :id");
    $stmt->execute([
        ":statut" => $statut,
        ":id" => $id
    ]);

    echo json_encode(["success" => true]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erreur serveur"]);
}
