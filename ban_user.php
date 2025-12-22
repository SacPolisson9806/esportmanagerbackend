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
$duration = $data['duration'] ?? null; // 1d,3d,7d,30d,permanent,custom
$customDate = $data['customDate'] ?? null; // datetime-local string
$permissions = $data['permissions'] ?? null;

if (!$id || !$duration || !$permissions) {
    echo json_encode(["success" => false, "message" => "Paramètres manquants"]);
    exit;
}

$host = "localhost";
$dbname = "esport_manager";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Calcul de la date de fin de bannissement
    if ($duration === "permanent") {
        $banExpire = null; // ban sans date de fin
    } elseif ($duration === "custom") {
        if (!$customDate) {
            echo json_encode(["success" => false, "message" => "Date personnalisée manquante"]);
            exit;
        }
        // customDate vient du input datetime-local => format "YYYY-MM-DDTHH:MM"
        $banExpire = str_replace("T", " ", $customDate) . ":00";
    } else {
        // Durées standard
        $intervalMap = [
            "1d" => "+1 day",
            "3d" => "+3 days",
            "7d" => "+7 days",
            "30d" => "+30 days"
        ];

        if (!isset($intervalMap[$duration])) {
            echo json_encode(["success" => false, "message" => "Durée invalide"]);
            exit;
        }

        $now = new DateTime("now");
        $now->modify($intervalMap[$duration]);
        $banExpire = $now->format("Y-m-d H:i:s");
    }

    $permissionsJson = json_encode($permissions);

    $stmt = $conn->prepare("
        UPDATE utilisateur
        SET statut = 'banni',
            ban_expire = :ban_expire,
            permissions = :permissions
        WHERE id_utilisateur = :id
    ");

    $stmt->execute([
        ":ban_expire" => $banExpire,
        ":permissions" => $permissionsJson,
        ":id" => $id
    ]);

    echo json_encode(["success" => true]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Erreur serveur"]);
}
