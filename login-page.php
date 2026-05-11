<?php
// ============================================================
//  LevelUp – login-page.php
//  Page d'accueil : connexion avec choix de rôle
//  + mot de passe oublié (simulé)
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
$vue     = isset($_GET['vue']) ? $_GET['vue'] : 'login'; // login | oubli

// ---- Traitement connexion ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $user     = trim($_POST['user'] ?? '');
    $pass     = $_POST['pass'] ?? '';
    $roleVoulu = $_POST['role_choisi'] ?? 'etudiant';

    if ($user === '' || $pass === '') {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT id, user, pass, role, nom, prenom, xp, niveau, streak
             FROM users WHERE user = :u AND actif = 1 LIMIT 1'
        );
        $stmt->execute([':u' => $user]);
        $row = $stmt->fetch();

        if (!$row || !password_verify($pass, $row['pass'])) {
            $error = 'Identifiant ou mot de passe incorrect.';
        } elseif ($row['role'] !== $roleVoulu) {
            $error = 'Ce compte n\'est pas un compte "'.htmlspecialchars($roleVoulu).'".';
        } else {
            // Streak
            $s2 = $pdo->prepare('SELECT last_login, streak FROM users WHERE id=:id');
            $s2->execute([':id' => $row['id']]);
            $prev = $s2->fetch();
            $newStreak = 1;
            if ($prev && $prev['last_login']) {
                $diff = (new DateTime())->diff(new DateTime($prev['last_login']))->days;
                if ($diff < 2) $newStreak = (int)$prev['streak'] + 1;
            }
            $pdo->prepare('UPDATE users SET last_login=NOW(), streak=:s WHERE id=:id')
                ->execute([':s' => $newStreak, ':id' => $row['id']]);

            $_SESSION['auth']    = 'ok';
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user']    = $row['user'];
            $_SESSION['role']    = $row['role'];
            $_SESSION['nom']     = $row['nom'];
            $_SESSION['prenom']  = $row['prenom'];
            $_SESSION['xp']      = $row['xp'];
            $_SESSION['niveau']  = $row['niveau'];
            $_SESSION['streak']  = $newStreak;
            header('Location: index.php'); exit();
        }
    }
}

// ---- Traitement mot de passe oublié (simulé) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'oubli') {
    $email = trim($_POST['email'] ?? '');
    $vue   = 'oubli';
    if ($email === '') {
        $error = 'Veuillez entrer votre adresse e-mail.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse e-mail invalide.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :e AND actif = 1 LIMIT 1');
        $stmt->execute([':e' => $email]);
        // On affiche toujours le même message (sécurité : ne pas révéler si l'email existe)
        $success = 'Si cette adresse est connue, un e-mail de réinitialisation a été envoyé.';
    }
}

// Rôle sélectionné (pour garder l'état des boutons)
$roleActif = $_POST['role_choisi'] ?? $_GET['role'] ?? 'etudiant';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>LevelUp – Connexion</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#0f0f1a;--bg2:#1a1a2e;--card:#16213e;
  --accent:#e94560;--neon:#00d4ff;--gold:#ffd700;
  --green:#00ff88;--text:#e0e0e0;--muted:#888;--radius:12px;
}
body{background:var(--bg);color:var(--text);font-family:'Segoe UI',sans-serif;min-height:100vh;
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  padding:20px;background:radial-gradient(ellipse at top,#1a1a3e 0%,var(--bg) 70%)}

/* LOGO */
.login-logo{text-align:center;margin-bottom:28px}
.logo-icon{font-size:3.5rem;display:block;animation:pulse 2s infinite}
@keyframes pulse{0%,100%{filter:drop-shadow(0 0 6px var(--neon))}50%{filter:drop-shadow(0 0 20px var(--neon))}}
.logo-title{font-size:2.8rem;font-weight:900;letter-spacing:2px;color:#fff}
.logo-title span{color:var(--neon)}
.logo-sub{font-size:.85rem;color:var(--muted);margin-top:6px}

/* CARTE */
.login-card{background:var(--card);border:1px solid rgba(0,212,255,.2);border-radius:var(--radius);
  box-shadow:0 8px 32px rgba(0,212,255,.15);padding:36px 40px;width:100%;max-width:440px}
.login-card h2{text-align:center;font-size:1.2rem;color:var(--neon);margin-bottom:24px;
  letter-spacing:1px;text-transform:uppercase}

/* BOUTONS RÔLE */
.role-switch{display:flex;gap:10px;margin-bottom:24px}
.role-btn{flex:1;padding:10px;border:2px solid rgba(0,212,255,.2);border-radius:8px;
  background:var(--bg2);color:var(--muted);font-size:.9rem;font-weight:600;cursor:pointer;
  transition:.2s;text-align:center}
.role-btn.active-etudiant{border-color:var(--neon);color:var(--neon);background:rgba(0,212,255,.08)}
.role-btn.active-enseignant{border-color:var(--gold);color:var(--gold);background:rgba(255,215,0,.08)}
.role-btn:hover{filter:brightness(1.2)}

/* CHAMPS */
.form-group{margin-bottom:16px}
.form-group label{display:block;font-size:.85rem;color:var(--muted);margin-bottom:6px}
.form-group input{width:100%;padding:12px 14px;background:var(--bg2);
  border:1px solid rgba(0,212,255,.25);border-radius:8px;color:var(--text);
  font-size:1rem;outline:none;transition:.2s}
.form-group input:focus{border-color:var(--neon);box-shadow:0 0 0 3px rgba(0,212,255,.12)}

/* BOUTON SUBMIT */
.btn-main{width:100%;padding:13px;margin-top:6px;
  background:linear-gradient(135deg,#0f3460,var(--accent));
  border:none;border-radius:8px;color:#fff;font-size:1rem;font-weight:700;
  cursor:pointer;transition:.2s;letter-spacing:.5px}
.btn-main:hover{filter:brightness(1.15)}

/* LIENS */
.links{display:flex;justify-content:space-between;margin-top:16px;font-size:.82rem}
.links a{color:var(--muted);transition:.2s}
.links a:hover{color:var(--neon)}

/* MESSAGES */
.msg-err{color:var(--accent);background:rgba(233,69,96,.08);border:1px solid rgba(233,69,96,.3);
  border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:.9rem;text-align:center}
.msg-ok{color:var(--green);background:rgba(0,255,136,.08);border:1px solid rgba(0,255,136,.3);
  border-radius:8px;padding:10px 14px;margin-bottom:16px;font-size:.9rem;text-align:center}

/* PIED */
.login-footer{margin-top:22px;font-size:.75rem;color:var(--muted);text-align:center}

/* LIEN RETOUR */
.btn-back{display:inline-block;margin-bottom:16px;font-size:.85rem;color:var(--muted);cursor:pointer}
.btn-back:hover{color:var(--neon)}
</style>
</head>
<body>

<div class="login-logo">
  <span class="logo-icon">&#9889;</span>
  <h1 class="logo-title">Level<span>Up</span></h1>
  <p class="logo-sub">Plateforme d'Apprentissage R&amp;T – IUT Béziers</p>
</div>

<div class="login-card">

<?php if ($vue === 'oubli'): ?>

  <!-- ======= VUE : MOT DE PASSE OUBLIÉ ======= -->
  <a class="btn-back" href="login-page.php">&#8592; Retour</a>
  <h2>&#128274; Mot de passe oublié</h2>

  <?php if ($error)   echo '<div class="msg-err">&#10060; '.htmlspecialchars($error).'</div>'; ?>
  <?php if ($success) echo '<div class="msg-ok">&#9989; '.htmlspecialchars($success).'</div>'; ?>

  <?php if (!$success): ?>
  <form method="post" action="login-page.php?vue=oubli">
    <input type="hidden" name="action" value="oubli"/>
    <div class="form-group">
      <label for="email">&#128231; Votre adresse e-mail étudiante</label>
      <input type="email" id="email" name="email" placeholder="prenom.nom@iut-beziers.fr" required/>
    </div>
    <button type="submit" class="btn-main">&#9993; Envoyer le lien de réinitialisation</button>
  </form>
  <?php endif; ?>

<?php else: ?>

  <!-- ======= VUE : CONNEXION ======= -->
  <h2>Connexion</h2>

  <?php if ($error) echo '<div class="msg-err">&#10060; '.htmlspecialchars($error).'</div>'; ?>

  <form method="post" action="login-page.php" id="loginForm">
    <input type="hidden" name="action" value="login"/>
    <input type="hidden" name="role_choisi" id="role_choisi" value="<?= htmlspecialchars($roleActif) ?>"/>

    <!-- Boutons choix de rôle -->
    <div class="role-switch">
      <button type="button" class="role-btn <?= $roleActif==='etudiant' ? 'active-etudiant' : '' ?>"
              onclick="setRole('etudiant',this)">
        &#127891; Étudiant
      </button>
      <button type="button" class="role-btn <?= $roleActif==='enseignant' ? 'active-enseignant' : '' ?>"
              onclick="setRole('enseignant',this)">
        &#127979; Enseignant
      </button>
    </div>

    <div class="form-group">
      <label for="user">&#128100; Identifiant</label>
      <input type="text" id="user" name="user" placeholder="Votre login"
             value="<?= htmlspecialchars($_POST['user'] ?? '') ?>" required/>
    </div>
    <div class="form-group">
      <label for="pass">&#128274; Mot de passe</label>
      <input type="password" id="pass" name="pass" placeholder="••••••••" required/>
    </div>

    <button type="submit" class="btn-main">&#9654; Se connecter</button>
  </form>

  <div class="links">
    <a href="login-page.php?vue=oubli">Mot de passe oublié ?</a>
    <a href="register.php">Créer un compte</a>
  </div>

<?php endif; ?>

</div>

<p class="login-footer">SAE23 &bull; R&amp;T &bull; IUT de Béziers</p>

<script>
function setRole(role, btn) {
  document.getElementById('role_choisi').value = role;
  document.querySelectorAll('.role-btn').forEach(b => {
    b.className = 'role-btn';
  });
  btn.classList.add('active-' + role);
}
</script>
</body>
</html>
