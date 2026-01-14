<?php
require_once __DIR__ . "/../../cors.php";

/*
|--------------------------------------------------------------------------
| Chargement des classes nécessaires
| - Database : connexion PDO centralisée
| - CEquipeAdmin : modèle représentant une équipe
| - ControleurEquipeAdmin : logique métier (MVC)
|--------------------------------------------------------------------------
*/
require_once __DIR__ . "/../../classes/Database.php";
require_once __DIR__ . "/../../classes/CEquipeAdmin.php";
require_once __DIR__ . "/../../classes/ControleurEquipeAdmin.php";

try {

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
    | Toute la logique métier passe par lui (architecture MVC propre)
    |--------------------------------------------------------------------------
    */
    $controleur = new ControleurEquipeAdmin($pdo);

    /*
    |--------------------------------------------------------------------------
    | Récupération orientée objet
    | getToutesLesEquipes() renvoie un tableau d'objets CEquipeAdmin
    |--------------------------------------------------------------------------
    */
    $equipes = $controleur->getToutesLesEquipes();

    /*
    |--------------------------------------------------------------------------
    | Conversion objets → tableaux
    | get_object_vars() transforme chaque objet en tableau pour JSON
    |--------------------------------------------------------------------------
    */
    $equipesArray = array_map(fn($e) => get_object_vars($e), $equipes);

    /*
    |--------------------------------------------------------------------------
    | Réponse JSON envoyée au front
    |--------------------------------------------------------------------------
    */
    echo json_encode([
        "success" => true,
        "equipes" => $equipesArray
    ]);
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
