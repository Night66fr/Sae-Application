<?php
// ============================================================
//  LevelUp – login.php
//  À inclure EN PREMIER dans chaque page protégée.
//  Si non connecté → redirige vers login-page.php
// ============================================================
session_start();

// ---- PDO : connexion BDD ----
$dbHost   = 'localhost';
$dbName   = 'db_PLACE_NEVEUX';
$dbUser   = '22505078';
$dbPasswd = '126620';

try {
    $pdo = new PDO(
        'mysql:host='.$dbHost.';dbname='.$dbName.';charset=utf8mb4',
        $dbUser, $dbPasswd
    );
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('<p style="color:red;text-align:center;">Erreur BDD : '.htmlspecialchars($e->getMessage()).'</p>');
}

// ---- Helpers ----
function cbGetValue($array, $name, $default = '') {
    return isset($array[$name]) ? $array[$name] : $default;
}
function cbPrintf() {
    $args = func_get_args();
    if (!$args) $args = [''];
    $args[0] .= "\n";
    call_user_func_array('printf', $args);
}

// ---- Restriction par rôle (à appeler après include) ----
function requireRole($roles) {
    if (!is_array($roles)) $roles = [$roles];
    if (!in_array(cbGetValue($_SESSION, 'role', ''), $roles, true)) {
        echo '<p style="color:#e94560;text-align:center;">&#128683; Accès refusé.</p>';
        echo '</div></body></html>';
        exit();
    }
}

// ---- Non connecté → login ----
if (cbGetValue($_SESSION, 'auth') !== 'ok') {
    header('Location: login-page.php');
    exit();
}

// ---- En-tête HTML ----
$pageTitle = isset($title) ? $title : 'LevelUp';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title><?= htmlspecialchars($pageTitle) ?> – LevelUp</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#0f0f1a;--bg2:#1a1a2e;--card:#16213e;
  --accent:#e94560;--neon:#00d4ff;--gold:#ffd700;
  --text:#e0e0e0;--muted:#888;--radius:12px;
}
body{background:var(--bg);color:var(--text);font-family:'Segoe UI',sans-serif;min-height:100vh}
.topbar{width:100%;background:var(--bg2);border-bottom:1px solid rgba(0,212,255,.15);
  display:flex;align-items:center;justify-content:space-between;padding:10px 28px;
  position:sticky;top:0;z-index:100}
.topbar-brand{font-weight:900;font-size:1.2rem;color:#fff}
.topbar-brand span{color:var(--neon)}
.topbar-info{display:flex;align-items:center;gap:14px;font-size:.9rem;flex-wrap:wrap}
.badge-xp{background:#0f3460;color:var(--neon);padding:3px 10px;border-radius:20px;font-weight:700;font-size:.8rem}
.badge-role{background:rgba(255,215,0,.12);color:var(--gold);padding:3px 10px;border-radius:20px;font-size:.8rem;text-transform:capitalize}
.btn-logout{background:var(--accent);color:#fff;border:none;border-radius:6px;
  padding:6px 14px;cursor:pointer;font-size:.85rem;font-weight:600;text-decoration:none;transition:.2s}
.btn-logout:hover{filter:brightness(1.2)}
.main-content{width:100%;max-width:960px;margin:0 auto;padding:32px 20px}
h1{font-size:1.8rem;margin-bottom:16px}
a{color:var(--neon);text-decoration:none}
a:hover{text-decoration:underline}
</style>
</head>
<body>
<?php
// ---- Navbar ----
$prenom = htmlspecialchars(cbGetValue($_SESSION,'prenom','') ?: cbGetValue($_SESSION,'user',''));
$role   = htmlspecialchars(cbGetValue($_SESSION,'role','etudiant'));
$xp     = (int)cbGetValue($_SESSION,'xp',0);
$niveau = (int)cbGetValue($_SESSION,'niveau',1);
$streak = (int)cbGetValue($_SESSION,'streak',0);

echo '<nav class="topbar">';
echo '<span class="topbar-brand">&#9889; Level<span>Up</span></span>';
echo '<div class="topbar-info">';
echo '<span>&#128100; '.$prenom.'</span>';
echo '<span class="badge-role">'.$role.'</span>';
echo '<span class="badge-xp">&#9889; '.$xp.' XP | Niv. '.$niveau.'</span>';
if ($streak > 1) echo '<span title="Streak">&#128293; '.$streak.'j</span>';
echo '<a class="btn-logout" href="logout.php?logout=true">D&eacute;connexion</a>';
echo '</div></nav>';
echo '<div class="main-content">';
?>
