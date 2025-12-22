<?php
require_once "CUtilisateur.php";

class ControleurUtilisateur {

    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function chargerParPseudo($pseudo) {
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateur WHERE pseudo = :pseudo LIMIT 1");
        $stmt->execute([":pseudo" => $pseudo]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return new CUtilisateur($row);
    }

    public function verifierConnexion($pseudo, $mot_de_passe) {
        $user = $this->chargerParPseudo($pseudo);
        if (!$user) return ["success" => false, "message" => "Pseudo incorrect"];

        // Vérification mot de passe
        $stmt = $this->pdo->prepare("SELECT mot_de_passe FROM utilisateur WHERE id_utilisateur = :id");
        $stmt->execute([":id" => $user->id]);
        $hash = $stmt->fetchColumn();

        if (!password_verify($mot_de_passe, $hash)) {
            return ["success" => false, "message" => "Mot de passe incorrect"];
        }

        // Vérification bannissement
        if ($user->estBanni() && $user->banActif()) {
            return [
                "success" => false,
                "message" => "Compte banni jusqu'au " . (new DateTime($user->ban_expire))->format("d/m/Y H:i")
            ];
        }

        // Ban expiré → réactivation
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

    public function creerUtilisateur($pseudo, $email, $mot_de_passe) {

    // Vérifier email
    $stmt = $this->pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = :email");
    $stmt->execute([":email" => $email]);
    if ($stmt->rowCount() > 0) {
        return ["success" => false, "message" => "Email déjà utilisé"];
    }

    // Vérifier pseudo
    $stmt = $this->pdo->prepare("SELECT id_utilisateur FROM utilisateur WHERE pseudo = :pseudo");
    $stmt->execute([":pseudo" => $pseudo]);
    if ($stmt->rowCount() > 0) {
        return ["success" => false, "message" => "Pseudo déjà utilisé"];
    }

    // Hash du mot de passe
    $hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

    // Insertion
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

}
