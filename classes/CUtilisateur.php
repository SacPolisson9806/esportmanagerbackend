<?php

class CUtilisateur {

    // ------------------------------------------------------------
    // Propriétés représentant les colonnes de la table "utilisateur"
    // ------------------------------------------------------------
    public $id;             // Identifiant unique de l'utilisateur
    public $pseudo;         // Nom d'utilisateur
    public $role;           // Rôle (super_admin, admin, joueur, etc.)
    public $statut;         // Statut (actif, restreint, banni)
    public $permissions;    // Tableau associatif des permissions (JSON en BDD)
    public $ban_expire;     // Date de fin de bannissement
    public $id_equipe;      // Équipe associée (si admin d'équipe)
    public $admin_valide;   // Indique si un admin a été validé par un super_admin

    // ------------------------------------------------------------
    // Constructeur : reçoit une ligne SQL et hydrate l'objet
    // ------------------------------------------------------------
    public function __construct(array $row) {

        // Hydratation des propriétés simples
        $this->id = $row['id_utilisateur'];
        $this->pseudo = $row['pseudo'];
        $this->role = $row['role'];
        $this->statut = $row['statut'];

        // Permissions stockées en JSON → converties en tableau PHP
        // Si la colonne est NULL, on utilise un tableau vide
        $this->permissions = json_decode($row['permissions'] ?? '[]', true);

        // Informations supplémentaires
        $this->ban_expire = $row['ban_expire'];
        $this->id_equipe = $row['id_equipe'];

        // admin_valide peut ne pas exister dans d'anciennes versions → valeur par défaut = 0
        $this->admin_valide = $row['admin_valide'] ?? 0;
    }

    // ------------------------------------------------------------
    // Vérifie si l'utilisateur est marqué comme "banni"
    // ------------------------------------------------------------
    public function estBanni() {
        return $this->statut === "banni";
    }

    // ------------------------------------------------------------
    // Vérifie si le bannissement est encore actif
    // - Si ban_expire est NULL → pas de ban
    // - Si ban_expire est dépassé → ban expiré
    // ------------------------------------------------------------
    public function banActif() {

        // Cas où aucune date n'est définie
        if ($this->ban_expire === null || $this->ban_expire === "0000-00-00 00:00:00") {
            return false;
        }

        // Compare la date de fin de ban avec la date actuelle
        return new DateTime($this->ban_expire) > new DateTime("now");
    }

    // ------------------------------------------------------------
    // Vérifie si l'utilisateur est restreint (permissions limitées)
    // ------------------------------------------------------------
    public function estRestreint() {
        return $this->statut === "restreint";
    }

    // ------------------------------------------------------------
    // Vérifie si l'utilisateur possède une permission donnée
    // Exemple : aPermission("can_post")
    // - Si la permission n'existe pas → autorisé par défaut
    // - Si elle existe → on retourne sa valeur (true/false)
    // ------------------------------------------------------------
    public function aPermission($key) {

        // Permission non définie → autorisée par défaut
        if (!isset($this->permissions[$key])) {
            return true;
        }

        // Permission définie → on retourne sa valeur
        return $this->permissions[$key] === true;
    }
}
