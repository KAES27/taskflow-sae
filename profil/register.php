<?php

require_once '../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT id FROM users WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'email' => $email
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $error = "Cet email est déjà utilisé.";
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (first_name, last_name, email, password)
                VALUES (:first_name, :last_name, :email, :password)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'password' => $hashedPassword
        ]);

        header('Location: login.php');
        exit;
    }
}




?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <title>Inscription</title>
</head>
<body>

    

    <form method="POST">
        <div class="inscription_card">
            <h1>Inscription</h1>

            <div class="input_group">
                <i class="bi bi-person"></i>
                <input type="text" name="first_name" placeholder="Prénom" required>
            </div>

            <div class="input_group">
                <i class="bi bi-person-badge"></i>
                <input type="text" name="last_name" placeholder="Nom" required>
            </div>

            <div class="input_group">
                <i class="bi bi-envelope"></i>
                <input type="email" name="email" placeholder="Email" required>
            </div>

            <div class="input_group">
                <i class="bi bi-lock"></i>
                <input type="password" name="password" placeholder="Mot de passe" required>
            </div>

            <button type="submit">S'inscrire</button>
            <p>Avez vous déjà un compte ? <a href="login.php">connexion</a></p>
        </div>
       
    </form>

</body>
</html>