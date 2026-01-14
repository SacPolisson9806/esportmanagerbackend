<?php

class CJeu {

    public $id;
    public $id_equipe;
    public $nom;
    public $rang;
    public $division;
    public $objectifs;

    public function __construct($data) {
        $this->id        = $data['id'] ?? null;
        $this->id_equipe = $data['id_equipe'] ?? null;
        $this->nom       = $data['nom'] ?? null;
        $this->rang      = $data['rang'] ?? null;
        $this->division  = $data['division'] ?? null;
        $this->objectifs = $data['objectifs'] ?? null;
    }
}
