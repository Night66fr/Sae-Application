<?php
// ============================================================
//  LevelUp – genhash.php
//  UTILITAIRE TEMPORAIRE – À SUPPRIMER APRÈS USAGE !
//  Ouvrir dans le navigateur pour générer le hash bcrypt.
// ============================================================
$mdp  = 'secret'; // ← changer ici si vous voulez un autre mot de passe
$hash = password_hash($mdp, PASSWORD_BCRYPT);
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"/><title>GénHash</title>
<style>
body{font-family:monospace;background:#0f0f1a;color:#e0e0e0;display:flex;
  flex-direction:column;align-items:center;justify-content:center;min-height:100vh;gap:16px}
.box{background:#16213e;border:1px solid #00d4ff;border-radius:8px;padding:20px 28px;max-width:600px;word-break:break-all}
p{color:#888;font-size:.85rem}
.warn{color:#e94560;font-weight:bold}
</style>
</head>
<body>
<h2 style="color:#00d4ff">&#9889; Hash généré</h2>
<div class="box">
  <b>Mot de passe :</b> <?= htmlspecialchars($mdp) ?><br/><br/>
  <b>Hash bcrypt :</b><br/><?= htmlspecialchars($hash) ?>
</div>
<p>Copiez ce hash puis dans phpMyAdmin → SQL :</p>
<div class="box">
  UPDATE users SET pass='<?= htmlspecialchars($hash) ?>' WHERE 1;
</div>
<p class="warn">&#9888; Supprimez ce fichier du serveur immédiatement après !</p>
</body>
</html>
