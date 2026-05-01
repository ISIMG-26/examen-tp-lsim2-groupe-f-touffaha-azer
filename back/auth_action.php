<?php
session_start();
require_once 'db.php';
require_once 'helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'register') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($email) || empty($password)) {
            $_SESSION['error'] = "Veuillez remplir tous les champs.";
            header("Location: auth.php");
            exit();
        }

        // Vérifier si l'utilisateur existe
        $existing = db_fetch_one($conn, "SELECT id FROM users WHERE email = ? OR username = ?", 'ss', [$email, $username]);
        if ($existing) {
            $_SESSION['error'] = "Cet email ou nom d'utilisateur est déjà utilisé.";
            header("Location: auth.php");
            exit();
        }

        // Hachage du mot de passe
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Si le nom d'utilisateur est 'admin', on lui donne le rôle admin, sinon 'user'
        $role = ($username === 'admin') ? 'admin' : 'user';

        // Insertion
        $ok = db_exec($conn, "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)", 'ssss', [$username, $email, $hashed_password, $role]);
        if ($ok > 0) {
            $_SESSION['success'] = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
        } else {
            $_SESSION['error'] = "Une erreur est survenue lors de l'inscription.";
        }
        header("Location: auth.php");
        exit();
    } 
    elseif ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $user = db_fetch_one($conn, "SELECT * FROM users WHERE email = ?", 's', [$email]);

        if ($user && password_verify($password, $user['password'])) {
            // Connexion réussie
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'] ?? 'user';
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = "Email ou mot de passe incorrect.";
            header("Location: auth.php");
            exit();
        }
    }
} else {
    header("Location: index.php");
    exit();
}
