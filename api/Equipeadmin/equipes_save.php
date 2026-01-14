<?php
require_once __DIR__ . "/../../cors.php";

session_start();

/*
|--------------------------------------------------------------------------
| Vérification du rôle super_admin
| Seul un super_admin peut créer ou modifier une équipe via cette API
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'super_admin') {
    echo json_encode([
        "success" => false,
        "message" => "Accès refusé"
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| Chargement des classes nécessaires
| - Database : connexion PDO centralisée
| - CEquipeAdmin : modèle représentant une équipe
| - ControleurEquipeAdmin : logique métier
|--------------------------------------------------------------------------
*/
require_once __DIR__ . "/../../classes/Database.php";
require_once __DIR__ . "/../../classes/CEquipeAdmin.php";
require_once __DIR__ . "/../../classes/ControleurEquipeAdmin.php";

/*
|--------------------------------------------------------------------------
| Lecture du JSON envoyé par le front
| Le front envoie un JSON contenant les champs de l’équipe
|--------------------------------------------------------------------------
*/
$data = json_decode(file_get_contents("php://input"), true);

/*
|--------------------------------------------------------------------------
| Vérification des champs obligatoires
| Ici seul "nom" est obligatoire pour créer une équipe
|--------------------------------------------------------------------------
*/
if (empty($data['nom'])) {
    echo json_encode([
        "success" => false,
        "message" => "Nom obligatoire"
    ]);
    exit;
}

try {

    /*
    |--------------------------------------------------------------------------
    | Connexion BDD via ta classe Database
    | Cela évite de répéter la configuration PDO partout
    |--------------------------------------------------------------------------
    */
    $pdo = Database::connect();

    /*
    |--------------------------------------------------------------------------
    | Instanciation du contrôleur
    | Toute la logique métier passe par lui (architecture MVC propre)
    |--------------------------------------------------------------------------
    */
    $controleur = new ControleurEquipeAdmin($pdo);

    /*
    |--------------------------------------------------------------------------
    | INSERT ou UPDATE orienté objet
    | saveEquipe() :
    |   - si id_equipe existe → UPDATE
    |   - sinon → INSERT
    |--------------------------------------------------------------------------
    */
    $result = $controleur->saveEquipe($data);

    /*
    |--------------------------------------------------------------------------
    | Réponse JSON envoyée au front
    |--------------------------------------------------------------------------
    */
    echo json_encode($result);
    exit;

} catch (Exception $e) {

    /*
    |--------------------------------------------------------------------------
    | Gestion propre des erreurs serveur
    | On évite d'afficher l'erreur SQL exacte pour des raisons de sécurité
    |--------------------------------------------------------------------------
    */
    echo json_encode([
        "success" => false,
        "message" => "Erreur serveur"
    ]);
    exit;
}
