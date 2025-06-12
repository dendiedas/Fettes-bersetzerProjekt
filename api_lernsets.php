<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Datenbankverbindung
$servername = "localhost";
$username = "root"; // Anpassen falls nötig
$password = ""; // Anpassen falls nötig
$dbname = "vocabulary_trainer"; // Geändert zu deiner Datenbank

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['error' => 'Datenbankverbindung fehlgeschlagen: ' . $e->getMessage()]);
    exit;
}

try {
    // Angepasst an deine Tabellenstruktur
    $stmt = $pdo->query("SELECT set_id as id, set_name as name, description as beschreibung, created_at FROM learning_sets ORDER BY created_at DESC");
    $lernsets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($lernsets);
} catch(PDOException $e) {
    echo json_encode(['error' => 'Fehler beim Laden der Lernsets: ' . $e->getMessage()]);
}
?>