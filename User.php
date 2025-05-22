<?php
// Verbindung zur Datenbank, Session starten und Fehlerbehandlung
require_once 'Database.php';
session_start();

/**
 * Benutzerklasse
 * Verwaltet alle Benutzeraktionen: Registrierung, Login, Profil, etc.
 */
class User {
    private $conn;
    private $table_name = "users";
    
    // Eigenschaften des Benutzers
    public $user_id;
    public $username;
    public $email;
    public $password;
    public $created_at;
    public $last_login;
    public $is_active;
    
    // Konstruktor mit Datenbankverbindung
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Neuen Benutzer registrieren
     * @return boolean Erfolg oder Misserfolg
     */
    public function register() {
        // SQL-Abfrage zum Einfügen eines neuen Benutzers
        $query = "INSERT INTO " . $this->table_name . " 
                 (username, email, password_hash) 
                 VALUES (:username, :email, :password)";
        
        // Vorbereiten der Abfrage
        $stmt = $this->conn->prepare($query);
        
        // Bereinigen der Eingaben
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        
        // Passwort hashen
        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);
        
        // Parameter binden
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $password_hash);
        
        // Abfrage ausführen
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Benutzer einloggen
     * @param string $username_or_email Benutzername oder E-Mail
     * @param string $password Passwort
     * @return boolean Erfolg oder Misserfolg
     */

    public function login($username_or_email, $password) {
    $query = "SELECT user_id, username, email, password_hash 
              FROM " . $this->table_name . " 
              WHERE username = :username OR email = :email";

    $stmt = $this->conn->prepare($query);
    $stmt->bindValue(":username", $username_or_email, PDO::PARAM_STR);
    $stmt->bindValue(":email", $username_or_email, PDO::PARAM_STR);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && password_verify($password, $row["password_hash"])) {
        $this->user_id = $row["user_id"];
        $this->username = $row["username"];
        $this->email = $row["email"];

        $this->updateLastLogin();

        $_SESSION["logged_in"] = true;
        $_SESSION["user_id"] = $this->user_id;
        $_SESSION["username"] = $this->username;

        return true;
    }

    return false;
}
    
    /**
     * Letzten Login-Zeitpunkt aktualisieren
     */
    private function updateLastLogin() {
    if (!$this->user_id) {
        error_log("updateLastLogin() aufgerufen ohne user_id!");
        return;
    }

    $query = "UPDATE " . $this->table_name . " 
              SET last_login = CURRENT_TIMESTAMP 
              WHERE user_id = :user_id";

    $stmt = $this->conn->prepare($query);
    $stmt->execute([
        ":user_id" => $this->user_id
    ]);
}
    /**
     * Prüfen, ob Benutzername bereits existiert
     * @param string $username Zu prüfender Benutzername
     * @return boolean true wenn existiert, false wenn nicht
     */
    public function usernameExists($username) {
        $query = "SELECT user_id FROM " . $this->table_name . " 
                 WHERE username = :username";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Prüfen, ob E-Mail bereits existiert
     * @param string $email Zu prüfende E-Mail
     * @return boolean true wenn existiert, false wenn nicht
     */
    public function emailExists($email) {
        $query = "SELECT user_id FROM " . $this->table_name . " 
                 WHERE email = :email";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    /**
     * Benutzer ausloggen
     */
    public function logout() {
        // Session-Variablen löschen
        $_SESSION = array();
        
        // Session zerstören
        session_destroy();
        
        // Sicherstellen, dass Cookies gelöscht werden
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
    }
    
    /**
     * Benutzerprofil aktualisieren
     * @return boolean Erfolg oder Misserfolg
     */
    public function updateProfile() {
        // SQL-Abfrage zum Aktualisieren des Benutzerprofils
        $query = "UPDATE " . $this->table_name . " 
                 SET username = :username, email = :email 
                 WHERE user_id = :user_id";
        
        // Vorbereiten der Abfrage
        $stmt = $this->conn->prepare($query);
        
        // Bereinigen der Eingaben
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        
        // Parameter binden
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":user_id", $this->user_id);
        
        // Abfrage ausführen
        if($stmt->execute()) {
            // Session-Variablen aktualisieren
            $_SESSION["username"] = $this->username;
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Passwort ändern
     * @param string $old_password Altes Passwort
     * @param string $new_password Neues Passwort
     * @return boolean Erfolg oder Misserfolg
     */
    public function changePassword($old_password, $new_password) {
        // Altes Passwort prüfen
        $query = "SELECT password_hash FROM " . $this->table_name . " 
                 WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Prüfen, ob altes Passwort korrekt ist
        if(password_verify($old_password, $row["password_hash"])) {
            // Neues Passwort hashen
            $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);
            
            // SQL-Abfrage zum Aktualisieren des Passworts
            $query = "UPDATE " . $this->table_name . " 
                     SET password_hash = :password 
                     WHERE user_id = :user_id";
            
            // Vorbereiten der Abfrage
            $stmt = $this->conn->prepare($query);
            
            // Parameter binden
            $stmt->bindParam(":password", $new_password_hash);
            $stmt->bindParam(":user_id", $this->user_id);
            
            // Abfrage ausführen
            if($stmt->execute()) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Benutzer anhand der ID laden
     * @param int $user_id ID des zu ladenden Benutzers
     * @return boolean Erfolg oder Misserfolg
     */
    public function readOne($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                 WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->user_id = $row["user_id"];
            $this->username = $row["username"];
            $this->email = $row["email"];
            $this->created_at = $row["created_at"];
            $this->last_login = $row["last_login"];
            $this->is_active = $row["is_active"];
            
            return true;
        }
        
        return false;
    }
}