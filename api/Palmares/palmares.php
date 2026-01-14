<?php

require_once "../../classes/cors.php";
require_once "../../classes/Database.php";
require_once "../../classes/ControleurPalmares.php";

$pdo = Database::connect();
$ctrl = new ControleurPalmares($pdo);

header("Content-Type: application/json");

// GET
if ($_SERVER["REQUEST_METHOD"] === "GET") {

    if (isset($_GET["id"])) {
        echo json_encode($ctrl->getPalmaresById($_GET["id"]));
        exit;
    }

    if (isset($_GET["id_equipe"])) {
        echo json_encode($ctrl->getAllPalmares($_GET["id_equipe"]));
        exit;
    }
}

// POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $data = $_POST;

    if (!empty($data["id"])) {
        echo json_encode($ctrl->updatePalmares($data["id"], $data));
        exit;
    }

    echo json_encode($ctrl->ajouterPalmares($data));
    exit;
}

// DELETE
if ($_SERVER["REQUEST_METHOD"] === "DELETE") {

    parse_str(file_get_contents("php://input"), $data);

    if (!empty($data["id"])) {
        echo json_encode($ctrl->supprimerPalmares($data["id"]));
        exit;
    }
}

echo json_encode(["success" => false, "message" => "Requête invalide"]);
