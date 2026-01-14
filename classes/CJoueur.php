<?php

class CJoueur {

    public $id_joueur;
    public $id_equipe;
    public $nom;
    public $pseudo;
    public $age;
    public $nationalite;
    public $jeu;
    public $role;
    public $experience;
    public $contrat;
    public $duree_contrat;
    public $date_arrivee;
    public $anciennes_equipes;
    public $twitter;
    public $instagram;
    public $twitch;
    public $youtube;
    public $tiktok;
    public $facebook;
    public $photo;

    public function __construct($data) {
    $this->id                = $data['id'] ?? null;
    $this->id_equipe         = $data['id_equipe'] ?? null;
    $this->nom               = $data['nom'] ?? null;
    $this->pseudo            = $data['pseudo'] ?? null;
    $this->age               = $data['age'] ?? null;
    $this->nationalite       = $data['nationalite'] ?? null;
    $this->jeu               = $data['jeu'] ?? null;
    $this->role              = $data['role'] ?? null;
    $this->experience        = $data['experience'] ?? null;
    $this->contrat           = $data['contrat'] ?? null;
    $this->duree_contrat     = $data['duree_contrat'] ?? null;
    $this->date_arrivee      = $data['date_arrivee'] ?? null;
    $this->anciennes_equipes = $data['anciennes_equipes'] ?? null;
    $this->photo             = $data['photo'] ?? null;
    $this->twitter           = $data['twitter'] ?? null;
    $this->instagram         = $data['instagram'] ?? null;
    $this->twitch            = $data['twitch'] ?? null;
    $this->youtube           = $data['youtube'] ?? null;
    $this->tiktok            = $data['tiktok'] ?? null;
    $this->facebook          = $data['facebook'] ?? null;
}

}
