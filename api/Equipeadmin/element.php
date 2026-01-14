<?php
require "../../config.php";
require "../../controllers/ControleurEquipeAdmin.php";

$ctrl = new ControleurEquipeAdmin($db);

$action = $_GET["action"] ?? null;
$type   = $_GET["type"] ?? null;
$id     = $_GET["id"] ?? null;

$data = $_POST;

switch ($action) {

    case "add":
        $ok = $ctrl->addElement($type, $data);
        break;

    case "update":
        $ok = $ctrl->updateElement($type, $id, $data);
        break;

    case "delete":
        $ok = $ctrl->deleteElement($type, $id);
        break;

    default:
        $ok = false;
}

echo json_encode(["success" => $ok]);
