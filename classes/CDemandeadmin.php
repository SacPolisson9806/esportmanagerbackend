<?php

class CDemandeAdmin
{
    // ------------------------------------------------------------
    // Stocke l'objet PDO pour exécuter les requêtes SQL
    // ------------------------------------------------------------
    private $pdo;

    // ------------------------------------------------------------
    // Constructeur : injection de dépendance (PDO)
    // ------------------------------------------------------------
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // ------------------------------------------------------------
    // Récupérer toutes les demandes d'admin d'équipe
    // Jointure avec la table utilisateur pour récupérer le pseudo
    // ------------------------------------------------------------
    public function getAll()
    {
        $sql = "
            SELECT 
                d.id_demande,            -- Identifiant de la demande
                d.id_utilisateur,        -- Utilisateur ayant fait la demande
                u.pseudo,                -- Pseudo de l'utilisateur
                d.nom_equipe,            -- Nom de l'équipe demandée
                d.description,           -- Description fournie
                d.statut,                -- Statut (en_attente, accepte, refuse)
                d.raison_refus,          -- Raison du refus si refusé
                d.vue_par_utilisateur,   -- Indique si l'utilisateur a vu la réponse
                d.date_demande           -- Date de création
            FROM demandes_admin_equipe d
            INNER JOIN utilisateur u ON u.id_utilisateur = d.id_utilisateur
            ORDER BY d.date_demande DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        // Retourne toutes les demandes sous forme de tableau associatif
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ------------------------------------------------------------
    // Accepter une demande d'admin d'équipe
    // ------------------------------------------------------------
    public function accepter($id_demande)
    {
        $sql = "UPDATE demandes_admin_equipe SET statut = 'accepte' WHERE id_demande = ?";
        $stmt = $this->pdo->prepare($sql);

        // Exécute la requête avec l'ID de la demande
        return $stmt->execute([$id_demande]);
    }

    // ------------------------------------------------------------
    // Refuser une demande d'admin d'équipe
    // - Enregistre la raison du refus
    // - Remet vue_par_utilisateur à 0 pour notifier l'utilisateur
    // ------------------------------------------------------------
    public function refuser($id_demande, $raison)
    {
        $sql = "
            UPDATE demandes_admin_equipe 
            SET statut = 'refuse', 
                raison_refus = ?, 
                vue_par_utilisateur = 0
            WHERE id_demande = ?
        ";

        $stmt = $this->pdo->prepare($sql);

        // Exécute la requête avec la raison et l'ID
        return $stmt->execute([$raison, $id_demande]);
    }

    // ------------------------------------------------------------
    // Supprimer toutes les autres demandes d'un utilisateur
    // (utile lorsqu'une demande est acceptée pour éviter les doublons)
    // ------------------------------------------------------------
    public function supprimerAutres($id_utilisateur, $id_demande)
    {
        $sql = "
            DELETE FROM demandes_admin_equipe
            WHERE id_utilisateur = ? 
              AND id_demande != ?
        ";

        $stmt = $this->pdo->prepare($sql);

        // Supprime toutes les demandes sauf celle acceptée
        return $stmt->execute([$id_utilisateur, $id_demande]);
    }

    // ------------------------------------------------------------
    // Créer une nouvelle demande d'admin d'équipe
    // - statut = en_attente
    // - vue_par_utilisateur = 0 (non vue)
    // - date_demande = NOW()
    // ------------------------------------------------------------
    public function create($id_utilisateur, $nom_equipe, $description)
    {
        $sql = "
            INSERT INTO demandes_admin_equipe 
            (id_utilisateur, nom_equipe, description, statut, vue_par_utilisateur, date_demande)
            VALUES (?, ?, ?, 'en_attente', 0, NOW())
        ";

        $stmt = $this->pdo->prepare($sql);

        // Exécute l'insertion
        return $stmt->execute([$id_utilisateur, $nom_equipe, $description]);
    }
}
