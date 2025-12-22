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

$data = json_decode(file_get_contents("php://input"), true);

$nom           = $data['nom'] ?? null;
$date_creation = $data['date_creation'] ?? null;
$fondateur     = $data['fondateur'] ?? null;
$logo_actuel   = $data['logo_actuel'] ?? null;
$logo_ancien   = $data['logo_ancien'] ?? null;
$jeux_pratique = $data['jeux_pratique'] ?? null;

if (!$nom) {
    echo json_encode(["success" => false, "message" => "Nom obligatoire"]);
    exit;
}

$host = "localhost";
$dbname = "esport_manager";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier si admin a déjà une équipe
    $check = $conn->prepare("SELECT id_equipe FROM equipe WHERE id_admin = :id_admin");
    $check->bindParam(':id_admin', $id_admin);
    $check->execute();

    if ($check->fetch()) {
        echo json_encode(["success" => false, "message" => "Vous avez déjà une équipe"]);
        exit;
    }

    // Création
    $stmt = $conn->prepare("
        INSERT INTO equipe (nom, date_creation, fondateur, logo_actuel, logo_ancien, jeux_pratique, id_admin)
        VALUES (:nom, :date_creation, :fondateur, :logo_actuel, :logo_ancien, :jeux_pratique, :id_admin)
    ");

    $stmt->bindParam(':nom', $nom);
    $stmt->bindParam(':date_creation', $date_creation);
    $stmt->bindParam(':fondateur', $fondateur);
    $stmt->bindParam(':logo_actuel', $logo_actuel);
    $stmt->bindParam(':logo_ancien', $logo_ancien);
    $stmt->bindParam(':jeux_pratique', $jeux_pratique);
    $stmt->bindParam(':id_admin', $id_admin);

    $stmt->execute();

    $id_equipe = $conn->lastInsertId();

    echo json_encode([
        "success" => true,
        "message" => "Équipe créée",
        "equipe" => [
            "id_equipe" => $id_equipe,
            "nom" => $nom,
            "date_creation" => $date_creation,
            "fondateur" => $fondateur,
            "logo_actuel" => $logo_actuel,
            "logo_ancien" => $logo_ancien,
            "jeux_pratique" => $jeux_pratique,
            "id_admin" => $id_admin
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erreur serveur"]);
}
