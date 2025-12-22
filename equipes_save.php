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

$data = json_decode(file_get_contents("php://input"), true);

$nom           = $data['nom'] ?? null;
$date_creation = $data['date_creation'] ?? null;
$fondateur     = $data['fondateur'] ?? null;
$logo_actuel   = $data['logo_actuel'] ?? null;
$logo_ancien   = $data['logo_ancien'] ?? null;
$jeux_pratique = $data['jeux_pratique'] ?? null;
$id_equipe     = $data['id_equipe'] ?? null;

if (!$nom) {
    echo json_encode(["success" => false, "message" => "Nom obligatoire"]);
    exit;
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($id_equipe) {
        // UPDATE
        $stmt = $conn->prepare("
            UPDATE equipe
            SET nom = :nom,
                date_creation = :date_creation,
                fondateur = :fondateur,
                logo_actuel = :logo_actuel,
                logo_ancien = :logo_ancien,
                jeux_pratique = :jeux_pratique
            WHERE id_equipe = :id_equipe
        ");
        $stmt->bindParam(':id_equipe', $id_equipe, PDO::PARAM_INT);
    } else {
        // INSERT
        $stmt = $conn->prepare("
            INSERT INTO equipe (nom, date_creation, fondateur, logo_actuel, logo_ancien, jeux_pratique)
            VALUES (:nom, :date_creation, :fondateur, :logo_actuel, :logo_ancien, :jeux_pratique)
        ");
    }

    $stmt->bindParam(':nom', $nom);
    $stmt->bindParam(':date_creation', $date_creation);
    $stmt->bindParam(':fondateur', $fondateur);
    $stmt->bindParam(':logo_actuel', $logo_actuel);
    $stmt->bindParam(':logo_ancien', $logo_ancien);
    $stmt->bindParam(':jeux_pratique', $jeux_pratique);

    $stmt->execute();

    if (!$id_equipe) {
        $id_equipe = $conn->lastInsertId();
    }

    echo json_encode(["success" => true, "id_equipe" => $id_equipe]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erreur serveur"]);
}
