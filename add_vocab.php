<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Datenbankverbindung
$servername = "localhost";
$username = "root"; // Anpassen falls nötig
$password = ""; // Anpassen falls nötig
$dbname = "vocabulary_trainer"; // Geändert zu deiner Datenbank

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Datenbankverbindung fehlgeschlagen: " . $e->getMessage();
    exit;
}

// JSON-Daten lesen
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['setId']) || !isset($input['source_word']) || !isset($input['target_word'])) {
    echo "Ungültige Daten";
    exit;
}

$setId = (int)$input['setId'];
$sourceWord = trim($input['source_word']);
$targetWord = trim($input['target_word']);
$exampleSentence = isset($input['example_sentence']) ? trim($input['example_sentence']) : '';

if ($setId <= 0 || empty($sourceWord) || empty($targetWord)) {
    echo "Alle Pflichtfelder müssen ausgefüllt werden";
    exit;
}

try {
    // Prüfen ob das Lernset existiert
    $stmt = $pdo->prepare("SELECT set_id FROM learning_sets WHERE set_id = ?");
    $stmt->execute([$setId]);
    if (!$stmt->fetch()) {
        echo "Lernset nicht gefunden";
        exit;
    }
    
    // Standard-Sprach-IDs abrufen (Deutsch=1, Englisch=2)
    $stmt = $pdo->prepare("SELECT language_id FROM languages WHERE language_code = 'de'");
    $stmt->execute();
    $deLanguageId = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT language_id FROM languages WHERE language_code = 'en'");
    $stmt->execute();
    $enLanguageId = $stmt->fetchColumn();
    
    if (!$deLanguageId || !$enLanguageId) {
        echo "Sprachen nicht in der Datenbank gefunden";
        exit;
    }
    
    // Transaktion starten
    $pdo->beginTransaction();
    
    // 1. Vokabel in vocabulary-Tabelle einfügen
    $stmt = $pdo->prepare("
        INSERT INTO vocabulary (source_language_id, target_language_id, source_word, target_word, example_sentence, created_by, created_at) 
        VALUES (?, ?, ?, ?, ?, 1, NOW())
    ");
    $stmt->execute([$deLanguageId, $enLanguageId, $sourceWord, $targetWord, $exampleSentence]);
    
    // ID der neu erstellten Vokabel
    $vocabId = $pdo->lastInsertId();
    
    // 2. Verknüpfung in set_vocabulary-Tabelle erstellen
    $stmt = $pdo->prepare("INSERT INTO set_vocabulary (set_id, vocab_id, added_at) VALUES (?, ?, NOW())");
    $stmt->execute([$setId, $vocabId]);
    
    // Transaktion bestätigen
    $pdo->commit();
    
    echo json_encode(["status" => "success"]);
} catch(PDOException $e) {
    // Transaktion rückgängig machen bei Fehler
    $pdo->rollBack();
    echo "Fehler beim Speichern: " . $e->getMessage();
}
?>