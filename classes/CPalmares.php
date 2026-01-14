<?php

class CPalmares {

    public $id;
    public $id_equipe;
    public $tournoi;
    public $date;
    public $resultat;
    public $recompense;

    public function __construct($data) {
        $this->id         = $data['id'] ?? null;
        $this->id_equipe  = $data['id_equipe'] ?? null;
        $this->tournoi    = $data['tournoi'] ?? null;
        $this->date       = $data['date'] ?? null;
        $this->resultat   = $data['resultat'] ?? null;
        $this->recompense = $data['recompense'] ?? null;
    }
}
