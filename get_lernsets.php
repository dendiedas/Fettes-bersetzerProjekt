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

$setId = isset($_GET['setId']) ? (int)$_GET['setId'] : 0;

if ($setId <= 0) {
    echo json_encode(['error' => 'Ungültige Lernset-ID']);
    exit;
}

try {
    // Lernset-Informationen abrufen (angepasst an deine Struktur)
    $stmt = $pdo->prepare("SELECT set_id as id, set_name as name, description as beschreibung FROM learning_sets WHERE set_id = ?");
    $stmt->execute([$setId]);
    $lernset = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$lernset) {
        echo json_encode(['error' => 'Lernset nicht gefunden']);
        exit;
    }
    
    // Vokabeln für dieses Lernset abrufen (über die Verknüpfungstabelle)
    $stmt = $pdo->prepare("
        SELECT v.vocab_id as id, v.source_word, v.target_word, v.example_sentence 
        FROM vocabulary v 
        INNER JOIN set_vocabulary sv ON v.vocab_id = sv.vocab_id 
        WHERE sv.set_id = ? 
        ORDER BY sv.added_at DESC
    ");
    $stmt->execute([$setId]);
    $vokabeln = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response = [
        'id' => $lernset['id'],
        'name' => $lernset['name'],
        'beschreibung' => $lernset['beschreibung'],
        'vokabeln' => $vokabeln
    ];
    
    echo json_encode($response);
    
} catch(PDOException $e) {
    echo json_encode(['error' => 'Fehler beim Laden der Daten: ' . $e->getMessage()]);
}
?>