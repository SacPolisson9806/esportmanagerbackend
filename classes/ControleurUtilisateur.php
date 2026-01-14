<?php
require_once "CUtilisateur.php";

class ControleurUtilisateur {

    private $pdo;

    // ------------------------------------------------------------
    // Constructeur : reçoit l'objet PDO pour exécuter les requêtes SQL
    // ------------------------------------------------------------
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // ------------------------------------------------------------
    // Charger un utilisateur à partir de son pseudo
    // Retourne un objet CUtilisateur ou null si introuvable
    // ------------------------------------------------------------
    public function chargerParPseudo($pseudo) {
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateur WHERE pseudo = :pseudo LIMIT 1");
        $stmt->execute([":pseudo" => $pseudo]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return new CUtilisateur($row);
    }

    // ------------------------------------------------------------
    // Vérifier la connexion d'un utilisateur
    // - Vérifie pseudo
    // - Vérifie mot de passe
    // - Vérifie bannissement
    // - Réactive un ban expiré
    // ------------------------------------------------------------
    public function verifierConnexion($pseudo, $mot_de_passe) {
        $user = $this->chargerParPseudo($pseudo);
        if (!$user) return ["success" => false, "message" => "Pseudo incorrect"];

        // Récupération du hash du mot de passe
        $stmt = $this->pdo->prepare("SELECT mot_de_passe FROM utilisateur WHERE id_utilisateur = :id");
        $stmt->execute([":id" => $user->id]);
        $hash = $stmt->fetchColumn();

        // Vérification du mot de passe
        if (!password_verify($mot_de_passe, $hash)) {
            return ["success" => false, "message" => "Mot de passe incorrect"];
        }

        // Vérification si l'utilisateur est banni
        if ($user->estBanni() && $user->banActif()) {
            return [
                "success" => false,
                "message" => "Compte banni jusqu'au " . (new DateTime($user->ban_expire))->format("d/m/Y H:i")
            ];
        }

        // Si le ban est expiré → réactivation automatique
        if ($user->estBanni() && !$user->banActif()) {
            $this->pdo->prepare("
                UPDATE utilisateur 
                SET statut = 'actif', ban_expire = NULL 
                WHERE id_utilisateur = :id
            ")->execute([":id" => $user->id]);

            $user->statut = "actif";
            $user->ban_expire = null;
        }

        return ["success" => true, "user" => $user];
    }

    // ------------------------------------------------------------
    // Création d'un utilisateur standard (non admin)
    // ------------------------------------------------------------
    public function creerUtilisateur($pseudo, $email, $mot_de_passe) {

        // Vérifier si l'email existe déjà
        $stmt = $this->pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = :email");
        $stmt->execute([":email" => $email]);
        if ($stmt->rowCount() > 0) {
            return ["success" => false, "message" => "Email déjà utilisé"];
        }

        // Vérifier si le pseudo existe déjà
        $stmt = $this->pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE pseudo = :pseudo");
        $stmt->execute([":pseudo" => $pseudo]);
        if ($stmt->rowCount() > 0) {
            return ["success" => false, "message" => "Pseudo déjà utilisé"];
        }

        // Hash du mot de passe
        $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

        // Insertion SQL
        $stmt = $this->pdo->prepare("
            INSERT INTO utilisateur (pseudo, email, mot_de_passe)
            VALUES (:pseudo, :email, :mot_de_passe)
        ");

        $ok = $stmt->execute([
            ":pseudo" => $pseudo,
            ":email" => $email,
            ":mot_de_passe" => $hash
        ]);

        if (!$ok) {
            return ["success" => false, "message" => "Erreur lors de la création du compte"];
        }

        return ["success" => true, "message" => "Compte créé avec succès"];
    }

    // ------------------------------------------------------------
    // Récupérer tous les utilisateurs (pour le tableau admin)
    // ------------------------------------------------------------
    public function getTousLesUtilisateurs() {
        $stmt = $this->pdo->query("
            SELECT 
                id_utilisateur,
                pseudo,
                email,
                role,
                statut,
                id_equipe,
                permissions,
                ban_expire
            FROM utilisateur
            ORDER BY id_utilisateur ASC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ------------------------------------------------------------
    // Bannir un utilisateur
    // - Durée prédéfinie ou personnalisée
    // - Permissions modifiées pendant le ban
    // ------------------------------------------------------------
    public function bannirUtilisateur($id, $duration, $customDate, $permissions) {

        // Gestion des durées
        if ($duration === "permanent") {
            $banExpire = null;

        } elseif ($duration === "custom") {
            if (!$customDate) {
                return ["success" => false, "message" => "Date personnalisée manquante"];
            }
            $banExpire = str_replace("T", " ", $customDate) . ":00";

        } else {
            // Durées prédéfinies
            $intervalMap = [
                "1d" => "+1 day",
                "3d" => "+3 days",
                "7d" => "+7 days",
                "30d" => "+30 days"
            ];

            if (!isset($intervalMap[$duration])) {
                return ["success" => false, "message" => "Durée invalide"];
            }

            $now = new DateTime("now");
            $now->modify($intervalMap[$duration]);
            $banExpire = $now->format("Y-m-d H:i:s");
        }

        // Permissions JSON
        $permissionsJson = json_encode($permissions);

        // Mise à jour SQL
        $stmt = $this->pdo->prepare("
            UPDATE utilisateur
            SET statut = 'banni',
                ban_expire = :ban_expire,
                permissions = :permissions
            WHERE id_utilisateur = :id
        ");

        $stmt->execute([
            ":ban_expire" => $banExpire,
            ":permissions" => $permissionsJson,
            ":id" => $id
        ]);

        return ["success" => true];
    }

    // ------------------------------------------------------------
    // Création d'un utilisateur admin
    // ------------------------------------------------------------
    public function creerUtilisateurAdmin($pseudo, $email, $mot_de_passe, $role, $id_equipe, $permissions) {

        // Vérifier doublon email
        $stmt = $this->pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = :email");
        $stmt->execute([":email" => $email]);
        if ($stmt->rowCount() > 0) {
            return ["success" => false, "message" => "Email déjà utilisé"];
        }

        // Vérifier doublon pseudo
        $stmt = $this->pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE pseudo = :pseudo");
        $stmt->execute([":pseudo" => $pseudo]);
        if ($stmt->rowCount() > 0) {
            return ["success" => false, "message" => "Pseudo déjà utilisé"];
        }

        // Hash du mot de passe
        $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

        // Insertion SQL
        $stmt = $this->pdo->prepare("
            INSERT INTO utilisateur (pseudo, email, mot_de_passe, role, statut, id_equipe, permissions)
            VALUES (:pseudo, :email, :mot_de_passe, :role, 'actif', :id_equipe, :permissions)
        ");

        $ok = $stmt->execute([
            ":pseudo" => $pseudo,
            ":email" => $email,
            ":mot_de_passe" => $hash,
            ":role" => $role,
            ":id_equipe" => $id_equipe,
            ":permissions" => json_encode($permissions)
        ]);

        if (!$ok) {
            return ["success" => false, "message" => "Erreur lors de la création du compte"];
        }

        return ["success" => true, "message" => "Compte créé avec succès"];
    }

    // ------------------------------------------------------------
    // Modifier le rôle d'un utilisateur
    // ------------------------------------------------------------
    public function mettreAJourRole($id, $role) {

        // Vérifier que le rôle existe
        $rolesValides = ["super_admin", "admin", "moderateur", "joueur", "coach", "visiteur"];

        if (!in_array($role, $rolesValides)) {
            return ["success" => false, "message" => "Rôle invalide"];
        }

        // Mise à jour SQL
        $stmt = $this->pdo->prepare("
            UPDATE utilisateur
            SET role = :role
            WHERE id_utilisateur = :id
        ");

        $stmt->execute([
            ":role" => $role,
            ":id" => $id
        ]);

        return ["success" => true];
    }

    // ------------------------------------------------------------
    // Modifier le statut d'un utilisateur (actif / restreint)
    // ------------------------------------------------------------
    public function mettreAJourStatut($id, $statut) {

        // Statuts autorisés
        $statutsValides = ["actif", "restreint"];

        if (!in_array($statut, $statutsValides)) {
            return ["success" => false, "message" => "Statut invalide"];
        }

        // Mise à jour SQL
        $stmt = $this->pdo->prepare("
            UPDATE utilisateur
            SET statut = :statut,
                ban_expire = NULL
            WHERE id_utilisateur = :id
        ");

        $stmt->execute([
            ":statut" => $statut,
            ":id" => $id
        ]);

        return ["success" => true];
    }

    // ------------------------------------------------------------
    // Restreindre un utilisateur (permissions limitées)
    // ------------------------------------------------------------
    public function restreindreUtilisateur($id, $permissions) {
        $permissionsJson = json_encode($permissions);

        $stmt = $this->pdo->prepare("
            UPDATE utilisateur
            SET statut = 'restreint',
                permissions = :permissions,
                ban_expire = NULL
            WHERE id_utilisateur = :id
        ");

        $stmt->execute([
            ":permissions" => $permissionsJson,
            ":id" => $id
        ]);

        return ["success" => true, "message" => "Utilisateur restreint"];
    }

    // ------------------------------------------------------------
    // Supprimer un utilisateur
    // ------------------------------------------------------------
    public function supprimerUtilisateur($id) {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM utilisateur WHERE id_utilisateur = :id");
            $stmt->execute([":id" => $id]);

            return ["success" => true, "message" => "Utilisateur supprimé"];
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Impossible de supprimer l'utilisateur (contrainte SQL ?)"];
        }
    }

    // ------------------------------------------------------------
    // Valider un admin (admin_valide = 1)
    // ------------------------------------------------------------
    public function validerAdmin($id) {
        $stmt = $this->pdo->prepare("
            UPDATE utilisateur 
            SET admin_valide = 1 
            WHERE id_utilisateur = :id
        ");

        $stmt->execute([":id" => $id]);

        return ["success" => true, "message" => "Admin validé"];
    }

    // ------------------------------------------------------------
    // Mettre à jour rôle + validation admin en même temps
    // ------------------------------------------------------------
    public function updateRole($id_utilisateur, $role, $admin_valide = 0)
    {
        $stmt = $this->pdo->prepare("
            UPDATE utilisateur
            SET role = :role,
                admin_valide = :admin_valide
            WHERE id_utilisateur = :id
        ");

        return $stmt->execute([
            ":role" => $role,
            ":admin_valide" => $admin_valide,
            ":id" => $id_utilisateur
        ]);
    }
}
