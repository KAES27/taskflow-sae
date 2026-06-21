<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../profil/login.php');
    exit;
}

require_once '../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    if (!empty($title)) {

        $sql = "INSERT INTO projects (title, description, owner_id)
                VALUES (:title, :description, :owner_id)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            'title' => $title,
            'description' => $description,
            'owner_id' => $_SESSION['user_id']
        ]);

        header('Location: ../dashboard.php');
        exit;
    } else {
        $error = "Le titre est obligatoire.";
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="../assets/css/style.css">
        <title>Créer un projet</title>
    </head>

    <body>

        <div class="project_card_create">

            <h1 >Créer un projet</h1>

            <?php if (!empty($error)): ?>
                <p class="error_message"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form method="POST">
                
                    <div class="input_group_title_projet">
                        <input type="text"
                            name="title"
                            placeholder="Nom du projet"
                            required>
                    </div>

                    <div class="input_group_description">
                        <textarea name="description"
                                placeholder="Description du projet"
                                rows="5"></textarea>
                    </div>

                    <button type="submit" class="add_project_btn">
                   
                        Créer le projet
                    </button>
            </form>

        </div>

    </body>
</html>