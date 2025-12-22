<?php
// Autoriser CORS avec credentials
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

$host = "localhost"; 
$dbname = "esport_manager";   // ⚠️ Mets le nom de ta base
$username = "root"; 
$password = ""; 

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer tous les joueurs
    $stmt = $conn->query("SELECT id_joueur, pseudo, role, age FROM joueur");
    $joueurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "joueurs" => $joueurs]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erreur serveur"]);
}
?>
