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

$host = "localhost";
$dbname = "esport_manager";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->query("
        SELECT 
            id_utilisateur,
            pseudo,
            email,
            role,
            statut,
            id_equipe,
            permissions,
            ban_expire
        FROM utilisateur
        ORDER BY id_utilisateur ASC
    ");

    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "users" => $users]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erreur serveur"]);
}
