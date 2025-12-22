<?php

class CUtilisateur {
    public $id;
    public $pseudo;
    public $role;
    public $statut;
    public $permissions;
    public $ban_expire;
    public $id_equipe;

    public function __construct(array $row) {
        $this->id = $row['id_utilisateur'];
        $this->pseudo = $row['pseudo'];
        $this->role = $row['role'];
        $this->statut = $row['statut'];
        $this->permissions = json_decode($row['permissions'] ??  [], true);
        $this->ban_expire = $row['ban_expire'];
        $this->id_equipe = $row['id_equipe'];
    }

    public function estBanni() {
        return $this->statut === "banni";
    }

    public function banActif() {
        if ($this->ban_expire === null || $this->ban_expire === "0000-00-00 00:00:00") {
            return false;
        }
        return new DateTime($this->ban_expire) > new DateTime("now");
    }
}
