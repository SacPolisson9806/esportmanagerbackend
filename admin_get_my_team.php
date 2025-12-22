<?php
// Autoriser CORS avec credentials
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['user'])) {
    echo json_encode(["success" => false, "message" => "Non connecté"]);
    exit;
}

$id_admin = $_SESSION['user']['id'];

$host = "localhost";
$dbname = "esport_manager";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT * FROM equipe WHERE id_admin = :id_admin LIMIT 1");
    $stmt->bindParam(':id_admin', $id_admin, PDO::PARAM_INT);
    $stmt->execute();
    $equipe = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "equipe" => $equipe ?: null]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erreur serveur"]);
}
