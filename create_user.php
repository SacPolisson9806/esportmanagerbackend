<?php
// Autoriser CORS avec credentials
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

session_start();

// Vérifier que l'utilisateur connecté est super_admin
if ($_SESSION['role'] !== 'super_admin') {
    echo json_encode(["success" => false, "message" => "Accès interdit"]);
    exit;
}

$host = "localhost"; 
$dbname = "esport_manager";
$username = "root"; 
$password = ""; 

$data = json_decode(file_get_contents("php://input"), true);

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("INSERT INTO utilisateur (pseudo, email, mot_de_passe, role, statut, id_equipe, permissions) 
                            VALUES (:pseudo, :email, :mot_de_passe, :role, :statut, :id_equipe, :permissions)");

    $stmt->execute([
        ":pseudo" => $data["pseudo"],
        ":email" => $data["email"],
        ":mot_de_passe" => password_hash($data["mot_de_passe"], PASSWORD_DEFAULT),
        ":role" => $data["role"],
        ":statut" => "actif",
        ":id_equipe" => $data["id_equipe"] ?? null,
        ":permissions" => json_encode($data["permissions"])
    ]);

    echo json_encode(["success" => true, "message" => "Compte créé avec succès"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erreur serveur"]);
}
?>
