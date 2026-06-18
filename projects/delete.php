<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../profil/login.php');
    exit;
}

require_once '../config/database.php';

if (isset($_POST['project_id'])) {

    $sql = "DELETE FROM projects WHERE id = :id";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'id' => $_POST['project_id']
    ]);
}

header('Location: ../dashboard.php');
exit;