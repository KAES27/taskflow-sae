<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../profil/login.php');
    exit;
}

require_once '../config/database.php';

/* 
   1. Vérifier l'id du projet
 */

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die("Projet introuvable.");
}

$projectId = (int) $_GET['id'];
$userId = $_SESSION['user_id'];

$member_error = '';
$member_success = '';
$task_error = '';

/*
   2. Récupérer le projet
 */

$sql = "SELECT *
        FROM projects
        WHERE id = :id";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    'id' => $projectId
]);

$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    die("Projet introuvable.");
}

/* 
   3. Vérifier owner / membre
 */

$isOwner = ($project['owner_id'] == $userId);

$sql = "SELECT project_id
        FROM project_members
        WHERE project_id = :project_id
        AND user_id = :user_id";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    'project_id' => $projectId,
    'user_id' => $userId
]);

$isMember = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$isOwner && !$isMember) {
    die("Vous n'avez pas accès à ce projet.");
}

/*
   4. Supprimer une tâche
   Owner seulement
 */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['delete_task_id'])
    && $isOwner
) {
    $sql = "DELETE FROM tasks
            WHERE id = :id
            AND project_id = :project_id";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'id' => $_POST['delete_task_id'],
        'project_id' => $projectId
    ]);
}

/*
   5. Terminer / remettre à faire
   Owner seulement
*/
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['change_role_user_id'])
    && isset($_POST['role'])
    && $isOwner
) {
    $memberId = (int) $_POST['change_role_user_id'];
    $role = $_POST['role'];

    if ($role === 'membre' || $role === 'manager') {
        $sql = "UPDATE project_members
                SET role = :role
                WHERE user_id = :id
                AND project_id = :project_id";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            'id' => $memberId,
            'project_id' => $projectId,
            'role' => $role
        ]);
    }
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['complete_task_id'])
    && $isOwner
) {
    $sql = "UPDATE tasks
            SET is_done = NOT is_done
            WHERE id = :id
            AND project_id = :project_id";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'id' => $_POST['complete_task_id'],
        'project_id' => $projectId
    ]);
}

/* =========================
   6. Ajouter un membre
   Owner seulement
========================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['member_email'])
    && $isOwner
) {
    $memberEmail = trim($_POST['member_email']);

    $role = $_POST['role'] ?? 'membre';

    if ($role !== 'membre' && $role !== 'manager') {
        $role = 'membre';
    }

    $sql = "SELECT id
            FROM users
            WHERE email = :email";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'email' => $memberEmail
    ]);

    $member = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$member) {
        $member_error = "Aucun utilisateur ne possède cette adresse email.";
    } elseif ($member['id'] == $project['owner_id']) {
        $member_error = "Cette personne est déjà propriétaire du projet.";
    } else {

        $sql = "SELECT project_id
                FROM project_members
                WHERE project_id = :project_id
                AND user_id = :user_id";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            'project_id' => $projectId,
            'user_id' => $member['id']
        ]);

        $alreadyMember = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($alreadyMember) {
            $member_error = "Cet utilisateur est déjà membre du projet.";
        } else {

            $sql = "INSERT INTO project_members (
                        project_id,
                        user_id,
                        role
                    )
                    VALUES (
                        :project_id,
                        :user_id,
                        :role
                    )";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                'project_id' => $projectId,
                'user_id' => $member['id'],
                'role' => $role
            ]);

            $member_success = "Membre ajouté avec succès.";
        }
    }
}

/* =========================
   7. Ajouter une tâche
   Owner seulement
========================= */

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['task_title'])
    && $isOwner
) {
    $taskTitle = trim($_POST['task_title']);
    $taskDescription = trim($_POST['task_description']);

    $priority = $_POST['priority'] ?? 'medium';

    if (
        $priority !== 'low'
        && $priority !== 'medium'
        && $priority !== 'high'
    ) {
        $priority = 'medium';
    }

    $dueDate = !empty($_POST['due_date'])
        ? $_POST['due_date']
        : null;

    $assignedTo = !empty($_POST['assigned_to'])
        ? (int) $_POST['assigned_to']
        : null;

    if (empty($taskTitle)) {
        $task_error = "Le titre de la tâche est obligatoire.";
    } else {

        /* Vérifie que la personne choisie est owner ou membre */

        if ($assignedTo !== null) {
            $sql = "SELECT users.id
                    FROM users
                    LEFT JOIN project_members
                        ON users.id = project_members.user_id
                        AND project_members.project_id = :project_id
                    WHERE users.id = :user_id
                    AND (
                        users.id = :owner_id
                        OR project_members.project_id IS NOT NULL
                    )";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                'project_id' => $projectId,
                'user_id' => $assignedTo,
                'owner_id' => $project['owner_id']
            ]);

            $allowedUser = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$allowedUser) {
                $task_error = "Cette personne ne fait pas partie du projet.";
            }
        }

        if (empty($task_error)) {
            $sql = "INSERT INTO tasks (
                        project_id,
                        title,
                        assigned_to,
                        description,
                        priority,
                        due_date
                    )
                    VALUES (
                        :project_id,
                        :title,
                        :assigned_to,
                        :description,
                        :priority,
                        :due_date
                    )";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                'project_id' => $projectId,
                'title' => $taskTitle,
                'assigned_to' => $assignedTo,
                'description' => $taskDescription,
                'priority' => $priority,
                'due_date' => $dueDate
            ]);
        }
    }
}

/* =========================
   8. Récupérer les membres
   Le owner est inclus aussi
========================= */

$sql = "SELECT users.id,
               users.first_name,
               users.last_name,
               users.email,
               'owner' AS role
        FROM projects
        JOIN users
            ON projects.owner_id = users.id
        WHERE projects.id = :project_id

        UNION

        SELECT users.id,
               users.first_name,
               users.last_name,
               users.email,
               project_members.role
        FROM project_members
        JOIN users
            ON project_members.user_id = users.id
        WHERE project_members.project_id = :project_id

        ORDER BY first_name, last_name";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    'project_id' => $projectId
]);

$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   9. Récupérer les tâches
========================= */

$sql = "SELECT tasks.*,
               users.first_name AS assigned_first_name,
               users.last_name AS assigned_last_name
        FROM tasks
        LEFT JOIN users
            ON tasks.assigned_to = users.id
        WHERE tasks.project_id = :project_id
        ORDER BY tasks.created_at DESC";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    'project_id' => $projectId
]);

$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">

    <link rel="stylesheet" href="../assets/css/style.css?v=25">

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
    >

    <title><?= htmlspecialchars($project['title']) ?></title>
</head>

<body>

    <main class="project_page">

        <section class="project_details">

            <div class="project_details_top">

                <a href="../dashboard.php" class="back_dashboard_link">
                    <i class="bi bi-arrow-left"></i>
                    Retour au tableau de bord
                </a>

                <?php if ($isOwner): ?>
                    <form action="delete.php" method="POST">
                        <input
                            type="hidden"
                            name="project_id"
                            value="<?= $project['id'] ?>"
                        >

                        <button type="submit" class="delete_project_btn">
                            <i class="bi bi-trash"></i>
                            Supprimer le projet
                        </button>
                    </form>
                <?php endif; ?>

            </div>

            <h1><?= htmlspecialchars($project['title']) ?></h1>

            <?php if (!empty($project['description'])): ?>
                <p class="project_description">
                    <?= nl2br(htmlspecialchars($project['description'])) ?>
                </p>
            <?php else: ?>
                <p class="project_description empty_description">
                    Aucune description pour ce projet.
                </p>
            <?php endif; ?>

            <p class="project_date">
                Créé le : <?= htmlspecialchars($project['created_at']) ?>
            </p>

        </section>

        <section class="project_collaboration_section">

            <div class="members_block">

                <h2>
                    <i class="bi bi-people"></i>
                    Membres du projet
                </h2>

                <?php if (!empty($member_error)): ?>
                    <p class="error_message">
                        <?= htmlspecialchars($member_error) ?>
                    </p>
                <?php endif; ?>

                <?php if (!empty($member_success)): ?>
                    <p class="success_message">
                        <?= htmlspecialchars($member_success) ?>
                    </p>
                <?php endif; ?>

                <?php if ($isOwner): ?>

                    <form
                        method="POST"
                        action="show.php?id=<?= $projectId ?>"
                        class="add_member_form"
                    >
                        <input
                            type="email"
                            name="member_email"
                            placeholder="Adresse email du membre"
                            required
                        >

                        <button type="submit" class="add_member_btn">
                            <i class="bi bi-person-plus"></i>
                            Ajouter
                        </button>
                    </form>

                <?php endif; ?>

                <div class="members_list">

                    <?php foreach ($members as $member): ?>

                        <div class="member_item">

                            <div class="member_avatar">
                                <i class="bi bi-person"></i>
                            </div>

                            <div>
                                <p class="member_name">
                                    <?= htmlspecialchars($member['first_name']) ?>
                                    <?= htmlspecialchars($member['last_name']) ?>
                                </p>

                                <p class="member_email">
                                    <?= htmlspecialchars($member['email']) ?>
                                </p>

                                <p class="member_email">
                                    Rôle :
                                    <?= htmlspecialchars($member['role']) ?>
                                </p>
                                <?php if ($isOwner && $member['role'] !== 'owner'): ?>

                                <form
                                    method="POST"
                                    action="show.php?id=<?= $projectId ?>"
                                    class="change_role_form"
                                >
                                    <input
                                        type="hidden"
                                        name="change_role_user_id"
                                        value="<?= $member['id'] ?>"
                                    >

                                    <select name="role">
                                        <option
                                            value="membre"
                                            <?= $member['role'] === 'membre' ? 'selected' : '' ?>
                                        >
                                            Membre
                                        </option>

                                        <option
                                            value="manager"
                                            <?= $member['role'] === 'manager' ? 'selected' : '' ?>
                                        >
                                            Manager
                                        </option>
                                    </select>

                                    <button type="submit">
                                        Modifier le rôle
                                    </button>
                                </form>

                            <?php endif; ?>
                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

        </section>

        <?php if ($isOwner): ?>

            <section class="task_create_section">

                <h2>
                    <i class="bi bi-plus-circle"></i>
                    Ajouter une tâche
                </h2>

                <?php if (!empty($task_error)): ?>
                    <p class="error_message">
                        <?= htmlspecialchars($task_error) ?>
                    </p>
                <?php endif; ?>

                <form
                    method="POST"
                    action="show.php?id=<?= $projectId ?>"
                    class="task_create_form"
                >

                    <input
                        type="text"
                        name="task_title"
                        placeholder="Titre de la tâche"
                        required
                    >

                    <textarea
                        name="task_description"
                        placeholder="Description de la tâche"
                    ></textarea>

                    <div class="task_form_row">

                        <select name="priority">
                            <option value="low">Priorité faible</option>
                            <option value="medium" selected>Priorité moyenne</option>
                            <option value="high">Priorité haute</option>
                        </select>

                        <input
                            type="date"
                            name="due_date"
                        >

                    </div>

                    <select name="assigned_to">
                        <option value="">
                            Ne pas attribuer pour le moment
                        </option>

                        <?php foreach ($members as $member): ?>
                            <option value="<?= $member['id'] ?>">
                                <?= htmlspecialchars($member['first_name']) ?>
                                <?= htmlspecialchars($member['last_name']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>

                    <button type="submit" class="add_task_btn">
                        <i class="bi bi-plus-lg"></i>
                        Ajouter la tâche
                    </button>

                </form>

            </section>

        <?php endif; ?>

        <section class="tasks_section">

            <h2>
                <i class="bi bi-list-task"></i>
                Liste des tâches
            </h2>

            <?php if (empty($tasks)): ?>

                <p class="empty_tasks_message">
                    Aucune tâche pour le moment.
                </p>

            <?php else: ?>

                <?php foreach ($tasks as $task): ?>

                    <article class="task_card">

                        <div class="task_card_header">

                            <h3>
                                <?= htmlspecialchars($task['title']) ?>
                            </h3>

                            <?php if ($task['is_done']): ?>
                                <span class="task_status_done">
                                    <i class="bi bi-check-circle-fill"></i>
                                    Terminée
                                </span>
                            <?php else: ?>
                                <span class="task_status_todo">
                                    <i class="bi bi-circle"></i>
                                    À faire
                                </span>
                            <?php endif; ?>

                        </div>

                        <?php if (!empty($task['description'])): ?>
                            <p class="task_description">
                                <?= nl2br(htmlspecialchars($task['description'])) ?>
                            </p>
                        <?php endif; ?>

                        <div class="task_info">

                            <p class="task_priority">
                                <i class="bi bi-flag"></i>
                                Priorité :
                                <strong>
                                    <?= htmlspecialchars($task['priority']) ?>
                                </strong>
                            </p>

                            <?php if (!empty($task['due_date'])): ?>
                                <p class="task_due_date">
                                    <i class="bi bi-calendar-event"></i>
                                    Date limite :
                                    <?= htmlspecialchars($task['due_date']) ?>
                                </p>
                            <?php endif; ?>

                            <?php if (!empty($task['assigned_first_name'])): ?>
                                <p class="task_assigned_to">
                                    <i class="bi bi-person-check"></i>
                                    Assignée à :
                                    <strong>
                                        <?= htmlspecialchars($task['assigned_first_name']) ?>
                                        <?= htmlspecialchars($task['assigned_last_name']) ?>
                                    </strong>
                                </p>
                            <?php else: ?>
                                <p class="task_assigned_to">
                                    <i class="bi bi-person"></i>
                                    Non attribuée
                                </p>
                            <?php endif; ?>

                        </div>

                        <?php if ($isOwner): ?>

                            <div class="task_actions">

                                <form
                                    method="POST"
                                    action="show.php?id=<?= $projectId ?>"
                                >
                                    <input
                                        type="hidden"
                                        name="complete_task_id"
                                        value="<?= $task['id'] ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="complete_task_btn"
                                    >
                                        <i class="bi bi-check-lg"></i>

                                        <?= $task['is_done']
                                            ? 'Remettre à faire'
                                            : 'Marquer terminée'
                                        ?>
                                    </button>
                                </form>

                                <form
                                    method="POST"
                                    action="show.php?id=<?= $projectId ?>"
                                >
                                    <input
                                        type="hidden"
                                        name="delete_task_id"
                                        value="<?= $task['id'] ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="delete_task_btn"
                                    >
                                        <i class="bi bi-trash"></i>
                                        Supprimer
                                    </button>
                                </form>

                            </div>

                        <?php endif; ?>

                    </article>

                <?php endforeach; ?>

            <?php endif; ?>

        </section>

    </main>

</body>
</html>