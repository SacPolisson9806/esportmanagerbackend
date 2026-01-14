<?php

require_once "CJeu.php";

class ControleurJeu {

    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // Récupérer tous les jeux d'une équipe
    public function getAllJeux($id_equipe) {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM equipe_jeux
            WHERE id_equipe = :id_equipe
            ORDER BY id DESC
        ");

        $stmt->execute([":id_equipe" => $id_equipe]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => new CJeu($row), $rows);
    }

    // Récupérer un jeu par ID
    public function getJeuById($id) {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM equipe_jeux
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute([":id" => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? new CJeu($row) : null;
    }

    // Ajouter un jeu
    public function ajouterJeu($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO equipe_jeux (id_equipe, nom, rang, division, objectifs)
            VALUES (:id_equipe, :nom, :rang, :division, :objectifs)
        ");

        $stmt->execute([
            ":id_equipe" => $data["id_equipe"],
            ":nom"       => $data["nom"],
            ":rang"      => $data["rang"],
            ":division"  => $data["division"],
            ":objectifs" => $data["objectifs"]
        ]);

        return [
            "success" => true,
            "message" => "Jeu ajouté",
            "id" => $this->pdo->lastInsertId()
        ];
    }

    // Modifier un jeu
    public function updateJeu($id, $data) {
        $stmt = $this->pdo->prepare("
            UPDATE equipe_jeux
            SET nom = :nom,
                rang = :rang,
                division = :division,
                objectifs = :objectifs
            WHERE id = :id
        ");

        $stmt->execute([
            ":nom"       => $data["nom"],
            ":rang"      => $data["rang"],
            ":division"  => $data["division"],
            ":objectifs" => $data["objectifs"],
            ":id"        => $id
        ]);

        return [
            "success" => true,
            "message" => "Jeu mis à jour"
        ];
    }

    // Supprimer un jeu
    public function supprimerJeu($id) {
        $stmt = $this->pdo->prepare("
            DELETE FROM equipe_jeux
            WHERE id = :id
        ");

        $stmt->execute([":id" => $id]);

        return [
            "success" => true,
            "message" => "Jeu supprimé"
        ];
    }
}
