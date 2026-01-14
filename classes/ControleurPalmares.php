<?php

require_once "CPalmares.php";

class ControleurPalmares {

    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /* -----------------------------------------
        GET ALL
    ----------------------------------------- */
    public function getAllPalmares($id_equipe) {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM equipe_palmares
            WHERE id_equipe = :id_equipe
            ORDER BY date DESC
        ");

        $stmt->execute([":id_equipe" => $id_equipe]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => new CPalmares($row), $rows);
    }

    /* -----------------------------------------
        GET BY ID
    ----------------------------------------- */
    public function getPalmaresById($id) {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM equipe_palmares
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute([":id" => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new CPalmares($row) : null;
    }

    /* -----------------------------------------
        CREATE
    ----------------------------------------- */
    public function ajouterPalmares($data) {

        $stmt = $this->pdo->prepare("
            INSERT INTO equipe_palmares (
                id_equipe, tournoi, date, resultat, recompense
            )
            VALUES (
                :id_equipe, :tournoi, :date, :resultat, :recompense
            )
        ");

        $stmt->execute([
            ":id_equipe"  => $data["id_equipe"],
            ":tournoi"    => $data["tournoi"],
            ":date"       => $data["date"],
            ":resultat"   => $data["resultat"],
            ":recompense" => $data["recompense"]
        ]);

        return [
            "success" => true,
            "message" => "Palmarès ajouté",
            "id" => $this->pdo->lastInsertId()
        ];
    }

    /* -----------------------------------------
        UPDATE
    ----------------------------------------- */
    public function updatePalmares($id, $data) {

        $stmt = $this->pdo->prepare("
            UPDATE equipe_palmares SET
                tournoi = :tournoi,
                date = :date,
                resultat = :resultat,
                recompense = :recompense
            WHERE id = :id
        ");

        $stmt->execute([
            ":tournoi"    => $data["tournoi"],
            ":date"       => $data["date"],
            ":resultat"   => $data["resultat"],
            ":recompense" => $data["recompense"],
            ":id"         => $id
        ]);

        return [
            "success" => true,
            "message" => "Palmarès mis à jour"
        ];
    }

    /* -----------------------------------------
        DELETE
    ----------------------------------------- */
    public function supprimerPalmares($id) {
        $stmt = $this->pdo->prepare("
            DELETE FROM equipe_palmares
            WHERE id = :id
        ");

        $stmt->execute([":id" => $id]);

        return [
            "success" => true,
            "message" => "Palmarès supprimé"
        ];
    }
}
