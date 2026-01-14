<?php
require_once __DIR__ . "/../../cors.php";
session_start();

// ------------------------------------------------------------
// Vérification : l'utilisateur doit être connecté
// ------------------------------------------------------------
if (!isset($_SESSION['user']['id_utilisateur'])) {
    echo json_encode([
        "success" => false,
        "message" => "Utilisateur non authentifié"
    ]);
    exit;
}

$idAdmin = $_SESSION['user']['id_utilisateur'];

// ------------------------------------------------------------
// Connexion BDD via ta classe Database
// ------------------------------------------------------------
require_once "../../classes/Database.php";
require_once "../../classes/ControleurEquipeAdmin.php";

$pdo = Database::connect();
$controleur = new ControleurEquipeAdmin($pdo);

// ------------------------------------------------------------
// Helper pour décoder un champ JSON envoyé dans $_POST
// ------------------------------------------------------------
function decodeJsonField($key) {
    if (!isset($_POST[$key])) return [];
    $decoded = json_decode($_POST[$key], true);
    return is_array($decoded) ? $decoded : [];
}

// ------------------------------------------------------------
// Construction du tableau $data attendu par creerEquipeComplete()
// ------------------------------------------------------------
$data = [
    "id_admin"           => $idAdmin,
    "nom"                => $_POST["nom"] ?? null,
    "tag"                => $_POST["tag"] ?? null,
    "date_creation"      => $_POST["date_creation"] ?? null,
    "description_courte" => $_POST["description_courte"] ?? null,
    "description_longue" => $_POST["description_longue"] ?? null,
    "pays"               => $_POST["pays"] ?? null,
    "ville"              => $_POST["ville"] ?? null,
    "site_web"           => $_POST["site_web"] ?? null,
    "email_general"      => $_POST["email_general"] ?? null,
    "email_recrutement"  => $_POST["email_recrutement"] ?? null,
    "telephone"          => $_POST["telephone"] ?? null,
    "twitter"            => $_POST["twitter"] ?? null,
    "instagram"          => $_POST["instagram"] ?? null,
    "twitch"             => $_POST["twitch"] ?? null,
    "youtube"            => $_POST["youtube"] ?? null,
    "tiktok"             => $_POST["tiktok"] ?? null,
    "facebook"           => $_POST["facebook"] ?? null,

    // Champs complexes (JSON)
    "jeux"               => decodeJsonField("jeux"),
    "joueurs"            => decodeJsonField("joueurs"),
    "managers"           => decodeJsonField("managers"),
    "staff"              => decodeJsonField("staff"),
    "sponsors"           => decodeJsonField("sponsors"),
    "palmares"           => decodeJsonField("palmares")
];

// ------------------------------------------------------------
// Appel de la méthode complète du contrôleur
// ------------------------------------------------------------
$result = $controleur->creerEquipeComplete($data, $_FILES);

// ------------------------------------------------------------
// Réponse JSON
// ------------------------------------------------------------
echo json_encode($result);
exit;
