<?php

class CStaff {

    public $id;
    public $id_equipe;
    public $nom;
    public $role;
    public $age;
    public $jeux_geres;
    public $photo;
    public $twitter;
    public $instagram;
    public $twitch;
    public $youtube;
    public $tiktok;
    public $facebook;

    public function __construct($data) {
        $this->id         = $data['id'] ?? null;
        $this->id_equipe  = $data['id_equipe'] ?? null;
        $this->nom        = $data['nom'] ?? null;
        $this->role       = $data['role'] ?? null;
        $this->age        = $data['age'] ?? null;
        $this->jeux_geres = $data['jeux_geres'] ?? null;
        $this->photo      = $data['photo'] ?? null;
        $this->twitter    = $data['twitter'] ?? null;
        $this->instagram  = $data['instagram'] ?? null;
        $this->twitch     = $data['twitch'] ?? null;
        $this->youtube    = $data['youtube'] ?? null;
        $this->tiktok     = $data['tiktok'] ?? null;
        $this->facebook   = $data['facebook'] ?? null;
    }
}
