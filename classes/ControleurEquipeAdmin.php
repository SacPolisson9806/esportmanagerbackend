<?php

require_once "CEquipeAdmin.php";

class ControleurEquipeAdmin {

    private $pdo; // Connexion PDO

    // ------------------------------------------------------------
    // Constructeur : injection de dépendance (PDO)
    // ------------------------------------------------------------
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /* ============================================================
        Vérifier si un utilisateur est admin d'une équipe
       ============================================================ */
    public function estAdminEquipe($id_equipe, $id_utilisateur) {

        $stmt = $this->pdo->prepare("
            SELECT id 
            FROM equipe_admins 
            WHERE id_equipe = :id_equipe AND id_utilisateur = :id_utilisateur
        ");

        $stmt->execute([
            ":id_equipe" => $id_equipe,
            ":id_utilisateur" => $id_utilisateur
        ]);

        return $stmt->fetch() ? true : false;
    }

    /* ============================================================
        Récupérer l'équipe d'un admin
       ============================================================ */
    public function getEquipeParAdmin($id_utilisateur) {

        $stmt = $this->pdo->prepare("
            SELECT e.*
            FROM equipe e
            JOIN equipe_admins ea ON ea.id_equipe = e.id_equipe
            WHERE ea.id_utilisateur = :id_utilisateur
            LIMIT 1
        ");

        $stmt->execute([":id_utilisateur" => $id_utilisateur]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? new CEquipeAdmin($data) : null;
    }

    /* ============================================================
        Récupérer une équipe par ID
       ============================================================ */
    public function getEquipeById($id_equipe) {

        $stmt = $this->pdo->prepare("
            SELECT *
            FROM equipe
            WHERE id_equipe = :id_equipe
            LIMIT 1
        ");

        $stmt->execute([":id_equipe" => $id_equipe]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new CEquipeAdmin($row) : null;
    }

    /* ============================================================
        Récupérer toutes les équipes
       ============================================================ */
    public function getToutesLesEquipes() {

        $stmt = $this->pdo->query("
            SELECT *
            FROM equipe
            ORDER BY id_equipe DESC
        ");

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => new CEquipeAdmin($row), $rows);
    }

    /* ============================================================
        UPLOAD LOGO PRINCIPAL
       ============================================================ */
    private function uploadLogo($file) {

        if (!isset($file) || $file["error"] !== UPLOAD_ERR_OK) {
            return null;
        }

        $folder = __DIR__ . "/../../uploads/equipes/";
        if (!is_dir($folder)) mkdir($folder, 0777, true);

        $ext = pathinfo($file["name"], PATHINFO_EXTENSION);
        $filename = "logo_" . time() . "_" . rand(1000,9999) . "." . $ext;

        move_uploaded_file($file["tmp_name"], $folder . $filename);

        return "uploads/equipes/" . $filename;
    }

    /* ============================================================
        UPLOAD PHOTO (joueur, manager, staff, sponsor)
       ============================================================ */
    private function uploadPhoto($file) {

        if (!isset($file) || $file["error"] !== UPLOAD_ERR_OK) {
            return null;
        }

        $folder = __DIR__ . "/../../uploads/equipes/photos/";
        if (!is_dir($folder)) mkdir($folder, 0777, true);

        $ext = pathinfo($file["name"], PATHINFO_EXTENSION);
        $filename = "photo_" . time() . "_" . rand(1000,9999) . "." . $ext;

        move_uploaded_file($file["tmp_name"], $folder . $filename);

        return "uploads/equipes/photos/" . $filename;
    }

    /* ============================================================
        1) CRÉER L'ÉQUIPE (données principales)
       ============================================================ */
    public function creerEquipe(array $data, $file_logo) {

        $logoPath = $this->uploadLogo($file_logo);

        $stmt = $this->pdo->prepare("
            INSERT INTO equipe (
                id_admin, nom, tag, date_creation, description_courte, description_longue,
                pays, ville, site_web, email_general, email_recrutement, telephone,
                logo, twitter, instagram, twitch, youtube, tiktok, facebook
            )
            VALUES (
                :id_admin, :nom, :tag, :date_creation, :description_courte, :description_longue,
                :pays, :ville, :site_web, :email_general, :email_recrutement, :telephone,
                :logo, :twitter, :instagram, :twitch, :youtube, :tiktok, :facebook
            )
        ");

        $stmt->execute([
            ":id_admin"          => $data["id_admin"],
            ":nom"               => $data["nom"],
            ":tag"               => $data["tag"],
            ":date_creation"     => $data["date_creation"],
            ":description_courte"=> $data["description_courte"],
            ":description_longue"=> $data["description_longue"],
            ":pays"              => $data["pays"],
            ":ville"             => $data["ville"],
            ":site_web"          => $data["site_web"],
            ":email_general"     => $data["email_general"],
            ":email_recrutement" => $data["email_recrutement"],
            ":telephone"         => $data["telephone"],
            ":logo"              => $logoPath,
            ":twitter"           => $data["twitter"],
            ":instagram"         => $data["instagram"],
            ":twitch"            => $data["twitch"],
            ":youtube"           => $data["youtube"],
            ":tiktok"            => $data["tiktok"],
            ":facebook"          => $data["facebook"]
        ]);

        return $this->pdo->lastInsertId();
    }

    /* ============================================================
        2) AJOUTER LES JEUX
       ============================================================ */
    public function ajouterJeux($idEquipe, array $jeux) {

        $stmt = $this->pdo->prepare("
            INSERT INTO equipe_jeux (id_equipe, nom, rang, division, objectifs)
            VALUES (:id_equipe, :nom, :rang, :division, :objectifs)
        ");

        foreach ($jeux as $jeu) {
            if (empty($jeu["nom"])) continue;

            $stmt->execute([
                ":id_equipe" => $idEquipe,
                ":nom"       => $jeu["nom"],
                ":rang"      => $jeu["rang"] ?? null,
                ":division"  => $jeu["division"] ?? null,
                ":objectifs" => $jeu["objectifs"] ?? null
            ]);
        }
    }

    /* ============================================================
        3) AJOUTER LES JOUEURS
       ============================================================ */
    public function ajouterJoueurs($idEquipe, array $joueurs, array $files) {

        $stmt = $this->pdo->prepare("
            INSERT INTO equipe_joueurs (
                id_equipe, nom, pseudo, age, nationalite, jeu, role,
                experience, contrat, duree_contrat, date_arrivee,
                anciennes_equipes, photo, twitter, instagram, twitch,
                youtube, tiktok, facebook
            )
            VALUES (
                :id_equipe, :nom, :pseudo, :age, :nationalite, :jeu, :role,
                :experience, :contrat, :duree_contrat, :date_arrivee,
                :anciennes_equipes, :photo, :twitter, :instagram, :twitch,
                :youtube, :tiktok, :facebook
            )
        ");

        foreach ($joueurs as $index => $j) {

            if (empty($j["nom"]) && empty($j["pseudo"])) continue;

            $photoField = "joueur_photo_" . $index;
            $photoPath = isset($files[$photoField]) ? $this->uploadPhoto($files[$photoField]) : null;

            $stmt->execute([
                ":id_equipe"        => $idEquipe,
                ":nom"              => $j["nom"] ?? "",
                ":pseudo"           => $j["pseudo"] ?? "",
                ":age"              => $j["age"] ?? null,
                ":nationalite"      => $j["nationalite"] ?? null,
                ":jeu"              => $j["jeu"] ?? null,
                ":role"             => $j["role"] ?? null,
                ":experience"       => $j["experience"] ?? null,
                ":contrat"          => $j["contrat"] ?? null,
                ":duree_contrat"    => $j["duree_contrat"] ?? null,
                ":date_arrivee"     => $j["date_arrivee"] ?? null,
                ":anciennes_equipes"=> $j["anciennes_equipes"] ?? null,
                ":photo"            => $photoPath,
                ":twitter"          => $j["twitter"] ?? null,
                ":instagram"        => $j["instagram"] ?? null,
                ":twitch"           => $j["twitch"] ?? null,
                ":youtube"          => $j["youtube"] ?? null,
                ":tiktok"           => $j["tiktok"] ?? null,
                ":facebook"         => $j["facebook"] ?? null
            ]);
        }
    }

    /* ============================================================
        4) AJOUTER LES MANAGERS
       ============================================================ */
    public function ajouterManagers($idEquipe, array $managers, array $files) {

        $stmt = $this->pdo->prepare("
            INSERT INTO equipe_managers (
                id_equipe, nom, role, age, jeux_geres, photo,
                twitter, instagram, twitch, youtube, tiktok, facebook
            )
            VALUES (
                :id_equipe, :nom, :role, :age, :jeux_geres, :photo,
                :twitter, :instagram, :twitch, :youtube, :tiktok, :facebook
            )
        ");

        foreach ($managers as $index => $m) {

            if (empty($m["nom"])) continue;

            $photoField = "manager_photo_" . $index;
            $photoPath = isset($files[$photoField]) ? $this->uploadPhoto($files[$photoField]) : null;

            $stmt->execute([
                ":id_equipe" => $idEquipe,
                ":nom"       => $m["nom"],
                ":role"      => $m["role"] ?? null,
                ":age"       => $m["age"] ?? null,
                ":jeux_geres"=> $m["jeux_geres"] ?? null,
                ":photo"     => $photoPath,
                ":twitter"   => $m["twitter"] ?? null,
                ":instagram" => $m["instagram"] ?? null,
                ":twitch"    => $m["twitch"] ?? null,
                ":youtube"   => $m["youtube"] ?? null,
                ":tiktok"    => $m["tiktok"] ?? null,
                ":facebook"  => $m["facebook"] ?? null
            ]);
        }
    }

    /* ============================================================
        5) AJOUTER LE STAFF
       ============================================================ */
    public function ajouterStaff($idEquipe, array $staff, array $files) {

        $stmt = $this->pdo->prepare("
            INSERT INTO equipe_staff (
                id_equipe, nom, role, age, jeux_geres, photo,
                twitter, instagram, twitch, youtube, tiktok, facebook
            )
            VALUES (
                :id_equipe, :nom, :role, :age, :jeux_geres, :photo,
                :twitter, :instagram, :twitch, :youtube, :tiktok, :facebook
            )
        ");

        foreach ($staff as $index => $s) {

            if (empty($s["nom"])) continue;

            $photoField = "staff_photo_" . $index;
            $photoPath = isset($files[$photoField]) ? $this->uploadPhoto($files[$photoField]) : null;

            $stmt->execute([
                ":id_equipe" => $idEquipe,
                ":nom"       => $s["nom"],
                ":role"      => $s["role"] ?? null,
                ":age"       => $s["age"] ?? null,
                ":jeux_geres"=> $s["jeux_geres"] ?? null,
                ":photo"     => $photoPath,
                ":twitter"   => $s["twitter"] ?? null,
                ":instagram" => $s["instagram"] ?? null,
                ":twitch"    => $s["twitch"] ?? null,
                ":youtube"   => $s["youtube"] ?? null,
                ":tiktok"    => $s["tiktok"] ?? null,
                ":facebook"  => $s["facebook"] ?? null
            ]);
        }
    }

    /* ============================================================
        6) AJOUTER LES SPONSORS
       ============================================================ */
    public function ajouterSponsors($idEquipe, array $sponsors, array $files) {

        $stmt = $this->pdo->prepare("
            INSERT INTO equipe_sponsors (
                id_equipe, nom, type, duree, lien, logo,
                twitter, instagram, youtube
            )
            VALUES (
                :id_equipe, :nom, :type, :duree, :lien, :logo,
                :twitter, :instagram, :youtube
            )
        ");

        foreach ($sponsors as $index => $s) {

            if (empty($s["nom"])) continue;

            $logoField = "sponsor_logo_" . $index;
            $logoPath = isset($files[$logoField]) ? $this->uploadPhoto($files[$logoField]) : null;

            $stmt->execute([
                ":id_equipe" => $idEquipe,
                ":nom"       => $s["nom"],
                ":type"      => $s["type"] ?? null,
                ":duree"     => $s["duree"] ?? null,
                ":lien"      => $s["lien"] ?? null,
                ":logo"      => $logoPath,
                ":twitter"   => $s["twitter"] ?? null,
                ":instagram" => $s["instagram"] ?? null,
                ":youtube"   => $s["youtube"] ?? null
            ]);
        }
    }

    /* ============================================================
        7) AJOUTER LE PALMARÈS
       ============================================================ */
    public function ajouterPalmares($idEquipe, array $palmares) {

        $stmt = $this->pdo->prepare("
            INSERT INTO equipe_palmares (
                id_equipe, tournoi, date, resultat, recompense
            )
            VALUES (
                :id_equipe, :tournoi, :date, :resultat, :recompense
            )
        ");

        foreach ($palmares as $p) {

            if (empty($p["tournoi"])) continue;

            $stmt->execute([
                ":id_equipe" => $idEquipe,
                ":tournoi"   => $p["tournoi"],
                ":date"      => $p["date"] ?? null,
                ":resultat"  => $p["resultat"] ?? null,
                ":recompense"=> $p["recompense"] ?? null
            ]);
        }
    }

    /* ============================================================
        8) MÉTHODE GLOBALE : CRÉER UNE ÉQUIPE COMPLÈTE
       ============================================================ */
    public function creerEquipeComplete(array $data, array $files) {

        try {
            $this->pdo->beginTransaction();

            // 1) Création équipe
            $idEquipe = $this->creerEquipe($data, $files["logo"] ?? null);

            // 2) Jeux
            if (!empty($data["jeux"])) {
                $this->ajouterJeux($idEquipe, $data["jeux"]);
            }

            // 3) Joueurs
            if (!empty($data["joueurs"])) {
                $this->ajouterJoueurs($idEquipe, $data["joueurs"], $files);
            }

            // 4) Managers
            if (!empty($data["managers"])) {
                $this->ajouterManagers($idEquipe, $data["managers"], $files);
            }

            // 5) Staff
            if (!empty($data["staff"])) {
                $this->ajouterStaff($idEquipe, $data["staff"], $files);
            }

            // 6) Sponsors
            if (!empty($data["sponsors"])) {
                $this->ajouterSponsors($idEquipe, $data["sponsors"], $files);
            }

            // 7) Palmarès
            if (!empty($data["palmares"])) {
                $this->ajouterPalmares($idEquipe, $data["palmares"]);
            }

            $this->pdo->commit();

            return [
                "success" => true,
                "message" => "Équipe créée avec succès",
                "id_equipe" => $idEquipe
            ];

        } catch (Exception $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            return [
                "success" => false,
                "message" => $e->getMessage()
            ];
        }
    }
        
    /* ============================================================
        Supprimer une équipe (hérité de ton ancienne version)
       ============================================================ */
    public function supprimerEquipe($id_equipe) {

        // Supprimer les relations admin → équipe
        $stmt = $this->pdo->prepare("DELETE FROM equipe_admins WHERE id_equipe = :id");
        $stmt->execute([":id" => $id_equipe]);

        // Supprimer les jeux
        $stmt = $this->pdo->prepare("DELETE FROM equipe_jeux WHERE id_equipe = :id");
        $stmt->execute([":id" => $id_equipe]);

        // Supprimer les joueurs
        $stmt = $this->pdo->prepare("DELETE FROM equipe_joueurs WHERE id_equipe = :id");
        $stmt->execute([":id" => $id_equipe]);

        // Supprimer les managers
        $stmt = $this->pdo->prepare("DELETE FROM equipe_managers WHERE id_equipe = :id");
        $stmt->execute([":id" => $id_equipe]);

        // Supprimer le staff
        $stmt = $this->pdo->prepare("DELETE FROM equipe_staff WHERE id_equipe = :id");
        $stmt->execute([":id" => $id_equipe]);

        // Supprimer les sponsors
        $stmt = $this->pdo->prepare("DELETE FROM equipe_sponsors WHERE id_equipe = :id");
        $stmt->execute([":id" => $id_equipe]);

        // Supprimer le palmarès
        $stmt = $this->pdo->prepare("DELETE FROM equipe_palmares WHERE id_equipe = :id");
        $stmt->execute([":id" => $id_equipe]);

        // Supprimer l'équipe elle-même
        $stmt = $this->pdo->prepare("DELETE FROM equipe WHERE id_equipe = :id");
        $stmt->execute([":id" => $id_equipe]);

        return [
            "success" => true,
            "message" => "Équipe supprimée avec succès"
        ];
    }
    /* ============================================================
    Créer OU mettre à jour une équipe (super_admin)
   ============================================================ */
public function saveEquipe(array $data)
{
    // Si id_equipe existe → UPDATE
    if (!empty($data["id_equipe"])) {
        $stmt = $this->pdo->prepare("
            UPDATE equipe SET
                nom = :nom,
                tag = :tag,
                date_creation = :date_creation,
                description_courte = :description_courte,
                description_longue = :description_longue,
                pays = :pays,
                ville = :ville,
                site_web = :site_web,
                email_general = :email_general,
                email_recrutement = :email_recrutement,
                telephone = :telephone,
                twitter = :twitter,
                instagram = :instagram,
                twitch = :twitch,
                youtube = :youtube,
                tiktok = :tiktok,
                facebook = :facebook
            WHERE id_equipe = :id_equipe
        ");

        $stmt->execute([
            ":nom"               => $data["nom"],
            ":tag"               => $data["tag"] ?? null,
            ":date_creation"     => $data["date_creation"] ?? null,
            ":description_courte"=> $data["description_courte"] ?? null,
            ":description_longue"=> $data["description_longue"] ?? null,
            ":pays"              => $data["pays"] ?? null,
            ":ville"             => $data["ville"] ?? null,
            ":site_web"          => $data["site_web"] ?? null,
            ":email_general"     => $data["email_general"] ?? null,
            ":email_recrutement" => $data["email_recrutement"] ?? null,
            ":telephone"         => $data["telephone"] ?? null,
            ":twitter"           => $data["twitter"] ?? null,
            ":instagram"         => $data["instagram"] ?? null,
            ":twitch"            => $data["twitch"] ?? null,
            ":youtube"           => $data["youtube"] ?? null,
            ":tiktok"            => $data["tiktok"] ?? null,
            ":facebook"          => $data["facebook"] ?? null,
            ":id_equipe"         => $data["id_equipe"]
        ]);

        return [
            "success" => true,
            "message" => "Équipe mise à jour"
        ];
    }

    // Sinon → INSERT
    $stmt = $this->pdo->prepare("
        INSERT INTO equipe (
            id_admin, nom, tag, date_creation, description_courte, description_longue,
            pays, ville, site_web, email_general, email_recrutement, telephone,
            twitter, instagram, twitch, youtube, tiktok, facebook
        )
        VALUES (
            :id_admin, :nom, :tag, :date_creation, :description_courte, :description_longue,
            :pays, :ville, :site_web, :email_general, :email_recrutement, :telephone,
            :twitter, :instagram, :twitch, :youtube, :tiktok, :facebook
        )
    ");

    $stmt->execute([
        ":id_admin"          => $data["id_admin"] ?? null,
        ":nom"               => $data["nom"],
        ":tag"               => $data["tag"] ?? null,
        ":date_creation"     => $data["date_creation"] ?? null,
        ":description_courte"=> $data["description_courte"] ?? null,
        ":description_longue"=> $data["description_longue"] ?? null,
        ":pays"              => $data["pays"] ?? null,
        ":ville"             => $data["ville"] ?? null,
        ":site_web"          => $data["site_web"] ?? null,
        ":email_general"     => $data["email_general"] ?? null,
        ":email_recrutement" => $data["email_recrutement"] ?? null,
        ":telephone"         => $data["telephone"] ?? null,
        ":twitter"           => $data["twitter"] ?? null,
        ":instagram"         => $data["instagram"] ?? null,
        ":twitch"            => $data["twitch"] ?? null,
        ":youtube"           => $data["youtube"] ?? null,
        ":tiktok"            => $data["tiktok"] ?? null,
        ":facebook"          => $data["facebook"] ?? null
    ]);

    return [
        "success" => true,
        "message" => "Équipe créée",
        "id_equipe" => $this->pdo->lastInsertId()
    ];
  }
  /* ============================================================
    Récupérer les admins d'une équipe
   ============================================================ */
public function getAdminsEquipe($id_equipe)
{
    $stmt = $this->pdo->prepare("
        SELECT 
            u.id_utilisateur,
            u.nom,
            u.prenom,
            u.email,
            ea.role
        FROM equipe_admins ea
        JOIN utilisateurs u ON u.id_utilisateur = ea.id_utilisateur
        WHERE ea.id_equipe = :id_equipe
    ");

    $stmt->execute([":id_equipe" => $id_equipe]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
/* ============================================================
    Mettre à jour une équipe (admin)
   ============================================================ */
public function updateEquipe(int $id_equipe, array $data): bool
{
    $stmt = $this->pdo->prepare("
        UPDATE equipe SET
            nom = :nom,
            tag = :tag,
            date_creation = :date_creation,
            description_courte = :description_courte,
            description_longue = :description_longue,
            pays = :pays,
            ville = :ville,
            site_web = :site_web,
            email_general = :email_general,
            email_recrutement = :email_recrutement,
            telephone = :telephone,
            twitter = :twitter,
            instagram = :instagram,
            twitch = :twitch,
            youtube = :youtube,
            tiktok = :tiktok,
            facebook = :facebook
        WHERE id_equipe = :id_equipe
    ");

    return $stmt->execute([
        ":nom"               => $data["nom"] ?? null,
        ":tag"               => $data["tag"] ?? null,
        ":date_creation"     => $data["date_creation"] ?? null,
        ":description_courte"=> $data["description_courte"] ?? null,
        ":description_longue"=> $data["description_longue"] ?? null,
        ":pays"              => $data["pays"] ?? null,
        ":ville"             => $data["ville"] ?? null,
        ":site_web"          => $data["site_web"] ?? null,
        ":email_general"     => $data["email_general"] ?? null,
        ":email_recrutement" => $data["email_recrutement"] ?? null,
        ":telephone"         => $data["telephone"] ?? null,
        ":twitter"           => $data["twitter"] ?? null,
        ":instagram"         => $data["instagram"] ?? null,
        ":twitch"            => $data["twitch"] ?? null,
        ":youtube"           => $data["youtube"] ?? null,
        ":tiktok"            => $data["tiktok"] ?? null,
        ":facebook"          => $data["facebook"] ?? null,
        ":id_equipe"         => $id_equipe
    ]);
  }
  /* ============================================================
   AJOUTER UN ÉLÉMENT (générique)
   ============================================================ */
public function addElement(string $type, array $data): bool {

    switch ($type) {

        case "jeu":
            $sql = "INSERT INTO equipe_jeux (id_equipe, nom, rang, division, objectifs)
                    VALUES (:id_equipe, :nom, :rang, :division, :objectifs)";
            break;

        case "joueur":
            $sql = "INSERT INTO equipe_joueurs (id_equipe, nom, pseudo, age, nationalite, jeu, role, experience,
                    contrat, duree_contrat, date_arrivee, anciennes_equipes, photo, twitter, instagram, twitch,
                    youtube, tiktok, facebook)
                    VALUES (:id_equipe, :nom, :pseudo, :age, :nationalite, :jeu, :role, :experience, :contrat,
                    :duree_contrat, :date_arrivee, :anciennes_equipes, :photo, :twitter, :instagram, :twitch,
                    :youtube, :tiktok, :facebook)";
            break;

        case "manager":
            $sql = "INSERT INTO equipe_managers (id_equipe, nom, role, age, jeux_geres, photo, twitter, instagram,
                    twitch, youtube, tiktok, facebook)
                    VALUES (:id_equipe, :nom, :role, :age, :jeux_geres, :photo, :twitter, :instagram, :twitch,
                    :youtube, :tiktok, :facebook)";
            break;

        case "staff":
            $sql = "INSERT INTO equipe_staff (id_equipe, nom, role, jeux_geres, photo, twitter, instagram, twitch,
                    youtube, tiktok, facebook)
                    VALUES (:id_equipe, :nom, :role, :jeux_geres, :photo, :twitter, :instagram, :twitch,
                    :youtube, :tiktok, :facebook)";
            break;

        case "sponsor":
            $sql = "INSERT INTO equipe_sponsors (id_equipe, nom, type, duree, lien, logo, twitter, instagram, youtube)
                    VALUES (:id_equipe, :nom, :type, :duree, :lien, :logo, :twitter, :instagram, :youtube)";
            break;

        case "palmares":
            $sql = "INSERT INTO equipe_palmares (id_equipe, tournoi, date, resultat, recompense)
                    VALUES (:id_equipe, :tournoi, :date, :resultat, :recompense)";
            break;

        default:
            return false;
    }

    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute($data);
}
/* ============================================================
   MODIFIER UN ÉLÉMENT (générique)
   ============================================================ */
public function updateElement(string $type, int $id, array $data): bool {

    switch ($type) {

        case "equipe":
            $sql = "UPDATE equipe SET 
                    nom=:nom, tag=:tag, date_creation=:date_creation, description_courte=:description_courte,
                    description_longue=:description_longue, pays=:pays, ville=:ville, site_web=:site_web,
                    email_general=:email_general, email_recrutement=:email_recrutement, telephone=:telephone,
                    logo=:logo, twitter=:twitter, instagram=:instagram, twitch=:twitch, youtube=:youtube,
                    tiktok=:tiktok, facebook=:facebook
                    WHERE id_equipe=:id";
            break;

        case "jeu":
            $sql = "UPDATE equipe_jeux SET nom=:nom, rang=:rang, division=:division, objectifs=:objectifs WHERE id=:id";
            break;

        case "joueur":
            $sql = "UPDATE equipe_joueurs SET nom=:nom, pseudo=:pseudo, age=:age, nationalite=:nationalite, jeu=:jeu,
                    role=:role, experience=:experience, contrat=:contrat, duree_contrat=:duree_contrat,
                    date_arrivee=:date_arrivee, anciennes_equipes=:anciennes_equipes, photo=:photo, twitter=:twitter,
                    instagram=:instagram, twitch=:twitch, youtube=:youtube, tiktok=:tiktok, facebook=:facebook
                    WHERE id=:id";
            break;

        case "manager":
            $sql = "UPDATE equipe_managers SET nom=:nom, role=:role, age=:age, jeux_geres=:jeux_geres, photo=:photo,
                    twitter=:twitter, instagram=:instagram, twitch=:twitch, youtube=:youtube, tiktok=:tiktok,
                    facebook=:facebook
                    WHERE id=:id";
            break;

        case "staff":
            $sql = "UPDATE equipe_staff SET nom=:nom, role=:role, jeux_geres=:jeux_geres, photo=:photo,
                    twitter=:twitter, instagram=:instagram, twitch=:twitch, youtube=:youtube, tiktok=:tiktok,
                    facebook=:facebook
                    WHERE id=:id";
            break;

        case "sponsor":
            $sql = "UPDATE equipe_sponsors SET nom=:nom, type=:type, duree=:duree, lien=:lien, logo=:logo,
                    twitter=:twitter, instagram=:instagram, youtube=:youtube
                    WHERE id=:id";
            break;

        case "palmares":
            $sql = "UPDATE equipe_palmares SET tournoi=:tournoi, date=:date, resultat=:resultat, recompense=:recompense
                    WHERE id=:id";
            break;

        default:
            return false;
    }

    $data["id"] = $id;

    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute($data);
}
/* ============================================================
   SUPPRIMER UN ÉLÉMENT (générique)
   ============================================================ */
public function deleteElement(string $type, int $id): bool {

    $table = match ($type) {
        "jeu"      => "equipe_jeux",
        "joueur"   => "equipe_joueurs",
        "manager"  => "equipe_managers",
        "staff"    => "equipe_staff",
        "sponsor"  => "equipe_sponsors",
        "palmares" => "equipe_palmares",
        default    => null
    };

    if (!$table) return false;

    $stmt = $this->pdo->prepare("DELETE FROM $table WHERE id=:id");
    return $stmt->execute(["id" => $id]);
}
}