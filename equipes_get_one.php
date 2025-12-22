<?php
// Autoriser CORS avec credentials
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

session_start();

if (!isset($_GET['id'])) {
    echo json_encode(["success" => false, "message" => "ID manquant"]);
    exit;
}

$id = (int) $_GET['id'];

$host = "localhost";
$dbname = "esport_manager";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT * FROM equipe WHERE id_equipe = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $equipe = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($equipe) {
        echo json_encode(["success" => true, "equipe" => $equipe]);
    } else {
        echo json_encode(["success" => false, "message" => "Équipe introuvable"]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erreur serveur"]);
}
