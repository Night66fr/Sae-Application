<?php
/**
 * connexion.php - Interface d'authentification LevelUp
 */

require_once('funcs.php');
require_once('config-db.php');

session_start();

// 1. Gestion de la déconnexion
if (isset($_REQUEST['logout']) && $_REQUEST['logout'] == 'true') {
    session_destroy();
    $_SESSION = array();
    header('Location: connexion.php');
    exit();
}

// 2. Traitement du formulaire
$error_msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = cbGetValue($_POST, 'user');
    $pass = cbGetValue($_POST, 'pass');

    if ($user != '' && $pass != '') {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$user]);
        $row = $stmt->fetch();

        // password_verify compare le texte saisi avec le hash du SQL
        if ($row && password_verify($pass, $row['password'])) {
            $_SESSION['auth'] = 'ok';
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];
            
            header('Location: dashboard.php');
            exit();
        } else {
            $error_msg = "Identifiants incorrects ou utilisateur inexistant.";
        }
    } else {
        $error_msg = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>LevelUp - Connexion</title>
    <style>
        :root {
            --primary-color: #6366f1;
            --bg-gradient: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
            --card-bg: rgba(255, 255, 255, 0.05);
            --text-main: #f8fafc;
        }
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: var(--bg-gradient);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-main);
        }
        .login-container {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 2.5rem;
            border-radius: 1.5rem;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .form-group { margin-bottom: 1.5rem; text-align: left; }
        input {
            width: 100%;
            padding: 0.75rem;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.5rem;
            color: white;
            box-sizing: border-box;
        }
        .btn-submit {
            width: 100%;
            padding: 0.75rem;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
            font-weight: bold;
        }
        .error-banner {
            background: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
<div class="login-container">
    <h1>LevelUp</h1>
    <?php if ($error_msg) echo "<div class='error-banner'>$error_msg</div>"; ?>
    <form action="connexion.php" method="POST">
        <div class="form-group">
            <label>Utilisateur</label>
            <input type="text" name="user" required>
        </div>
        <div class="form-group">
            <label>Mot de passe</label>
            <input type="password" name="pass" required>
        </div>
        <button type="submit" class="btn-submit">Se connecter</button>
    </form>
</div>
</body>
</html>