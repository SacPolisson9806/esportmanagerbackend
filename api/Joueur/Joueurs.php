<?php

require_once "../../classes/cors.php";
require_once "../../classes/Database.php";
require_once "../../classes/ControleurJoueur.php";

$pdo = Database::connect();
$ctrl = new ControleurJoueur($pdo);

header("Content-Type: application/json");

// GET
if ($_SERVER["REQUEST_METHOD"] === "GET") {

    if (isset($_GET["id"])) {
        echo json_encode($ctrl->getJoueurById($_GET["id"]));
        exit;
    }

    if (isset($_GET["id_equipe"])) {
        echo json_encode($ctrl->getAllJoueurs($_GET["id_equipe"]));
        exit;
    }
}

// POST (create or update)
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $data = $_POST;
    $file = $_FILES["photo"] ?? null;

    if (!empty($data["id"])) {
        echo json_encode($ctrl->updateJoueur($data["id"], $data, $file));
        exit;
    }

    echo json_encode($ctrl->ajouterJoueur($data, $file));
    exit;
}

// DELETE
if ($_SERVER["REQUEST_METHOD"] === "DELETE") {

    parse_str(file_get_contents("php://input"), $data);

    if (!empty($data["id"])) {
        echo json_encode($ctrl->supprimerJoueur($data["id"]));
        exit;
    }
}

echo json_encode(["success" => false, "message" => "Requête invalide"]);
