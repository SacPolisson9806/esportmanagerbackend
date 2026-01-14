<?php
require_once "../../classes/Database.php";
require_once "../../classes/ControleurEquipeAdmin.php";
require_once __DIR__ . "/../../cors.php";

session_start();

/*
|--------------------------------------------------------------------------
| Vérification que l'utilisateur est connecté
| Cette API permet à un admin d'équipe de modifier SON équipe
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
| Connexion à la base via ta classe Database
| Cela évite de répéter la configuration PDO partout
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
| Lecture du JSON envoyé par le front
| Le front envoie toutes les données de l'équipe à modifier
|--------------------------------------------------------------------------
*/
$data = json_decode(file_get_contents("php://input"), true);

/*
|--------------------------------------------------------------------------
| Récupération de l'utilisateur connecté
|--------------------------------------------------------------------------
*/
$id_user = $_SESSION["user"]["id_utilisateur"];

/*
|--------------------------------------------------------------------------
| Récupération de l'ID de l'équipe à modifier
|--------------------------------------------------------------------------
*/
$id_equipe = $data["id_equipe"];

/*
|--------------------------------------------------------------------------
| Vérification que l'utilisateur est bien admin de cette équipe
| Sécurité : un admin ne peut modifier QUE son équipe
|--------------------------------------------------------------------------
*/
if (!$ctrl->estAdminEquipe($id_equipe, $id_user)) {
    echo json_encode([
        "success" => false,
        "message" => "Accès refusé"
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Mise à jour de l'équipe via ton contrôleur
| updateEquipe() renvoie true ou false
|--------------------------------------------------------------------------
*/
$ok = $ctrl->updateEquipe($id_equipe, $data);

/*
|--------------------------------------------------------------------------
| Réponse JSON envoyée au front
|--------------------------------------------------------------------------
*/
echo json_encode([
    "success" => $ok,
    "message" => $ok ? "Équipe mise à jour" : "Erreur"
]);
