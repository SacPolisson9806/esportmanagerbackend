<?php
// ------------------------------------------------------------
// Active les règles CORS (autorise les requêtes venant du front)
// ------------------------------------------------------------
require_once __DIR__ . "/../../cors.php";

// ------------------------------------------------------------
// Démarre la session pour identifier l'utilisateur connecté
// ------------------------------------------------------------
session_start();

// ------------------------------------------------------------
// Vérification : l'utilisateur doit être connecté
// ------------------------------------------------------------
if (!isset($_SESSION['user'])) {
    echo json_encode(["success" => false, "message" => "Non connecté"]);
    exit;
}

// ID de l'admin connecté
$id_admin = $_SESSION['user']['id_utilisateur'];

// ------------------------------------------------------------
// Chargement des classes nécessaires
// ------------------------------------------------------------
require_once __DIR__ . "/../../classes/Database.php";        // ✔ Ajouté
require_once __DIR__ . "/../../classes/CEquipeAdmin.php";
require_once __DIR__ . "/../../classes/ControleurEquipeAdmin.php";

// ------------------------------------------------------------
// Lecture des données JSON envoyées par le front
// ------------------------------------------------------------
$data = json_decode(file_get_contents("php://input"), true);

// ------------------------------------------------------------
// Vérification des champs obligatoires
// ------------------------------------------------------------
$nom = $data['nom'] ?? null;

if (!$nom) {
    echo json_encode(["success" => false, "message" => "Nom obligatoire"]);
    exit;
}

// ------------------------------------------------------------
// Connexion à la base via ta classe Database
// ------------------------------------------------------------
$pdo = Database::connect();   // ✔ Remplace totalement le PDO brut

// ------------------------------------------------------------
// Instanciation du contrôleur d'équipe
// ------------------------------------------------------------
$controleur = new ControleurEquipeAdmin($pdo);

// ------------------------------------------------------------
// Vérifier si l'admin possède déjà une équipe
// ------------------------------------------------------------
$equipeExistante = $controleur->getEquipeParAdmin($id_admin);

if ($equipeExistante !== null) {
    echo json_encode([
        "success" => false,
        "message" => "Vous avez déjà une équipe"
    ]);
    exit;
}

// ------------------------------------------------------------
// Construction du tableau de données attendu par creerEquipe()
// ------------------------------------------------------------
$dataEquipe = [
    "id_admin"           => $id_admin,
    "nom"                => $data["nom"] ?? null,
    "tag"                => $data["tag"] ?? null,
    "date_creation"      => $data["date_creation"] ?? null,
    "description_courte" => $data["description_courte"] ?? null,
    "description_longue" => $data["description_longue"] ?? null,
    "pays"               => $data["pays"] ?? null,
    "ville"              => $data["ville"] ?? null,
    "site_web"           => $data["site_web"] ?? null,
    "email_general"      => $data["email_general"] ?? null,
    "email_recrutement"  => $data["email_recrutement"] ?? null,
    "telephone"          => $data["telephone"] ?? null,
    "twitter"            => $data["twitter"] ?? null,
    "instagram"          => $data["instagram"] ?? null,
    "twitch"             => $data["twitch"] ?? null,
    "youtube"            => $data["youtube"] ?? null,
    "tiktok"             => $data["tiktok"] ?? null,
    "facebook"           => $data["facebook"] ?? null
];

// ------------------------------------------------------------
// Récupération du fichier logo (upload)
// ------------------------------------------------------------
$file_logo = $_FILES["logo"] ?? null;

// ------------------------------------------------------------
// Création de l'équipe via le contrôleur
// ------------------------------------------------------------
$result = $controleur->creerEquipe($dataEquipe, $file_logo);

// ------------------------------------------------------------
// Réponse envoyée au front
// ------------------------------------------------------------
echo json_encode($result);
exit;
