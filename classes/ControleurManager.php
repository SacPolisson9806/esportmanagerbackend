<?php

require_once "CManager.php";

class ControleurManager {

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

        $folder = __DIR__ . "/../../uploads/managers/";
        if (!is_dir($folder)) mkdir($folder, 0777, true);

        $ext = pathinfo($file["name"], PATHINFO_EXTENSION);
        $filename = "manager_" . time() . "_" . rand(1000,9999) . "." . $ext;

        $path = $folder . $filename;

        move_uploaded_file($file["tmp_name"], $path);

        return "uploads/managers/" . $filename;
    }

    /* -----------------------------------------
        GET ALL
    ----------------------------------------- */
    public function getAllManagers($id_equipe) {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM equipe_managers
            WHERE id_equipe = :id_equipe
            ORDER BY id DESC
        ");

        $stmt->execute([":id_equipe" => $id_equipe]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => new CManager($row), $rows);
    }

    /* -----------------------------------------
        GET BY ID
    ----------------------------------------- */
    public function getManagerById($id) {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM equipe_managers
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute([":id" => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new CManager($row) : null;
    }

    /* -----------------------------------------
        CREATE
    ----------------------------------------- */
    public function ajouterManager($data, $file_photo) {

        $photoPath = $this->uploadPhoto($file_photo);

        $stmt = $this->pdo->prepare("
            INSERT INTO equipe_managers (
                id_equipe, nom, role, age, jeux_geres,
                photo, twitter, instagram, twitch, youtube, tiktok, facebook
            )
            VALUES (
                :id_equipe, :nom, :role, :age, :jeux_geres,
                :photo, :twitter, :instagram, :twitch, :youtube, :tiktok, :facebook
            )
        ");

        $stmt->execute([
            ":id_equipe"  => $data["id_equipe"],
            ":nom"        => $data["nom"],
            ":role"       => $data["role"],
            ":age"        => $data["age"],
            ":jeux_geres" => $data["jeux_geres"],
            ":photo"      => $photoPath,
            ":twitter"    => $data["twitter"],
            ":instagram"  => $data["instagram"],
            ":twitch"     => $data["twitch"],
            ":youtube"    => $data["youtube"],
            ":tiktok"     => $data["tiktok"],
            ":facebook"   => $data["facebook"]
        ]);

        return [
            "success" => true,
            "message" => "Manager ajouté",
            "id" => $this->pdo->lastInsertId()
        ];
    }

    /* -----------------------------------------
        UPDATE
    ----------------------------------------- */
    public function updateManager($id, $data, $file_photo) {

        $newPhoto = $this->uploadPhoto($file_photo);

        if (!$newPhoto) {
            $old = $this->getManagerById($id);
            $newPhoto = $old->photo;
        }

        $stmt = $this->pdo->prepare("
            UPDATE equipe_managers SET
                nom = :nom,
                role = :role,
                age = :age,
                jeux_geres = :jeux_geres,
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
            ":nom"        => $data["nom"],
            ":role"       => $data["role"],
            ":age"        => $data["age"],
            ":jeux_geres" => $data["jeux_geres"],
            ":photo"      => $newPhoto,
            ":twitter"    => $data["twitter"],
            ":instagram"  => $data["instagram"],
            ":twitch"     => $data["twitch"],
            ":youtube"    => $data["youtube"],
            ":tiktok"     => $data["tiktok"],
            ":facebook"   => $data["facebook"],
            ":id"         => $id
        ]);

        return [
            "success" => true,
            "message" => "Manager mis à jour"
        ];
    }

    /* -----------------------------------------
        DELETE
    ----------------------------------------- */
    public function supprimerManager($id) {
        $stmt = $this->pdo->prepare("
            DELETE FROM equipe_managers
            WHERE id = :id
        ");

        $stmt->execute([":id" => $id]);

        return [
            "success" => true,
            "message" => "Manager supprimé"
        ];
    }
}
