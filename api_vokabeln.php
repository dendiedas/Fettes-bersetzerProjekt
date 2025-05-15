<?php
// API-Endpoint für Vokabeln eines Lernsets
header('Content-Type: application/json');

// Datenbankverbindung herstellen
require_once 'Database.php';
require_once 'User.php';
require_once 'Vocabulary.php';

// Session starten
session_start();

// Datenbankverbindung herstellen
$database = new Database();
$db = $database->getConnection();

// Vocabulary-Objekt erstellen
$vocabulary = new Vocabulary($db);

// Lernset-ID aus der URL abrufen
$set_id = isset($_GET['setId']) ? (int)$_GET['setId'] : 0;

if ($set_id <= 0) {
    echo json_encode([
        'error' => 'Ungültige Lernset-ID'
    ]);
    exit;
}

// Benutzer-ID abrufen (wenn angemeldet)
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// Filter festlegen (Kategorie = Lernset-ID)
$filters = [
    'category_id' => $set_id
];

// Anzahl der zu ladenden Vokabeln
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

try {
    // Wenn Benutzer angemeldet ist, Vokabeln für Lernsitzung abrufen
    if ($user_id > 0) {
        $vokabeln = $vocabulary->getForLearningSession($user_id, $limit, $filters);
    } else {
        // Ansonsten einfach die ersten X Vokabeln der Kategorie abrufen
        $vokabeln = $vocabulary->readAll(1, $limit, $filters);
    }
    
    // Wenn keine Vokabeln gefunden wurden, Beispieldaten zurückgeben
    if (empty($vokabeln)) {
        // Beispiel-Vokabeln je nach Kategorie zurückgeben
        switch ($set_id) {
            case 1: // Englisch Grundlagen
                $vokabeln = [
                    ['source_word' => 'Apfel', 'target_word' => 'Apple'],
                    ['source_word' => 'Buch', 'target_word' => 'Book'],
                    ['source_word' => 'Stuhl', 'target_word' => 'Chair'],
                    ['source_word' => 'Fenster', 'target_word' => 'Window'],
                    ['source_word' => 'Tisch', 'target_word' => 'Table']
                ];
                break;
            case 2: // Spanisch für Anfänger
                $vokabeln = [
                    ['source_word' => 'Hallo', 'target_word' => 'Hola'],
                    ['source_word' => 'Danke', 'target_word' => 'Gracias'],
                    ['source_word' => 'Bitte', 'target_word' => 'Por favor'],
                    ['source_word' => 'Tschüss', 'target_word' => 'Adiós'],
                    ['source_word' => 'Freund', 'target_word' => 'Amigo']
                ];
                break;
            default:
                // Standardvokabeln für andere Kategorien
                $vokabeln = [
                    ['source_word' => 'Wort 1', 'target_word' => 'Word 1'],
                    ['source_word' => 'Wort 2', 'target_word' => 'Word 2'],
                    ['source_word' => 'Wort 3', 'target_word' => 'Word 3'],
                    ['source_word' => 'Wort 4', 'target_word' => 'Word 4'],
                    ['source_word' => 'Wort 5', 'target_word' => 'Word 5']
                ];
        }
    }
    
    // Vereinfachtes Format für die Lernanwendung
    $formatted_vokabeln = [];
    foreach ($vokabeln as $vokabel) {
        $formatted_vokabeln[] = [
            'deutsch' => $vokabel['source_word'] ?? $vokabel['deutsch'],
            'englisch' => $vokabel['target_word'] ?? $vokabel['englisch']
        ];
    }
    
    echo json_encode($formatted_vokabeln);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Ein Fehler ist aufgetreten: ' . $e->getMessage()
    ]);
}
?>