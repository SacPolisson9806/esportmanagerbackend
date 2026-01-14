<?php

require_once "CDemandeAdmin.php";
require_once "ControleurUtilisateur.php";

class ControleurDemandeAdmin
{
    // ------------------------------------------------------------
    // Propriétés internes
    // ------------------------------------------------------------
    private $pdo;          // Connexion PDO
    private $demande;      // Gestion des demandes admin (CDemandeAdmin)
    private $utilisateur;  // Gestion des utilisateurs (ControleurUtilisateur)

    // ------------------------------------------------------------
    // Constructeur : injection de dépendances
    // - Initialise PDO
    // - Instancie les contrôleurs nécessaires
    // ------------------------------------------------------------
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;

        // Gestion des demandes admin
        $this->demande = new CDemandeAdmin($pdo);

        // Gestion des utilisateurs (pour changer le rôle)
        $this->utilisateur = new ControleurUtilisateur($pdo);
    }

    // ------------------------------------------------------------
    // Récupérer toutes les demandes d'admin d'équipe
    // ------------------------------------------------------------
    public function getAll()
    {
        return $this->demande->getAll();
    }

    // ------------------------------------------------------------
    // Créer une nouvelle demande d'admin d'équipe
    // ------------------------------------------------------------
    public function create($id_utilisateur, $nom_equipe, $description)
    {
        return $this->demande->create($id_utilisateur, $nom_equipe, $description);
    }

    // ------------------------------------------------------------
    // Accepter une demande d'admin d'équipe
    // Workflow :
    // 1. Marquer la demande comme acceptée
    // 2. Donner le rôle "admin_equipe" à l'utilisateur
    // 3. Valider l'admin (admin_valide = 1)
    // 4. Supprimer toutes les autres demandes de cet utilisateur
    // ------------------------------------------------------------
    public function accepter($id_demande, $id_utilisateur)
    {
        // Étape 1 : accepter la demande
        $this->demande->accepter($id_demande);

        // Étape 2 + 3 : donner le rôle admin_equipe + valider l'admin
        $this->utilisateur->updateRole($id_utilisateur, "admin_equipe", 1);

        // Étape 4 : supprimer les autres demandes de cet utilisateur
        $this->demande->supprimerAutres($id_utilisateur, $id_demande);

        return true;
    }

    // ------------------------------------------------------------
    // Refuser une demande d'admin d'équipe
    // - Enregistre la raison du refus
    // - Marque la demande comme non vue par l'utilisateur
    // ------------------------------------------------------------
    public function refuser($id_demande, $raison)
    {
        return $this->demande->refuser($id_demande, $raison);
    }

    // ------------------------------------------------------------
    // Récupérer la demande d'un utilisateur spécifique
    // (utile pour afficher l'état de sa demande dans son espace)
    // ------------------------------------------------------------
    public function getByUser($id_utilisateur)
    {
        // On récupère toutes les demandes
        $demandes = $this->demande->getAll();

        // On cherche celle qui correspond à l'utilisateur
        foreach ($demandes as $d) {
            if ($d["id_utilisateur"] == $id_utilisateur) {
                return $d;
            }
        }

        // Aucune demande trouvée
        return null;
    }
}
