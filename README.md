# TaskFlow SAE

TaskFlow SAE est une application web simple de gestion et de création de projets collaboratifs. Elle permet à un utilisateur de créer un compte, de se connecter, de créer des projets, d'ajouter des membres et de gérer les tâches associées à chaque projet.

## Objectif du projet

L'objectif de ce projet est de proposer une plateforme légère permettant d'organiser le travail autour de plusieurs projets. Chaque projet peut contenir des membres, des rôles et des tâches avec une priorité, une date limite et un état d'avancement.

## Fonctionnalités principales

- Inscription d'un utilisateur.
- Connexion et déconnexion avec gestion de session.
- Tableau de bord affichant les projets de l'utilisateur.
- Création d'un nouveau projet.
- Affichage détaillé d'un projet.
- Suppression d'un projet par son propriétaire.
- Ajout de membres à un projet via leur adresse email.
- Attribution d'un rôle aux membres : `membre` ou `manager`.
- Création de tâches dans un projet.
- Attribution d'une tâche à un membre du projet.
- Gestion des priorités : faible, moyenne ou haute.
- Ajout d'une date limite à une tâche.
- Marquage d'une tâche comme terminée ou à faire.
- Suppression d'une tâche.
- Protection des pages : un utilisateur non connecté est redirigé vers la page de connexion.

## Technologies utilisées

- **PHP** : logique serveur et gestion des pages dynamiques.
- **MySQL** : stockage des utilisateurs, projets, membres et tâches.
- **PDO** : connexion sécurisée à la base de données.
- **HTML5** : structure des pages.
- **CSS3** : mise en page et design de l'interface.
- **Bootstrap Icons** : icônes utilisées dans l'interface.

## Structure du projet

```text
TaskFlow-SAE/
├── assets/
│   └── css/
│       └── style.css
├── config/
│   └── database.php
├── profil/
│   ├── login.php
│   ├── logout.php
│   └── register.php
├── projects/
│   ├── create.php
│   ├── delete.php
│   └── show.php
├── sql/
│   └── schema.sql
├── dashboard.php
└── README.md
```

## Description des dossiers et fichiers

### `assets/css/style.css`

Contient tout le style graphique de l'application : formulaires de connexion et d'inscription, tableau de bord, cartes de projets, page de détail d'un projet, gestion des membres et affichage des tâches.

### `config/database.php`

Contient les informations de connexion à la base de données MySQL. La connexion est réalisée avec PDO.

```php
$host = 'localhost';
$dbname = 'taskflow_db';
$username = 'root';
$password = '';
```

### `profil/register.php`

Permet à un nouvel utilisateur de créer un compte. Le mot de passe est sécurisé avec `password_hash()` avant d'être enregistré dans la base de données.

### `profil/login.php`

Permet à un utilisateur existant de se connecter. Le mot de passe saisi est vérifié avec `password_verify()`.

### `profil/logout.php`

Détruit la session de l'utilisateur et le redirige vers la page de connexion.

### `dashboard.php`

Affiche le tableau de bord de l'utilisateur connecté. On y retrouve tous les projets dont il est propriétaire ou membre.

### `projects/create.php`

Permet de créer un nouveau projet avec un titre et une description. L'utilisateur connecté devient automatiquement propriétaire du projet.

### `projects/show.php`

Affiche les détails d'un projet : description, membres, rôles et tâches. Cette page permet également d'ajouter un membre, modifier son rôle, créer une tâche, l'attribuer à un membre, la marquer comme terminée ou la supprimer.

### `projects/delete.php`

Permet de supprimer un projet. Grâce aux contraintes SQL, les membres et les tâches liés au projet sont aussi supprimés automatiquement.

### `sql/schema.sql`

Contient le script SQL de création de la base de données.

## Base de données

La base de données utilisée est nommée :

```sql
 taskflow_db
```

Elle contient les tables suivantes :

- `users` : stocke les informations des utilisateurs.
- `profiles` : stocke des informations complémentaires sur les profils utilisateurs.
- `projects` : stocke les projets créés.
- `project_members` : associe les utilisateurs aux projets avec un rôle.
- `tasks` : stocke les tâches liées aux projets.
- `comments` : table prévue pour gérer des commentaires associés aux tâches.

## Installation du projet

### 1. Placer le projet dans le serveur local

Avec XAMPP, WAMP ou MAMP, placer le dossier du projet dans le dossier du serveur local.

Exemple avec XAMPP :

```text
C:/xampp/htdocs/taskflow-sae
```

### 2. Créer la base de données

Ouvrir phpMyAdmin puis créer une base de données nommée :

```sql
CREATE DATABASE taskflow_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
```

### 3. Importer le script SQL

Importer le fichier suivant dans phpMyAdmin :

```text
sql/schema.sql
```

Ce fichier crée les tables nécessaires au fonctionnement du projet.

### 4. Vérifier la configuration de la base de données

Dans le fichier :

```text
config/database.php
```

Vérifier les informations de connexion :

```php
$host = 'localhost';
$dbname = 'taskflow_db';
$username = 'root';
$password = '';
```

Ces valeurs correspondent à la configuration classique de XAMPP. Elles doivent être modifiées si votre environnement utilise un autre nom d'utilisateur ou mot de passe.

### 5. Lancer le projet

Démarrer Apache et MySQL, puis ouvrir le projet dans le navigateur :

```text
http://localhost/taskflow-sae/profil/register.php
```

Créer un compte, puis se connecter depuis :

```text
http://localhost/taskflow-sae/profil/login.php
```

Après la connexion, l'utilisateur est redirigé vers le tableau de bord :

```text
http://localhost/taskflow-sae/dashboard.php
```

## Utilisation rapide

1. Créer un compte utilisateur.
2. Se connecter.
3. Créer un nouveau projet depuis le tableau de bord.
4. Ouvrir le projet.
5. Ajouter des membres avec leur adresse email.
6. Créer des tâches.
7. Définir la priorité, la date limite et la personne assignée.
8. Suivre l'avancement des tâches.

## Sécurité mise en place

- Les mots de passe sont hachés avec `password_hash()`.
- La vérification des mots de passe est faite avec `password_verify()`.
- Les requêtes SQL utilisent PDO avec des requêtes préparées.
- Les pages importantes vérifient que l'utilisateur est connecté.
- Les données affichées sont protégées avec `htmlspecialchars()` afin de limiter les risques d'injection HTML.
- L'accès à un projet est contrôlé : seuls le propriétaire ou les membres du projet peuvent consulter la page du projet.

## Améliorations possibles

- Ajouter une page de profil utilisateur.
- Permettre la modification d'un projet.
- Permettre la modification d'une tâche.
- Ajouter un système de commentaires visible dans l'interface.
- Ajouter une recherche ou un filtre de tâches.
- Ajouter un système de notifications.
- Améliorer la gestion détaillée des permissions selon les rôles.
- Ajouter une confirmation avant la suppression d'un projet ou d'une tâche.

## Auteur

Projet réalisé dans le cadre d'une SAE de développement web.
