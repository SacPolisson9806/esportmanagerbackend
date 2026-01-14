<?php

require_once "CJoueur.php";

class ControleurJoueur {

    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /* -----------------------------------------
        UPLOAD PHOTO
    ----------------------------------------- */
    private function uploadPhoto($file) {
        if (!isset($file) || $file["error"] !== UPLOAD_ERR_OK) {
            return null;
        }

        $folder = __DIR__ . "/../../uploads/joueurs/";
        if (!is_dir($folder)) mkdir($folder, 0777, true);

        $ext = pathinfo($file["name"], PATHINFO_EXTENSION);
        $filename = "joueur_" . time() . "_" . rand(1000,9999) . "." . $ext;

        $path = $folder . $filename;

        move_uploaded_file($file["tmp_name"], $path);

        return "uploads/joueurs/" . $filename;
    }

    /* -----------------------------------------
        GET ALL
    ----------------------------------------- */
    public function getAllJoueurs($id_equipe) {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM equipe_joueurs
            WHERE id_equipe = :id_equipe
            ORDER BY id DESC
        ");

        $stmt->execute([":id_equipe" => $id_equipe]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => new CJoueur($row), $rows);
    }

    /* -----------------------------------------
        GET BY ID
    ----------------------------------------- */
    public function getJoueurById($id) {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM equipe_joueurs
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute([":id" => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new CJoueur($row) : null;
    }

    /* -----------------------------------------
        CREATE
    ----------------------------------------- */
    public function ajouterJoueur($data, $file_photo) {

        $photoPath = $this->uploadPhoto($file_photo);

        $stmt = $this->pdo->prepare("
            INSERT INTO equipe_joueurs (
                id_equipe, nom, pseudo, age, nationalite, jeu, role, experience,
                contrat, duree_contrat, date_arrivee, anciennes_equipes,
                photo, twitter, instagram, twitch, youtube, tiktok, facebook
            )
            VALUES (
                :id_equipe, :nom, :pseudo, :age, :nationalite, :jeu, :role, :experience,
                :contrat, :duree_contrat, :date_arrivee, :anciennes_equipes,
                :photo, :twitter, :instagram, :twitch, :youtube, :tiktok, :facebook
            )
        ");

        $stmt->execute([
            ":id_equipe"         => $data["id_equipe"],
            ":nom"               => $data["nom"],
            ":pseudo"            => $data["pseudo"],
            ":age"               => $data["age"],
            ":nationalite"       => $data["nationalite"],
            ":jeu"               => $data["jeu"],
            ":role"              => $data["role"],
            ":experience"        => $data["experience"],
            ":contrat"           => $data["contrat"],
            ":duree_contrat"     => $data["duree_contrat"],
            ":date_arrivee"      => $data["date_arrivee"],
            ":anciennes_equipes" => $data["anciennes_equipes"],
            ":photo"             => $photoPath,
            ":twitter"           => $data["twitter"],
            ":instagram"         => $data["instagram"],
            ":twitch"            => $data["twitch"],
            ":youtube"           => $data["youtube"],
            ":tiktok"            => $data["tiktok"],
            ":facebook"          => $data["facebook"]
        ]);

        return [
            "success" => true,
            "message" => "Joueur ajouté",
            "id" => $this->pdo->lastInsertId()
        ];
    }

    /* -----------------------------------------
        UPDATE
    ----------------------------------------- */
    public function updateJoueur($id, $data, $file_photo) {

        $newPhoto = $this->uploadPhoto($file_photo);

        if (!$newPhoto) {
            $old = $this->getJoueurById($id);
            $newPhoto = $old->photo;
        }

        $stmt = $this->pdo->prepare("
            UPDATE equipe_joueurs SET
                nom = :nom,
                pseudo = :pseudo,
                age = :age,
                nationalite = :nationalite,
                jeu = :jeu,
                role = :role,
                experience = :experience,
                contrat = :contrat,
                duree_contrat = :duree_contrat,
                date_arrivee = :date_arrivee,
                anciennes_equipes = :anciennes_equipes,
                photo = :photo,
                twitter = :twitter,
                instagram = :instagram,
                twitch = :twitch,
                youtube = :youtube,
                tiktok = :tiktok,
                facebook = :facebook
            WHERE id = :id
        ");

        $stmt->execute([
            ":nom"               => $data["nom"],
            ":pseudo"            => $data["pseudo"],
            ":age"               => $data["age"],
            ":nationalite"       => $data["nationalite"],
            ":jeu"               => $data["jeu"],
            ":role"              => $data["role"],
            ":experience"        => $data["experience"],
            ":contrat"           => $data["contrat"],
            ":duree_contrat"     => $data["duree_contrat"],
            ":date_arrivee"      => $data["date_arrivee"],
            ":anciennes_equipes" => $data["anciennes_equipes"],
            ":photo"             => $newPhoto,
            ":twitter"           => $data["twitter"],
            ":instagram"         => $data["instagram"],
            ":twitch"            => $data["twitch"],
            ":youtube"           => $data["youtube"],
            ":tiktok"            => $data["tiktok"],
            ":facebook"          => $data["facebook"],
            ":id"                => $id
        ]);

        return [
            "success" => true,
            "message" => "Joueur mis à jour"
        ];
    }

    /* -----------------------------------------
        DELETE
    ----------------------------------------- */
    public function supprimerJoueur($id) {
        $stmt = $this->pdo->prepare("
            DELETE FROM equipe_joueurs
            WHERE id = :id
        ");

        $stmt->execute([":id" => $id]);

        return [
            "success" => true,
            "message" => "Joueur supprimé"
        ];
    }
}
