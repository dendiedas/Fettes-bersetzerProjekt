<?php
require_once 'config/Database.php';

/**
 * Vocabulary Klasse
 * Verwaltet alle Vokabel-Operationen: Hinzufügen, Bearbeiten, Löschen, etc.
 */
class Vocabulary {
    private $conn;
    private $table_name = "vocabulary";
    
    // Eigenschaften einer Vokabel
    public $vocab_id;
    public $source_language_id;
    public $target_language_id;
    public $source_word;
    public $target_word;
    public $example_sentence;
    public $translation_example;
    public $difficulty_level;
    public $category_id;
    public $created_at;
    public $created_by;
    
    // Konstruktor mit Datenbankverbindung
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Neue Vokabel erstellen
     * @return boolean Erfolg oder Misserfolg
     */
    public function create() {
        // SQL-Abfrage zum Einfügen einer neuen Vokabel
        $query = "INSERT INTO " . $this->table_name . " 
                 (source_language_id, target_language_id, source_word, 
                  target_word, example_sentence, translation_example, 
                  difficulty_level, category_id, created_by) 
                 VALUES 
                 (:source_language_id, :target_language_id, :source_word, 
                  :target_word, :example_sentence, :translation_example, 
                  :difficulty_level, :category_id, :created_by)";
        
        // Vorbereiten der Abfrage
        $stmt = $this->conn->prepare($query);
        
        // Bereinigen der Eingaben
        $this->source_word = htmlspecialchars(strip_tags($this->source_word));
        $this->target_word = htmlspecialchars(strip_tags($this->target_word));
        $this->example_sentence = htmlspecialchars(strip_tags($this->example_sentence));
        $this->translation_example = htmlspecialchars(strip_tags($this->translation_example));
        
        // Parameter binden
        $stmt->bindParam(":source_language_id", $this->source_language_id);
        $stmt->bindParam(":target_language_id", $this->target_language_id);
        $stmt->bindParam(":source_word", $this->source_word);
        $stmt->bindParam(":target_word", $this->target_word);
        $stmt->bindParam(":example_sentence", $this->example_sentence);
        $stmt->bindParam(":translation_example", $this->translation_example);
        $stmt->bindParam(":difficulty_level", $this->difficulty_level);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":created_by", $this->created_by);
        
        // Abfrage ausführen
        if($stmt->execute()) {
            $this->vocab_id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Vokabel aktualisieren
     * @return boolean Erfolg oder Misserfolg
     */
    public function update() {
        // SQL-Abfrage zum Aktualisieren einer Vokabel
        $query = "UPDATE " . $this->table_name . " 
                 SET source_language_id = :source_language_id, 
                     target_language_id = :target_language_id, 
                     source_word = :source_word, 
                     target_word = :target_word, 
                     example_sentence = :example_sentence, 
                     translation_example = :translation_example, 
                     difficulty_level = :difficulty_level, 
                     category_id = :category_id 
                 WHERE vocab_id = :vocab_id";
        
        // Vorbereiten der Abfrage
        $stmt = $this->conn->prepare($query);
        
        // Bereinigen der Eingaben
        $this->source_word = htmlspecialchars(strip_tags($this->source_word));
        $this->target_word = htmlspecialchars(strip_tags($this->target_word));
        $this->example_sentence = htmlspecialchars(strip_tags($this->example_sentence));
        $this->translation_example = htmlspecialchars(strip_tags($this->translation_example));
        
        // Parameter binden
        $stmt->bindParam(":source_language_id", $this->source_language_id);
        $stmt->bindParam(":target_language_id", $this->target_language_id);
        $stmt->bindParam(":source_word", $this->source_word);
        $stmt->bindParam(":target_word", $this->target_word);
        $stmt->bindParam(":example_sentence", $this->example_sentence);
        $stmt->bindParam(":translation_example", $this->translation_example);
        $stmt->bindParam(":difficulty_level", $this->difficulty_level);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":vocab_id", $this->vocab_id);
        
        // Abfrage ausführen
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Vokabel löschen
     * @return boolean Erfolg oder Misserfolg
     */
    public function delete() {
        // SQL-Abfrage zum Löschen einer Vokabel
        $query = "DELETE FROM " . $this->table_name . " WHERE vocab_id = :vocab_id";
        
        // Vorbereiten der Abfrage
        $stmt = $this->conn->prepare($query);
        
        // Parameter binden
        $stmt->bindParam(":vocab_id", $this->vocab_id);
        
        // Abfrage ausführen
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Einzelne Vokabel lesen
     * @param int $vocab_id ID der zu lesenden Vokabel
     * @return boolean Erfolg oder Misserfolg
     */
    public function readOne($vocab_id) {
        // SQL-Abfrage zum Lesen einer Vokabel
        $query = "SELECT v.*, 
                    sl.language_name as source_language_name, 
                    tl.language_name as target_language_name,
                    c.category_name
                 FROM " . $this->table_name . " v
                 LEFT JOIN languages sl ON v.source_language_id = sl.language_id
                 LEFT JOIN languages tl ON v.target_language_id = tl.language_id
                 LEFT JOIN categories c ON v.category_id = c.category_id
                 WHERE v.vocab_id = :vocab_id";
        
        // Vorbereiten der Abfrage
        $stmt = $this->conn->prepare($query);
        
        // Parameter binden
        $stmt->bindParam(":vocab_id", $vocab_id);
        
        // Abfrage ausführen
        $stmt->execute();
        
        // Ergebnis abrufen
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            // Eigenschaften setzen
            $this->vocab_id = $row["vocab_id"];
            $this->source_language_id = $row["source_language_id"];
            $this->target_language_id = $row["target_language_id"];
            $this->source_word = $row["source_word"];
            $this->target_word = $row["target_word"];
            $this->example_sentence = $row["example_sentence"];
            $this->translation_example = $row["translation_example"];
            $this->difficulty_level = $row["difficulty_level"];
            $this->category_id = $row["category_id"];
            $this->created_at = $row["created_at"];
            $this->created_by = $row["created_by"];
            
            // Zusätzliche Informationen
            $this->source_language_name = $row["source_language_name"];
            $this->target_language_name = $row["target_language_name"];
            $this->category_name = $row["category_name"];
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Alle Vokabeln lesen mit optionaler Filterung und Paginierung
     * @param int $page Seitennummer
     * @param int $items_per_page Elemente pro Seite
     * @param array $filters Filteroptionen (source_language_id, target_language_id, category_id, created_by, search_term)
     * @return array Array mit Vokabeln
     */
    public function readAll($page = 1, $items_per_page = 10, $filters = []) {
        // Standardwerte für Paginierung
        $page = $page < 1 ? 1 : $page;
        $offset = ($page - 1) * $items_per_page;
        
        // Basis-SQL-Abfrage
        $query = "SELECT v.*, 
                    sl.language_name as source_language_name, 
                    tl.language_name as target_language_name,
                    c.category_name
                 FROM " . $this->table_name . " v
                 LEFT JOIN languages sl ON v.source_language_id = sl.language_id
                 LEFT JOIN languages tl ON v.target_language_id = tl.language_id
                 LEFT JOIN categories c ON v.category_id = c.category_id
                 WHERE 1=1";
        
        // Filter für Quellsprache
        if(isset($filters['source_language_id']) && !empty($filters['source_language_id'])) {
            $query .= " AND v.source_language_id = :source_language_id";
        }
        
        // Filter für Zielsprache
        if(isset($filters['target_language_id']) && !empty($filters['target_language_id'])) {
            $query .= " AND v.target_language_id = :target_language_id";
        }
        
        // Filter für Kategorie
        if(isset($filters['category_id']) && !empty($filters['category_id'])) {
            $query .= " AND v.category_id = :category_id";
        }
        
        // Filter für Ersteller
        if(isset($filters['created_by']) && !empty($filters['created_by'])) {
            $query .= " AND v.created_by = :created_by";
        }
        
        // Filter für Suchbegriff
        if(isset($filters['search_term']) && !empty($filters['search_term'])) {
            $query .= " AND (v.source_word LIKE :search_term OR v.target_word LIKE :search_term)";
        }
        
        // Sortierung und Paginierung
        $query .= " ORDER BY v.created_at DESC LIMIT :limit OFFSET :offset";
        
        // Vorbereiten der Abfrage
        $stmt = $this->conn->prepare($query);
        
        // Parameter für Filter binden
        if(isset($filters['source_language_id']) && !empty($filters['source_language_id'])) {
            $stmt->bindParam(":source_language_id", $filters['source_language_id']);
        }
        
        if(isset($filters['target_language_id']) && !empty($filters['target_language_id'])) {
            $stmt->bindParam(":target_language_id", $filters['target_language_id']);
        }
        
        if(isset($filters['category_id']) && !empty($filters['category_id'])) {
            $stmt->bindParam(":category_id", $filters['category_id']);
        }
        
        if(isset($filters['created_by']) && !empty($filters['created_by'])) {
            $stmt->bindParam(":created_by", $filters['created_by']);
        }
        
        if(isset($filters['search_term']) && !empty($filters['search_term'])) {
            $search_term = "%" . $filters['search_term'] . "%";
            $stmt->bindParam(":search_term", $search_term);
        }
        
        // Parameter für Paginierung binden
        $stmt->bindParam(":limit", $items_per_page, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        
        // Abfrage ausführen
        $stmt->execute();
        
        // Ergebnisse abrufen
        $vocabularies = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $vocabularies[] = $row;
        }
        
        return $vocabularies;
    }
    
    /**
     * Anzahl der Vokabeln mit optionaler Filterung
     * @param array $filters Filteroptionen (source_language_id, target_language_id, category_id, created_by, search_term)
     * @return int Anzahl der Vokabeln
     */
    public function count($filters = []) {
        // Basis-SQL-Abfrage
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " v WHERE 1=1";
        
        // Filter für Quellsprache
        if(isset($filters['source_language_id']) && !empty($filters['source_language_id'])) {
            $query .= " AND v.source_language_id = :source_language_id";
        }
        
        // Filter für Zielsprache
        if(isset($filters['target_language_id']) && !empty($filters['target_language_id'])) {
            $query .= " AND v.target_language_id = :target_language_id";
        }
        
        // Filter für Kategorie
        if(isset($filters['category_id']) && !empty($filters['category_id'])) {
            $query .= " AND v.category_id = :category_id";
        }
        
        // Filter für Ersteller
        if(isset($filters['created_by']) && !empty($filters['created_by'])) {
            $query .= " AND v.created_by = :created_by";
        }
        
        // Filter für Suchbegriff
        if(isset($filters['search_term']) && !empty($filters['search_term'])) {
            $query .= " AND (v.source_word LIKE :search_term OR v.target_word LIKE :search_term)";
        }
        
        // Vorbereiten der Abfrage
        $stmt = $this->conn->prepare($query);
        
        // Parameter für Filter binden
        if(isset($filters['source_language_id']) && !empty($filters['source_language_id'])) {
            $stmt->bindParam(":source_language_id", $filters['source_language_id']);
        }
        
        if(isset($filters['target_language_id']) && !empty($filters['target_language_id'])) {
            $stmt->bindParam(":target_language_id", $filters['target_language_id']);
        }
        
        if(isset($filters['category_id']) && !empty($filters['category_id'])) {
            $stmt->bindParam(":category_id", $filters['category_id']);
        }
        
        if(isset($filters['created_by']) && !empty($filters['created_by'])) {
            $stmt->bindParam(":created_by", $filters['created_by']);
        }
        
        if(isset($filters['search_term']) && !empty($filters['search_term'])) {
            $search_term = "%" . $filters['search_term'] . "%";
            $stmt->bindParam(":search_term", $search_term);
        }
        
        // Abfrage ausführen
        $stmt->execute();
        
        // Ergebnis abrufen
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    }
    
    /**
     * Vokabeln für eine Lernsitzung abrufen
     * @param int $user_id ID des Benutzers
     * @param int $limit Anzahl der Vokabeln
     * @param array $filters Filteroptionen (source_language_id, target_language_id, category_id)
     * @return array Array mit Vokabeln für die Lernsitzung
     */
    public function getForLearningSession($user_id, $limit = 10, $filters = []) {
        // Basis-SQL-Abfrage 
        // Wir holen Vokabeln basierend auf:
        // 1. Vokabeln, die bald wiederholt werden müssen (Spaced Repetition)
        // 2. Vokabeln, die noch nicht gelernt wurden
        // 3. Vokabeln mit niedrigem Mastery-Level
        $query = "SELECT v.*, 
                    sl.language_name as source_language_name, 
                    tl.language_name as target_language_name,
                    c.category_name,
                    IFNULL(up.mastery_level, -1) as mastery_level,
                    IFNULL(up.next_review, '1970-01-01') as next_review
                 FROM " . $this->table_name . " v
                 LEFT JOIN languages sl ON v.source_language_id = sl.language_id
                 LEFT JOIN languages tl ON v.target_language_id = tl.language_id
                 LEFT JOIN categories c ON v.category_id = c.category_id
                 LEFT JOIN user_progress up ON v.vocab_id = up.vocab_id AND up.user_id = :user_id
                 WHERE 1=1";
        
        // Filter für Quellsprache
        if(isset($filters['source_language_id']) && !empty($filters['source_language_id'])) {
            $query .= " AND v.source_language_id = :source_language_id";
        }
        
        // Filter für Zielsprache
        if(isset($filters['target_language_id']) && !empty($filters['target_language_id'])) {
            $query .= " AND v.target_language_id = :target_language_id";
        }
        
        // Filter für Kategorie
        if(isset($filters['category_id']) && !empty($filters['category_id'])) {
            $query .= " AND v.category_id = :category_id";
        }
        
        // Vokabeln priorisieren, die:
        // 1. Zur Wiederholung fällig sind (next_review <= CURRENT_TIMESTAMP)
        // 2. Noch nie gelernt wurden (mastery_level = -1)
        // 3. Niedrigen Mastery-Level haben
        $query .= " ORDER BY 
                    CASE 
                        WHEN up.next_review <= CURRENT_TIMESTAMP AND up.next_review IS NOT NULL THEN 0
                        WHEN up.mastery_level IS NULL OR up.mastery_level = -1 THEN 1
                        ELSE 2
                    END,
                    IFNULL(up.mastery_level, -1) ASC,
                    RAND()
                 LIMIT :limit";
        
        // Vorbereiten der Abfrage
        $stmt = $this->conn->prepare($query);
        
        // Parameter binden
        $stmt->bindParam(":user_id", $user_id);
        
        // Parameter für Filter binden
        if(isset($filters['source_language_id']) && !empty($filters['source_language_id'])) {
            $stmt->bindParam(":source_language_id", $filters['source_language_id']);
        }
        
        if(isset($filters['target_language_id']) && !empty($filters['target_language_id'])) {
            $stmt->bindParam(":target_language_id", $filters['target_language_id']);
        }
        
        if(isset($filters['category_id']) && !empty($filters['category_id'])) {
            $stmt->bindParam(":category_id", $filters['category_id']);
        }
        
        // Parameter für Limit binden
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        
        // Abfrage ausführen
        $stmt->execute();
        
        // Ergebnisse abrufen
        $vocabularies = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $vocabularies[] = $row;
        }
        
        return $vocabularies;
    }
    
    /**
     * Lernfortschritt einer Vokabel aktualisieren
     * @param int $user_id Benutzer-ID
     * @param int $vocab_id Vokabel-ID
     * @param int $correct War die Antwort korrekt (1) oder falsch (0)
     * @return boolean Erfolg oder Misserfolg
     */
    public function updateProgress($user_id, $vocab_id, $correct) {
        // Prüfen, ob bereits ein Fortschrittseintrag existiert
        $query = "SELECT * FROM user_progress WHERE user_id = :user_id AND vocab_id = :vocab_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":vocab_id", $vocab_id);
        $stmt->execute();
        
        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Bestehender Eintrag gefunden
            $mastery_level = $row['mastery_level'];
            $times_practiced = $row['times_practiced'] + 1;
            $times_correct = $row['times_correct'] + ($correct ? 1 : 0);
            
            // Mastery Level anpassen (0-5)
            if($correct) {
                $mastery_level = min(5, $mastery_level + 1);
            } else {
                $mastery_level = max(0, $mastery_level - 1);
            }
            
            // Nächsten Überprüfungszeitpunkt berechnen (Spaced Repetition)
            $next_review_days = [1, 2, 4, 7, 14, 30]; // Tage bis zur nächsten Wiederholung
            $days_until_next = $next_review_days[$mastery_level];
            $next_review = date('Y-m-d H:i:s', strtotime("+{$days_until_next} days"));
            
            // Fortschritt aktualisieren
            $query = "UPDATE user_progress 
                     SET mastery_level = :mastery_level,
                         last_practiced = CURRENT_TIMESTAMP,
                         next_review = :next_review,
                         times_practiced = :times_practiced,
                         times_correct = :times_correct 
                     WHERE user_id = :user_id AND vocab_id = :vocab_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":mastery_level", $mastery_level);
            $stmt->bindParam(":next_review", $next_review);
            $stmt->bindParam(":times_practiced", $times_practiced);
            $stmt->bindParam(":times_correct", $times_correct);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":vocab_id", $vocab_id);
            
        } else {
            // Neuen Fortschrittseintrag erstellen
            $mastery_level = $correct ? 1 : 0;
            $times_practiced = 1;
            $times_correct = $correct ? 1 : 0;
            
            // Nächsten Überprüfungszeitpunkt berechnen
            $next_review_days = [1, 2, 4, 7, 14, 30]; // Tage bis zur nächsten Wiederholung
            $days_until_next = $next_review_days[$mastery_level];
            $next_review = date('Y-m-d H:i:s', strtotime("+{$days_until_next} days"));
            
            $query = "INSERT INTO user_progress
                     (user_id, vocab_id, mastery_level, last_practiced, next_review, times_practiced, times_correct)
                     VALUES
                     (:user_id, :vocab_id, :mastery_level, CURRENT_TIMESTAMP, :next_review, :times_practiced, :times_correct)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":vocab_id", $vocab_id);
            $stmt->bindParam(":mastery_level", $mastery_level);
            $stmt->bindParam(":next_review", $next_review);
            $stmt->bindParam(":times_practiced", $times_practiced);
            $stmt->bindParam(":times_correct", $times_correct);
        }
        
        // Abfrage ausführen
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Vokabeln importieren (z.B. aus CSV)
     * @param array $vocabularies Array mit Vokabeln
     * @param int $user_id Benutzer-ID des Importierenden
     * @return array Ergebnis des Imports (success, imported, failed)
     */
    public function importVocabularies($vocabularies, $user_id) {
        $imported = 0;
        $failed = 0;
        
        // Transaktion starten
        $this->conn->beginTransaction();
        
        try {
            foreach($vocabularies as $vocab) {
                // Pflichtfelder prüfen
                if(empty($vocab['source_word']) || empty($vocab['target_word']) || 
                   empty($vocab['source_language_id']) || empty($vocab['target_language_id'])) {
                    $failed++;
                    continue;
                }
                
                $this->source_language_id = $vocab['source_language_id'];
                $this->target_language_id = $vocab['target_language_id'];
                $this->source_word = $vocab['source_word'];
                $this->target_word = $vocab['target_word'];
                $this->example_sentence = $vocab['example_sentence'] ?? null;
                $this->translation_example = $vocab['translation_example'] ?? null;
                $this->difficulty_level = $vocab['difficulty_level'] ?? 1;
                $this->category_id = $vocab['category_id'] ?? null;
                $this->created_by = $user_id;
                
                if($this->create()) {
                    $imported++;
                } else {
                    $failed++;
                }
            }
            
            // Transaktion bestätigen
            $this->conn->commit();
            
            return [
                'success' => true,
                'imported' => $imported,
                'failed' => $failed
            ];
            
        } catch(Exception $e) {
            // Transaktion zurückrollen bei Fehler
            $this->conn->rollBack();
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'imported' => $imported,
                'failed' => $failed
            ];
        }
    }
}