<?php
require_once 'Database.php';
require_once 'User.php';

$db = (new Database())->getConnection();
$user = new User($db);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user->username = $_POST["username"] ?? '';
    $user->email = $_POST["email"] ?? '';
    $user->password = $_POST["password"] ?? '';

    if ($user->usernameExists($user->username)) {
        echo "Benutzername existiert bereits.";
        exit;
    }

    if ($user->emailExists($user->email)) {
        echo "E-Mail existiert bereits.";
        exit;
    }

    if ($user->register()) {
        echo "success";
    } else {
        echo "Fehler bei der Registrierung.";
    }
}
?>
