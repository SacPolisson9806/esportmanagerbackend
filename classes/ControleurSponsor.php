<?php

require_once "CSponsor.php";

class ControleurSponsor {

    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /* -----------------------------------------
        UPLOAD LOGO
    ----------------------------------------- */
    private function uploadLogo($file) {
        if (!isset($file) || $file["error"] !== UPLOAD_ERR_OK) {
            return null;
        }

        $folder = __DIR__ . "/../../uploads/sponsors/";
        if (!is_dir($folder)) mkdir($folder, 0777, true);

        $ext = pathinfo($file["name"], PATHINFO_EXTENSION);
        $filename = "sponsor_" . time() . "_" . rand(1000,9999) . "." . $ext;

        $path = $folder . $filename;

        move_uploaded_file($file["tmp_name"], $path);

        return "uploads/sponsors/" . $filename;
    }

    /* -----------------------------------------
        GET ALL
    ----------------------------------------- */
    public function getAllSponsors($id_equipe) {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM equipe_sponsors
            WHERE id_equipe = :id_equipe
            ORDER BY id DESC
        ");

        $stmt->execute([":id_equipe" => $id_equipe]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => new CSponsor($row), $rows);
    }

    /* -----------------------------------------
        GET BY ID
    ----------------------------------------- */
    public function getSponsorById($id) {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM equipe_sponsors
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute([":id" => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new CSponsor($row) : null;
    }

    /* -----------------------------------------
        CREATE
    ----------------------------------------- */
    public function ajouterSponsor($data, $file_logo) {

        $logoPath = $this->uploadLogo($file_logo);

        $stmt = $this->pdo->prepare("
            INSERT INTO equipe_sponsors (
                id_equipe, nom, type, duree, lien,
                logo, twitter, instagram, youtube
            )
            VALUES (
                :id_equipe, :nom, :type, :duree, :lien,
                :logo, :twitter, :instagram, :youtube
            )
        ");

        $stmt->execute([
            ":id_equipe" => $data["id_equipe"],
            ":nom"       => $data["nom"],
            ":type"      => $data["type"],
            ":duree"     => $data["duree"],
            ":lien"      => $data["lien"],
            ":logo"      => $logoPath,
            ":twitter"   => $data["twitter"],
            ":instagram" => $data["instagram"],
            ":youtube"   => $data["youtube"]
        ]);

        return [
            "success" => true,
            "message" => "Sponsor ajouté",
            "id" => $this->pdo->lastInsertId()
        ];
    }

    /* -----------------------------------------
        UPDATE
    ----------------------------------------- */
    public function updateSponsor($id, $data, $file_logo) {

        $newLogo = $this->uploadLogo($file_logo);

        if (!$newLogo) {
            $old = $this->getSponsorById($id);
            $newLogo = $old->logo;
        }

        $stmt = $this->pdo->prepare("
            UPDATE equipe_sponsors SET
                nom = :nom,
                type = :type,
                duree = :duree,
                lien = :lien,
                logo = :logo,
                twitter = :twitter,
                instagram = :instagram,
                youtube = :youtube
            WHERE id = :id
        ");

        $stmt->execute([
            ":nom"       => $data["nom"],
            ":type"      => $data["type"],
            ":duree"     => $data["duree"],
            ":lien"      => $data["lien"],
            ":logo"      => $newLogo,
            ":twitter"   => $data["twitter"],
            ":instagram" => $data["instagram"],
            ":youtube"   => $data["youtube"],
            ":id"        => $id
        ]);

        return [
            "success" => true,
            "message" => "Sponsor mis à jour"
        ];
    }

    /* -----------------------------------------
        DELETE
    ----------------------------------------- */
    public function supprimerSponsor($id) {
        $stmt = $this->pdo->prepare("
            DELETE FROM equipe_sponsors
            WHERE id = :id
        ");

        $stmt->execute([":id" => $id]);

        return [
            "success" => true,
            "message" => "Sponsor supprimé"
        ];
    }
}
