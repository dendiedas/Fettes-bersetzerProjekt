<?php
require_once 'Database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['error' => 'Nicht angemeldet']);
  exit;
}

if (!isset($_GET['setId'])) {
  echo json_encode(['error' => 'Keine Set-ID angegeben']);
  exit;
}

$db = (new Database())->getConnection();
$setId = $_GET['setId'];
$userId = $_SESSION['user_id'];

// Lernset holen
$stmt = $db->prepare("SELECT set_name AS name FROM learning_sets 
                      WHERE set_id = :id AND (created_by = :uid OR is_public = 1)");
$stmt->bindParam(":id", $setId);
$stmt->bindParam(":uid", $userId);
$stmt->execute();

$set = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$set) {
  echo json_encode(['error' => 'Lernset nicht gefunden oder kein Zugriff']);
  exit;
}

// Vokabeln holen
$stmt = $db->prepare("SELECT source_word, target_word, example_sentence 
                      FROM vocabulary 
                      WHERE category_id IS NOT NULL AND vocab_id IN (
                        SELECT vocab_id FROM set_vocabulary WHERE set_id = :setId
                      )");
$stmt->bindParam(":setId", $setId);
$stmt->execute();

$set['vokabeln'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($set);
