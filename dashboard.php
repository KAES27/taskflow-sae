<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: profil/login.php');
    exit;
}

require_once 'config/database.php';

$userId = $_SESSION['user_id'];

$sql = "SELECT first_name, last_name FROM users WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'id' => $userId
]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT * FROM projects WHERE owner_id = :owner_id";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'owner_id' => $userId
]);

$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <title>Gestion de vos projets</title>
</head>
<body>
    <nav class="dashboard_navbar">
        <div class="navbar_left">
            <h2>Tableau de bord</h2>
        </div>

        <div class="navbar_center">
            <p>Bienvenue <?= htmlspecialchars($user['first_name']) ?> <?= htmlspecialchars($user['last_name']) ?></p>
        </div>

        <div class="navbar_right">
            <a href="profil/logout.php" class="logout_link">Se déconnecter</a>
        </div>
    </nav>

<main class="dashboard_container">
    <section class="dashboard_header">
        <div>
            <h1>Vos projets</h1>
            <p>Retrouvez ici tous vos projets collaboratifs.</p>
        </div>

        <a href="projects/create.php" class="add_project_btn">
            <i class="bi bi-plus-lg"></i>
            Créer un projet
        </a>
    </section>

    <section class="projects_area">
       <?php if (empty($projects)): ?>
            <p class="empty_message">Aucun projet pour le moment.</p>
        <?php else: ?>
          <?php foreach($projects as $project): ?>
                <a href="projects/show.php?id=<?= $project['id'] ?>" class="project_card">
                    <h3><?= htmlspecialchars($project['title']) ?></h3>
                    <p><?= htmlspecialchars($project['description']) ?></p>
                </a>
            <?php endforeach; ?>
       
        <?php endif; ?>
            
    </section>
</main>

</body>
</html>