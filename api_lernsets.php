<?php
require_once 'Database.php';
session_start();

if (!isset($_SESSION["user_id"])) {
  http_response_code(401);
  echo json_encode(["error" => "Nicht angemeldet"]);
  exit;
}

$db = (new Database())->getConnection();
$user_id = $_SESSION["user_id"];

$stmt = $db->prepare("SELECT set_id AS id, set_name AS name, description AS beschreibung 
                      FROM learning_sets 
                      WHERE created_by = :uid OR is_public = 1
                      ORDER BY created_at DESC");

$stmt->bindParam(":uid", $user_id, PDO::PARAM_INT);
$stmt->execute();

$sets = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($sets);
?>
