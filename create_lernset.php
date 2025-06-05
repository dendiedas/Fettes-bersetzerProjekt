<?php
require_once 'Database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo 'Nicht angemeldet';
  exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$name = $data['name'] ?? '';
$beschreibung = $data['beschreibung'] ?? '';

if (trim($name) === '') {
  echo 'Name fehlt';
  exit;
}

$db = (new Database())->getConnection();

$stmt = $db->prepare("INSERT INTO learning_sets (set_name, description, created_by, is_public) 
                      VALUES (:name, :beschreibung, :uid, 1)");
$stmt->bindParam(":name", $name);
$stmt->bindParam(":beschreibung", $beschreibung);
$stmt->bindParam(":uid", $_SESSION["user_id"]);

if ($stmt->execute()) {
  echo 'success';
} else {
  echo 'Fehler beim Speichern';
}
