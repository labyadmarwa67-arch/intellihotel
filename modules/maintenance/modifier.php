<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../modules/auth/login.php");
    exit;
}
require_once '../../config/database.php';

$id = (int)$_GET['id'];
$m  = $conn->query("SELECT * FROM maintenance WHERE id=$id")->fetch_assoc();
if (!$m) { header("Location: index.php"); exit; }

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $technicien_id = (int)$_POST['technicien_id'];
    $description   = $conn->real_escape_string($_POST['description']);
    $statut        = $conn->real_escape_string($_POST['statut']);
    $date_debut    = $_POST['date_debut'];
    $date_fin      = $_POST['date_fin'] ?: 'NULL';

    if ($date_fin !== 'NULL') {
        $conn->query("UPDATE maintenance SET technicien_id=$technicien_id,
            description='$description', statut='$statut',
            date_debut='$date_debut', date_fin='$date_fin' WHERE id=$id");
    } else {
        $conn->query("UPDATE maintenance SET technicien_id=$technicien_id,
            description='$description', statut='$statut',
            date_debut='$date_debut', date_fin=NULL WHERE id=$id");
    }

    if ($statut === 'terminee') {
        $conn->query("UPDATE chambres SET etat='vide' WHERE id={$m['chambre_id']}");
    }

    $message = "success|✅ Intervention modifiée avec succès !";
    $m = $conn->query("SELECT * FROM maintenance WHERE id=$id")->fetch_assoc();
}

$techniciens = $conn->query("SELECT * FROM utilisateurs WHERE role='technicien'");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Intervention — IntelliHotel</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:Arial; min-height:100vh; background:linear-gradient(135deg,#1a1a2e,#16213e,#0f3460); }
        .navbar { background:rgba(26,26,46,0.98); color:white; padding:15px 30px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #f0c040; }
        .navbar h1 { font-size:20px; }
        .navbar a { color:#f0c040; text-decoration:none; font-size:14px; }
        .container { padding:30px; max-width:650px; margin:0 auto; }
        .btn-back { display:inline-block; margin-bottom:20px; color:#f0c040; text-decoration:none; font-size:14px; }
        h2 { color:white; margin-bottom:25px; font-size:24px; }
        .form-card { background:rgba(255,255,255,0.95); padding:30px; border-radius:16px; box-shadow:0 8px 32px rgba(0,0,0,0.3); }
        .section-title { font-size:15px; font-weight:bold; color:#1a1a2e; margin-bottom:15px; padding-bottom:8px; border-bottom:2px solid #f0c040; }
        label { display:block; font-size:13px; color:#555; margin-bottom:6px; font-weight:bold; }
        select, input, textarea { width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; margin-bottom:20px; font-size:14px; color:#333; }
        textarea { height:100px; resize:vertical; }
        select:focus, input:focus, textarea:focus { outline:none; border-color:#f0c040; }
        .row { display:grid; grid-template-columns:1fr 1fr; gap:15px; }
        .btn-submit { width:100%; padding:14px; background:linear-gradient(135deg,#f0c040,#e0a020); color:#1a1a2e; border:none; border-radius:10px; font-size:15px; font-weight:bold; cursor:pointer; }
        .success { background:#d4edda; border:1px solid #c3e6cb; color:#155724; padding:15px; border-radius:10px; margin-bottom:20px; text-align:center; }
    </style>
</head>
<body>
<div class="navbar">
    <h1>🏨 IntelliHotel</h1>
    <a href="../../index.php">🏠 Dashboard</a>
</div>

<div class="container">
    <a href="index.php" class="btn-back">← Retour</a>
    <h2>✏️ Modifier Intervention #<?= $id ?></h2>

    <?php if($message):
        [$type, $texte] = explode('|', $message);
    ?>
        <div class="<?= $type ?>"><?= $texte ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST">
            <div class="section-title">👷 Technicien</div>
            <label>Assigner un technicien</label>
            <select name="technicien_id" required>
                <?php while($t = $techniciens->fetch_assoc()): ?>
                    <option value="<?= $t['id'] ?>" <?= $m['technicien_id']==$t['id'] ? 'selected':'' ?>>
                        🔧 <?= $t['nom'] ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <div class="section-title">📝 Détails</div>
            <label>Description</label>
            <textarea name="description" required><?= $m['description'] ?></textarea>

            <label>📊 Statut</label>
            <select name="statut" required>
                <option value="en_cours"  <?= $m['statut']=='en_cours'  ? 'selected':'' ?>>⚙️ En Cours</option>
                <option value="terminee"  <?= $m['statut']=='terminee'  ? 'selected':'' ?>>✅ Terminée</option>
                <option value="prolongee" <?= $m['statut']=='prolongee' ? 'selected':'' ?>>⚠️ Prolongée</option>
            </select>

            <div class="section-title">📅 Dates</div>
            <div class="row">
                <div>
                    <label>Date Début</label>
                    <input type="date" name="date_debut" value="<?= $m['date_debut'] ?>" required>
                </div>
                <div>
                    <label>Date Fin</label>
                    <input type="date" name="date_fin" value="<?= $m['date_fin'] ?? '' ?>">
                </div>
            </div>

            <button type="submit" class="btn-submit">
                💾 ENREGISTRER
            </button>
        </form>
    </div>
</div>
</body>
</html>