<?php
// API-Endpoint für Lernsets
header('Content-Type: application/json');

// Datenbankverbindung herstellen
require_once 'Database.php';
require_once 'User.php';
require_once 'Vocabulary.php';

// Session starten
session_start();

// Prüfen, ob Benutzer angemeldet ist (optional, kann auch für öffentliche Sets entfernt werden)
$logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

// Datenbankverbindung herstellen
$database = new Database();
$db = $database->getConnection();

// Vocabulary-Objekt erstellen
$vocabulary = new Vocabulary($db);

// Hilfsfunktion um Kategorien/Sammlungen abzurufen
function getCategories($db) {
    $query = "SELECT * FROM categories ORDER BY category_name";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $categories = [];
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $categories[] = [
            'id' => $row['category_id'],
            'name' => $row['category_name'],
            'beschreibung' => $row['description'] ?? 'Lerne Vokabeln zu diesem Thema'
        ];
    }
    
    // Wenn keine Kategorien existieren, füge Standard-Lernsets hinzu
    if(empty($categories)) {
        $categories = [
            [
                'id' => 1,
                'name' => 'Englisch Grundlagen',
                'beschreibung' => 'Lerne grundlegende englische Vokabeln'
            ],
            [
                'id' => 2,
                'name' => 'Spanisch für Anfänger',
                'beschreibung' => 'Spanische Vokabeln für den Einstieg'
            ],
            [
                'id' => 3,
                'name' => 'Französisch Alltag',
                'beschreibung' => 'Französische Vokabeln für den täglichen Gebrauch'
            ],
            [
                'id' => 4,
                'name' => 'Italienisch Urlaub',
                'beschreibung' => 'Wichtige italienische Begriffe für den Urlaub'
            ]
        ];
    }
    
    return $categories;
}

// Lernsets abrufen und als JSON zurückgeben
$lernsets = getCategories($db);
echo json_encode($lernsets);
?>