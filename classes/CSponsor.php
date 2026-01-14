<?php

class CSponsor {

    public $id;
    public $id_equipe;
    public $nom;
    public $type;
    public $duree;
    public $lien;
    public $logo;
    public $twitter;
    public $instagram;
    public $youtube;

    public function __construct($data) {
        $this->id        = $data['id'] ?? null;
        $this->id_equipe = $data['id_equipe'] ?? null;
        $this->nom       = $data['nom'] ?? null;
        $this->type      = $data['type'] ?? null;
        $this->duree     = $data['duree'] ?? null;
        $this->lien      = $data['lien'] ?? null;
        $this->logo      = $data['logo'] ?? null;
        $this->twitter   = $data['twitter'] ?? null;
        $this->instagram = $data['instagram'] ?? null;
        $this->youtube   = $data['youtube'] ?? null;
    }
}
