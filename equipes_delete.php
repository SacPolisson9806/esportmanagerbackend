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
$id_equipe = $data['id_equipe'] ?? null;

if (!$id_equipe) {
    echo json_encode(["success" => false, "message" => "ID manquant"]);
    exit;
}

$host = "localhost";
$dbname = "esport_manager";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("DELETE FROM equipe WHERE id_equipe = :id");
    $stmt->bindParam(':id', $id_equipe, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erreur serveur"]);
}
