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

if (!$input || !isset($input['name'])) {
    echo "Ungültige Daten";
    exit;
}

$name = trim($input['name']);
$beschreibung = isset($input['beschreibung']) ? trim($input['beschreibung']) : '';

if (empty($name)) {
    echo "Name darf nicht leer sein";
    exit;
}

try {
    // Für Testzwecke verwenden wir user_id = 1 (du kannst später ein Login-System hinzufügen)
    $stmt = $pdo->prepare("INSERT INTO learning_sets (set_name, description, created_by, created_at) VALUES (?, ?, 1, NOW())");
    $stmt->execute([$name, $beschreibung]);
    echo "success";
} catch(PDOException $e) {
    echo "Fehler beim Speichern: " . $e->getMessage();
}
?>