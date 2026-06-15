<?php

session_start();

require_once '../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT id,password FROM users WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'email' => $email
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);
     if ($user && password_verify($password, $user['password'])) {
        
        $_SESSION['user_id'] = $user['id'];
        header('Location: ../dashboard.php');
        exit;
    } else {
        $error = "Email ou mot de passe incorrect.";
    }

        
    }





?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <title>Connexion</title>
</head>
<body>

    

    <form method="POST">
        <div class="inscription_card">
            <h1>Connexion</h1>
            <?php if (!empty($error)): ?>
                <p class="error_message"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <div class="input_group">
                <i class="bi bi-envelope"></i>
                <input type="email" name="email" placeholder="Email" required>
            </div>

            <div class="input_group">
                <i class="bi bi-lock"></i>
                <input type="password" name="password" placeholder="Mot de passe" required>
            </div>

            <button type="submit">Se connecter</button>
           
        </div>
       
    </form>

</body>
</html>