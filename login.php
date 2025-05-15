<?php
require_once 'Database.php';
require_once 'User.php';

session_start();
$db = (new Database())->getConnection();
$user = new User($db);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usernameOrEmail = $_POST["username_or_email"];
    $password = $_POST["password"];

    if ($user->login($usernameOrEmail, $password)) {
        echo "success";
    } else {
        echo "Login fehlgeschlagen.";
    }
}
?>
