<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../profil/login.php');
    exit;
}



require_once '../config/database.php';

if (isset($_POST['delete_task_id'])) {

    $sql = "DELETE FROM tasks
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'id' => $_POST['delete_task_id']
    ]);
}

if (isset($_POST['complete_task_id'])) {

    $sql = "UPDATE tasks
            SET is_done = 1
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'id' => $_POST['complete_task_id']
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_title'])) {

    $taskTitle = trim($_POST['task_title']);
    $taskDescription = trim($_POST['task_description']);
    $priority = $_POST['priority'];
    $dueDate = !empty($_POST['due_date'])
        ? $_POST['due_date']
        : null;

    $sql = "INSERT INTO tasks (
                project_id,
                title,
                description,
                priority,
                due_date
            )
            VALUES (
                :project_id,
                :title,
                :description,
                :priority,
                :due_date
            )";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'project_id' => $_GET['id'],
        'title' => $taskTitle,
        'description' => $taskDescription,
        'priority' => $priority,
        'due_date' => $dueDate
    ]);
}

if (!isset($_GET['id'])) {
    die("Projet introuvable.");
}

$projectId = $_GET['id'];

$sql = "SELECT * FROM projects WHERE id = :id";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'id' => $projectId
]);

$project = $stmt->fetch(PDO::FETCH_ASSOC);

$sql = "SELECT * FROM tasks
        WHERE project_id = :project_id
        ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    'project_id' => $projectId
]);

$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$project) {
    die("Projet introuvable.");
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title><?= htmlspecialchars($project['title']) ?></title>
</head>
<hr>

<h2>Ajouter une tâche</h2>

<form method="POST">

    <input
        type="text"
        name="task_title"
        placeholder="Titre de la tâche"
        required
    >

    <br><br>

    <textarea
        name="task_description"
        placeholder="Description"
    ></textarea>

    <br><br>

    <select name="priority">
        <option value="low">Faible</option>
        <option value="medium">Moyenne</option>
        <option value="high">Haute</option>
    </select>

    <br><br>

    <input
        type="date"
        name="due_date"
    >

    <br><br>

    <button type="submit">
        Ajouter la tâche
    </button>

</form>
<body>

<h1><?= htmlspecialchars($project['title']) ?></h1>

<p>
    <?= nl2br(htmlspecialchars($project['description'])) ?>
</p>

<p>
    Créé le :
    <?= htmlspecialchars($project['created_at']) ?>
</p>
<hr>

<h2>Liste des tâches</h2>

<?php if (empty($tasks)): ?>

    <p>Aucune tâche pour le moment.</p>

<?php else: ?>

    <?php foreach ($tasks as $task): ?>

        <div>

        <h3><?= $task['is_done'] ? 'DONE' : 'NOT DONE' ?><?= htmlspecialchars($task['title']) ?></h3>

        <?php if (!$task['is_done']): ?>

        <form method="POST">
            <input
                type="hidden"
                name="complete_task_id"
                value="<?= $task['id'] ?>"
            >

            <button type="submit">
                Marquer terminée
            </button>
        </form>

<?php endif; ?>

            <p>
                <?= htmlspecialchars($task['description']) ?>
            </p>

            <form method="POST">

                <input
                    type="hidden"
                    name="delete_task_id"
                    value="<?= $task['id'] ?>"
                >

                <button type="submit">
                    Supprimer la tâche
                </button>

            </form>

            <p>
                Priorité :
                <?= htmlspecialchars($task['priority']) ?>
            </p>

        </div>

        <hr>

    <?php endforeach; ?>

<?php endif; ?>


<form action="delete.php" method="POST">

    <input
        type="hidden"
        name="project_id"
        value="<?= $project['id'] ?>"
    >

    <button type="submit">
        Supprimer le projet
    </button>

</form>

<br>

<a href="../dashboard.php">
    Retour au dashboard
</a>

</body>
</html>