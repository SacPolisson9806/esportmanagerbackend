<?php
require_once __DIR__ . "/../../cors.php";

/*
|--------------------------------------------------------------------------
| Vérification du paramètre GET
| L’API doit recevoir ?id_equipe=XX
|--------------------------------------------------------------------------
*/
if (!isset($_GET['id_equipe'])) {
    echo json_encode([
        "success" => false,
        "message" => "ID équipe manquant"
    ]);
    exit;
}

$idEquipe = intval($_GET['id_equipe']);

session_start();

/*
|--------------------------------------------------------------------------
| Vérification que l'utilisateur est connecté
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['user']['id_utilisateur'])) {
    echo json_encode([
        "success" => false,
        "message" => "Utilisateur non authentifié"
    ]);
    exit;
}

$idAdmin = $_SESSION['user']['id_utilisateur'];

/*
|--------------------------------------------------------------------------
| Chargement des classes nécessaires
| - Database : connexion PDO centralisée
| - CEquipeAdmin : modèle représentant une équipe
| - ControleurEquipeAdmin : logique métier
|--------------------------------------------------------------------------
*/
require_once __DIR__ . "/../../classes/Database.php";
$pdo = Database::connect(); // ✔ Connexion centralisée

require_once __DIR__ . "/../../classes/CEquipeAdmin.php";
require_once __DIR__ . "/../../classes/ControleurEquipeAdmin.php";

/*
|--------------------------------------------------------------------------
| Instanciation du contrôleur
|--------------------------------------------------------------------------
*/
$controleur = new ControleurEquipeAdmin($pdo);

/*
|--------------------------------------------------------------------------
| Vérification que l'utilisateur est bien admin de cette équipe
| (Sécurité : un admin ne peut voir QUE son équipe)
|--------------------------------------------------------------------------
*/
if (!$controleur->estAdminEquipe($idEquipe, $idAdmin)) {
    echo json_encode([
        "success" => false,
        "message" => "Accès refusé"
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| 1) Récupération de l'équipe via ton modèle orienté objet
|--------------------------------------------------------------------------
*/
$equipe = $controleur->getEquipeById($idEquipe);

if (!$equipe) {
    echo json_encode([
        "success" => false,
        "message" => "Équipe introuvable"
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| 2) Récupération des données liées (jeux, joueurs, staff, etc.)
| Petite fonction utilitaire pour éviter de répéter du code
|--------------------------------------------------------------------------
*/
function fetchAll($pdo, $sql, $params) {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$jeux = fetchAll($pdo, "SELECT * FROM equipe_jeux WHERE id_equipe = :id", [":id" => $idEquipe]);
$joueurs = fetchAll($pdo, "SELECT * FROM equipe_joueurs WHERE id_equipe = :id", [":id" => $idEquipe]);
$managers = fetchAll($pdo, "SELECT * FROM equipe_managers WHERE id_equipe = :id", [":id" => $idEquipe]);
$staff = fetchAll($pdo, "SELECT * FROM equipe_staff WHERE id_equipe = :id", [":id" => $idEquipe]);
$sponsors = fetchAll($pdo, "SELECT * FROM equipe_sponsors WHERE id_equipe = :id", [":id" => $idEquipe]);
$palmares = fetchAll($pdo, "SELECT * FROM equipe_palmares WHERE id_equipe = :id ORDER BY date DESC", [":id" => $idEquipe]);

/*
|--------------------------------------------------------------------------
| 3) Réponse finale envoyée au front
| - "equipe" est un objet CEquipeAdmin
| - le reste sont des tableaux associatifs
|--------------------------------------------------------------------------
*/
echo json_encode([
    "success" => true,
    "equipe" => $equipe,
    "jeux" => $jeux,
    "joueurs" => $joueurs,
    "managers" => $managers,
    "staff" => $staff,
    "sponsors" => $sponsors,
    "palmares" => $palmares
]);
exit;
