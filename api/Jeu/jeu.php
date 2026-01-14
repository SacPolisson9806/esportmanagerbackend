<?php

require_once "../../classes/cors.php";
require_once "../../classes/database.php";
require_once "../../classes/ControleurJeu.php";

$pdo = Database::connect();
$ctrl = new ControleurJeu($pdo);

header("Content-Type: application/json");

// GET : récupérer jeux
if ($_SERVER["REQUEST_METHOD"] === "GET") {

    if (isset($_GET["id"])) {
        echo json_encode($ctrl->getJeuById($_GET["id"]));
        exit;
    }

    if (isset($_GET["id_equipe"])) {
        echo json_encode($ctrl->getAllJeux($_GET["id_equipe"]));
        exit;
    }
}

// POST : ajouter ou modifier
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $data = $_POST;

    if (!empty($data["id"])) {
        echo json_encode($ctrl->updateJeu($data["id"], $data));
        exit;
    }

    echo json_encode($ctrl->ajouterJeu($data));
    exit;
}

// DELETE : supprimer
if ($_SERVER["REQUEST_METHOD"] === "DELETE") {

    parse_str(file_get_contents("php://input"), $data);

    if (!empty($data["id"])) {
        echo json_encode($ctrl->supprimerJeu($data["id"]));
        exit;
    }
}

echo json_encode(["success" => false, "message" => "Requête invalide"]);
