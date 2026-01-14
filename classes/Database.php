<?php

class Database {

    // ------------------------------------------------------------
    // Paramètres de connexion à la base de données
    // ------------------------------------------------------------
    private static $host = "localhost";          // Adresse du serveur MySQL
    private static $dbname = "esport_manager";   // Nom de la base de données
    private static $username = "root";           // Nom d'utilisateur MySQL
    private static $password = "";               // Mot de passe MySQL
    private static $pdo = null;                  // Instance PDO (singleton)

    // ------------------------------------------------------------
    // Méthode statique : connect()
    // Retourne une instance PDO unique (pattern Singleton)
    // ------------------------------------------------------------
    public static function connect() {

        // Si aucune connexion n'existe encore → on la crée
        if (self::$pdo === null) {
            try {

                // Création de l'objet PDO
                self::$pdo = new PDO(
                    "mysql:host=" . self::$host . ";dbname=" . self::$dbname . ";charset=utf8",
                    self::$username,
                    self::$password
                );

                // Activation du mode d'erreur : exceptions
                // Permet de détecter facilement les erreurs SQL
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            } catch (PDOException $e) {

                // En cas d'échec de connexion → arrêt du script
                die("Erreur connexion BDD : " . $e->getMessage());
            }
        }

        // Retourne toujours la même instance PDO
        return self::$pdo;
    }

    // ------------------------------------------------------------
    // Méthode statique : disconnect()
    // Permet de fermer proprement la connexion PDO
    // ------------------------------------------------------------
    public static function disconnect() {
        self::$pdo = null; // Libère l'objet PDO
    }
}
