<?php
require_once __DIR__ . "/../../cors.php";
session_start();

/*
|--------------------------------------------------------------------------
| Vérification que l'utilisateur est connecté
| Cette API renvoie l'équipe dont il est admin
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION["user"])) {
    echo json_encode([
        "success" => false,
        "message" => "Non connecté"
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Chargement des classes nécessaires
| - Database : connexion PDO centralisée
| - ControleurEquipeAdmin : logique métier (MVC)
|--------------------------------------------------------------------------
*/
require_once __DIR__ . "/../../classes/Database.php";
require_once __DIR__ . "/../../classes/ControleurEquipeAdmin.php";

/*
|--------------------------------------------------------------------------
| Connexion à la base via ta classe Database
|--------------------------------------------------------------------------
*/
$pdo = Database::connect();

/*
|--------------------------------------------------------------------------
| Instanciation du contrôleur
|--------------------------------------------------------------------------
*/
$ctrl = new ControleurEquipeAdmin($pdo);

/*
|--------------------------------------------------------------------------
| Récupération de l'ID utilisateur connecté
|--------------------------------------------------------------------------
*/
$id_user = $_SESSION["user"]["id_utilisateur"];

/*
|--------------------------------------------------------------------------
| Récupération de l'équipe dont il est admin
| getEquipeParAdmin() renvoie un objet CEquipeAdmin ou null
|--------------------------------------------------------------------------
*/
$equipe = $ctrl->getEquipeParAdmin($id_user);

/*
|--------------------------------------------------------------------------
| Réponse JSON envoyée au front
|--------------------------------------------------------------------------
*/
echo json_encode([
    "success" => true,
    "equipe" => $equipe
]);
exit;
