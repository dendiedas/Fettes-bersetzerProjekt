<?php
/**
 * Datenbankverbindungsklasse
 * Sorgt für eine einheitliche Verbindung zur Datenbank im gesamten Projekt
 */
class Database {
    private $host = "localhost";
    private $db_name = "vocabulary_trainer";
    private $username = "root";
    private $password = "";
    public $conn;
    
    // Datenbankverbindung herstellen
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4", 
                                  $this->username, 
                                  $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch(PDOException $e) {
            echo "Verbindungsfehler: " . $e->getMessage();
        }
        
        return $this->conn;
    }
}