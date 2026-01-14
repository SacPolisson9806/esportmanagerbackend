<?php

class CEquipeAdmin {

    // ------------------------------------------------------------
    // Propriétés représentant les colonnes de la table "equipe_admin"
    // ------------------------------------------------------------
    public $id_equipe;             // Identifiant unique de l'équipe
    public $id_admin;              // Identifiant de l'admin responsable
    public $nom;                   // Nom complet de l'équipe
    public $tag;                   // Tag court (ex : G2, KC, Vitality)
    public $date_creation;         // Date officielle de création de l'équipe
    public $description_courte;    // Description courte (aperçu)
    public $description_longue;    // Description longue (présentation complète)
    public $pays;                  // Pays d'origine
    public $ville;                 // Ville d'origine
    public $site_web;              // Site web officiel
    public $email_general;         // Email général de contact
    public $email_recrutement;     // Email dédié au recrutement
    public $telephone;             // Numéro de téléphone
    public $logo;                  // URL du logo de l'équipe

    // Réseaux sociaux
    public $twitter;
    public $instagram;
    public $twitch;
    public $youtube;
    public $tiktok;
    public $facebook;

    // ------------------------------------------------------------
    // Constructeur : hydrate l'objet à partir d'un tableau SQL
    // ------------------------------------------------------------
    public function __construct($data) {

        // Hydratation des propriétés principales
        $this->id_equipe          = $data['id_equipe'] ?? null;
        $this->id_admin           = $data['id_admin'] ?? null;
        $this->nom                = $data['nom'] ?? null;
        $this->tag                = $data['tag'] ?? null;
        $this->date_creation      = $data['date_creation'] ?? null;

        // Descriptions
        $this->description_courte = $data['description_courte'] ?? null;
        $this->description_longue = $data['description_longue'] ?? null;

        // Informations géographiques
        $this->pays               = $data['pays'] ?? null;
        $this->ville              = $data['ville'] ?? null;

        // Informations de contact
        $this->site_web           = $data['site_web'] ?? null;
        $this->email_general      = $data['email_general'] ?? null;
        $this->email_recrutement  = $data['email_recrutement'] ?? null;
        $this->telephone          = $data['telephone'] ?? null;

        // Logo
        $this->logo               = $data['logo'] ?? null;

        // Réseaux sociaux
        $this->twitter            = $data['twitter'] ?? null;
        $this->instagram          = $data['instagram'] ?? null;
        $this->twitch             = $data['twitch'] ?? null;
        $this->youtube            = $data['youtube'] ?? null;
        $this->tiktok             = $data['tiktok'] ?? null;
        $this->facebook           = $data['facebook'] ?? null;
    }
}
