<?php
// ============================================================
//  LevelUp – register.php
//  Création de compte avec validation e-mail IUT Béziers
// ============================================================
session_start();

$dbHost   = 'localhost';
$dbName   = 'db_PLACE_NEVEUX';
$dbUser   = '22505078';
$dbPasswd = '126620';

try {
    $pdo = new PDO('mysql:host='.$dbHost.';dbname='.$dbName.';charset=utf8mb4', $dbUser, $dbPasswd);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('<p style="color:red;text-align:center;">Erreur BDD : '.htmlspecialchars($e->getMessage()).'</p>');
}

// Déjà connecté
if (isset($_SESSION['auth']) && $_SESSION['auth'] === 'ok') {
    header('Location: index.php'); exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prenom = trim($_POST['prenom'] ?? '');
    $nom    = trim($_POST['nom']    ?? '');
    $email  = trim($_POST['email']  ?? '');
    $login  = trim($_POST['login']  ?? '');
    $pass1  = $_POST['pass1'] ?? '';
    $pass2  = $_POST['pass2'] ?? '';

    // ---- Validations ----
    if ($prenom==='' || $nom==='' || $email==='' || $login==='' || $pass1==='') {
        $error = 'Tous les champs sont obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse e-mail invalide.';
    } elseif (!preg_match('/@(iut-beziers\.fr|etud\.iut-beziers\.fr|univ-montpellier\.fr)$/i', $email)) {
        $error = 'Seules les adresses @iut-beziers.fr ou @etud.iut-beziers.fr sont acceptées.';
    } elseif (strlen($pass1) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères.';
    } elseif ($pass1 !== $pass2) {
        $error = 'Les deux mots de passe ne correspondent pas.';
    } elseif (!preg_match('/^[a-z0-9._-]{3,30}$/i', $login)) {
        $error = 'Le login ne doit contenir que des lettres, chiffres, points ou tirets (3–30 caractères).';
    } else {
        // Vérifie unicité login et email
        $chk = $pdo->prepare('SELECT id FROM users WHERE user=:u OR email=:e LIMIT 1');
        $chk->execute([':u' => $login, ':e' => $email]);
        if ($chk->fetch()) {
            $error = 'Ce login ou cette adresse e-mail est déjà utilisé.';
        } else {
            $hash = password_hash($pass1, PASSWORD_BCRYPT);
            $ins  = $pdo->prepare(
                'INSERT INTO users (user, pass, role, nom, prenom, email, niveau, xp, streak, actif)
                 VALUES (:u, :p, "etudiant", :n, :pr, :e, 1, 0, 0, 1)'
            );
            $ins->execute([
                ':u'  => $login,
                ':p'  => $hash,
                ':n'  => $nom,
                ':pr' => $prenom,
                ':e'  => $email,
            ]);
            $success = 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>LevelUp – Créer un compte</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#0f0f1a;--bg2:#1a1a2e;--card:#16213e;
  --accent:#e94560;--neon:#00d4ff;--gold:#ffd700;
  --green:#00ff88;--text:#e0e0e0;--muted:#888;--radius:12px;
}
body{background:var(--bg);color:var(--text);font-family:'Segoe UI',sans-serif;min-height:100vh;
  display:flex;flex-direction:column;align-items:center;justify-content:center;padding:30px 20px;
  background:radial-gradient(ellipse at top,#1a1a3e 0%,var(--bg) 70%)}

.login-logo{text-align:center;margin-bottom:24px}
.logo-icon{font-size:3rem;display:block;animation:pulse 2s infinite}
@keyframes pulse{0%,100%{filter:drop-shadow(0 0 6px var(--neon))}50%{filter:drop-shadow(0 0 20px var(--neon))}}
.logo-title{font-size:2.4rem;font-weight:900;letter-spacing:2px;color:#fff}
.logo-title span{color:var(--neon)}
.logo-sub{font-size:.85rem;color:var(--muted);margin-top:4px}

.register-card{background:var(--card);border:1px solid rgba(0,212,255,.2);border-radius:var(--radius);
  box-shadow:0 8px 32px rgba(0,212,255,.15);padding:36px 40px;width:100%;max-width:480px}
.register-card h2{text-align:center;font-size:1.2rem;color:var(--neon);margin-bottom:24px;
  letter-spacing:1px;text-transform:uppercase}

.form-row{display:flex;gap:12px}
.form-row .form-group{flex:1}
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:.83rem;color:var(--muted);margin-bottom:5px}
.form-group input{width:100%;padding:11px 13px;background:var(--bg2);
  border:1px solid rgba(0,212,255,.25);border-radius:8px;color:var(--text);font-size:.95rem;outline:none;transition:.2s}
.form-group input:focus{border-color:var(--neon);box-shadow:0 0 0 3px rgba(0,212,255,.12)}

.hint{font-size:.75rem;color:var(--muted);margin-top:4px}

.btn-main{width:100%;padding:13px;margin-top:6px;
  background:linear-gradient(135deg,#0f3460,var(--accent));
  border:none;border-radius:8px;color:#fff;font-size:1rem;font-weight:700;cursor:pointer;transition:.2s}
.btn-main:hover{filter:brightness(1.15)}

.msg-err{color:var(--accent);background:rgba(233,69,96,.08);border:1px solid rgba(233,69,96,.3);
  border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:.9rem;text-align:center}
.msg-ok{color:var(--green);background:rgba(0,255,136,.08);border:1px solid rgba(0,255,136,.3);
  border-radius:8px;padding:12px 14px;margin-bottom:16px;font-size:.95rem;text-align:center}

.bottom-link{text-align:center;margin-top:18px;font-size:.85rem;color:var(--muted)}
.bottom-link a{color:var(--neon)}
.bottom-link a:hover{text-decoration:underline}

.login-footer{margin-top:22px;font-size:.75rem;color:var(--muted);text-align:center}
</style>
</head>
<body>

<div class="login-logo">
  <span class="logo-icon">&#9889;</span>
  <h1 class="logo-title">Level<span>Up</span></h1>
  <p class="logo-sub">Plateforme d'Apprentissage R&amp;T – IUT Béziers</p>
</div>

<div class="register-card">
  <h2>&#128100; Créer un compte</h2>

  <?php if ($error)   echo '<div class="msg-err">&#10060; '.htmlspecialchars($error).'</div>'; ?>
  <?php if ($success) echo '<div class="msg-ok">&#9989; '.htmlspecialchars($success).'<br/><a href="login-page.php" style="color:var(--neon);">Se connecter</a></div>'; ?>

  <?php if (!$success): ?>
  <form method="post" action="register.php">

    <div class="form-row">
      <div class="form-group">
        <label for="prenom">Prénom</label>
        <input type="text" id="prenom" name="prenom"
               value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>"
               placeholder="Alice" required/>
      </div>
      <div class="form-group">
        <label for="nom">Nom</label>
        <input type="text" id="nom" name="nom"
               value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
               placeholder="Dupont" required/>
      </div>
    </div>

    <div class="form-group">
      <label for="email">&#128231; E-mail IUT</label>
      <input type="email" id="email" name="email"
             value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
             placeholder="prenom.nom@etud.iut-beziers.fr" required/>
      <p class="hint">&#9432; Seules les adresses @iut-beziers.fr ou @etud.iut-beziers.fr sont acceptées.</p>
    </div>

    <div class="form-group">
      <label for="login">&#128273; Login (identifiant de connexion)</label>
      <input type="text" id="login" name="login"
             value="<?= htmlspecialchars($_POST['login'] ?? '') ?>"
             placeholder="alice.dupont" required/>
      <p class="hint">Lettres, chiffres, points ou tirets. 3 à 30 caractères.</p>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="pass1">&#128274; Mot de passe</label>
        <input type="password" id="pass1" name="pass1" placeholder="••••••••" required/>
        <p class="hint">6 caractères minimum.</p>
      </div>
      <div class="form-group">
        <label for="pass2">Confirmer</label>
        <input type="password" id="pass2" name="pass2" placeholder="••••••••" required/>
      </div>
    </div>

    <button type="submit" class="btn-main">&#9889; Créer mon compte</button>
  </form>
  <?php endif; ?>

  <div class="bottom-link">
    Déjà un compte ? <a href="login-page.php">Se connecter</a>
  </div>
</div>

<p class="login-footer">SAE23 &bull; R&amp;T &bull; IUT de Béziers</p>
</body>
</html>
