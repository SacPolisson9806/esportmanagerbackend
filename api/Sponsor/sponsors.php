<?php

require_once "../../classes/cors.php";
require_once "../../classes/Database.php";
require_once "../../classes/ControleurSponsor.php";

$pdo = Database::connect();
$ctrl = new ControleurSponsor($pdo);

header("Content-Type: application/json");

// GET
if ($_SERVER["REQUEST_METHOD"] === "GET") {

    if (isset($_GET["id"])) {
        echo json_encode($ctrl->getSponsorById($_GET["id"]));
        exit;
    }

    if (isset($_GET["id_equipe"])) {
        echo json_encode($ctrl->getAllSponsors($_GET["id_equipe"]));
        exit;
    }
}

// POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $data = $_POST;
    $file = $_FILES["logo"] ?? null;

    if (!empty($data["id"])) {
        echo json_encode($ctrl->updateSponsor($data["id"], $data, $file));
        exit;
    }

    echo json_encode($ctrl->ajouterSponsor($data, $file));
    exit;
}

// DELETE
if ($_SERVER["REQUEST_METHOD"] === "DELETE") {

    parse_str(file_get_contents("php://input"), $data);

    if (!empty($data["id"])) {
        echo json_encode($ctrl->supprimerSponsor($data["id"]));
        exit;
    }
}

echo json_encode(["success" => false, "message" => "Requête invalide"]);
