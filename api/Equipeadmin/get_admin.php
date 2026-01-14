<?php
require_once __DIR__ . "/../../cors.php";

session_start();

/*
|--------------------------------------------------------------------------
| Chargement des classes nécessaires
|--------------------------------------------------------------------------
*/
require_once __DIR__ . "/../../classes/Database.php";
require_once __DIR__ . "/../../classes/ControleurEquipeAdmin.php";

/*
|--------------------------------------------------------------------------
| Connexion BDD via Database
|--------------------------------------------------------------------------
*/
$pdo = Database::connect();
$ctrl = new ControleurEquipeAdmin($pdo);

/*
|--------------------------------------------------------------------------
| Vérification du paramètre GET
|--------------------------------------------------------------------------
*/
$id_equipe = $_GET["id_equipe"] ?? null;

if (!$id_equipe) {
    echo json_encode([
        "success" => false,
        "message" => "ID équipe manquant"
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Récupération des admins
|--------------------------------------------------------------------------
*/
$admins = $ctrl->getAdminsEquipe($id_equipe);

/*
|--------------------------------------------------------------------------
| Réponse JSON
|--------------------------------------------------------------------------
*/
echo json_encode([
    "success" => true,
    "admins" => $admins
]);
exit;
