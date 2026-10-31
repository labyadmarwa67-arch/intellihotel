<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../modules/auth/login.php");
    exit;
}
require_once '../../config/database.php';

$id = (int)$_GET['id'];
$chambre = $conn->query("SELECT * FROM chambres WHERE id=$id")->fetch_assoc();

if (!$chambre) {
    header("Location: index.php");
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero    = (int)$_POST['numero'];
    $type      = $conn->real_escape_string($_POST['type']);
    $vue       = $conn->real_escape_string($_POST['vue']);
    $prix_base = (float)$_POST['prix_base'];
    $etat      = $conn->real_escape_string($_POST['etat']);

    $conn->query("UPDATE chambres SET 
                  numero=$numero, type='$type', vue='$vue',
                  prix_base=$prix_base, etat='$etat'
                  WHERE id=$id");
    $message = "✅ Chambre modifiée avec succès !";
    $chambre = $conn->query("SELECT * FROM chambres WHERE id=$id")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Chambre — IntelliHotel</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: Arial; background: #f0f2f5; }
        .navbar {
            background: #1a1a2e; color: white;
            padding: 15px 30px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .navbar h1 { font-size: 20px; }
        .navbar a { color: #ff6b6b; text-decoration: none; font-size: 14px; }
        .container { padding: 30px; max-width: 600px; margin: 0 auto; }
        h2 { color: #1a1a2e; margin-bottom: 25px; font-size: 22px; }
        .form-card {
            background: white; padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        label {
            display: block; font-size: 13px;
            color: #555; margin-bottom: 6px; font-weight: bold;
        }
        input, select {
            width: 100%; padding: 11px;
            border: 1px solid #ddd; border-radius: 8px;
            margin-bottom: 20px; font-size: 14px;
        }
        input:focus, select:focus {
            outline: none; border-color: #1a1a2e;
        }
        .btn-submit {
            width: 100%; padding: 13px;
            background: #1a1a2e; color: white;
            border: none; border-radius: 8px;
            font-size: 15px; cursor: pointer;
        }
        .btn-submit:hover { background: #16213e; }
        .btn-back {
            display: inline-block; margin-bottom: 20px;
            color: #1a1a2e; text-decoration: none; font-size: 14px;
        }
        .success {
            background: #d4edda; border: 1px solid #c3e6cb;
            color: #155724; padding: 12px; border-radius: 8px;
            margin-bottom: 20px; text-align: center;
        }
    </style>
</head>
<body>
<div class="navbar">
    <h1>🏨 IntelliHotel</h1>
    <a href="../../index.php">🏠 Dashboard</a>
</div>

<div class="container">
    <a href="index.php" class="btn-back">← Retour aux chambres</a>
    <h2>✏️ Modifier Chambre N° <?= $chambre['numero'] ?></h2>

    <?php if($message): ?>
        <div class="success"><?= $message ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST">
            <label>🔢 Numéro de chambre</label>
            <input type="number" name="numero" 
                   value="<?= $chambre['numero'] ?>" required>

            <label>🛏️ Type de chambre</label>
            <select name="type" required>
                <option value="Single"   <?= $chambre['type']=='Single' ? 'selected':'' ?>>🛏️ Single</option>
                <option value="Double"   <?= $chambre['type']=='Double' ? 'selected':'' ?>>🛏️🛏️ Double</option>
                <option value="Suite"    <?= $chambre['type']=='Suite'  ? 'selected':'' ?>>👑 Suite</option>
            </select>

            <label>🌅 Vue</label>
            <select name="vue" required>
                <option value="Mer"     <?= $chambre['vue']=='Mer'     ? 'selected':'' ?>>🌊 Vue sur Mer</option>
                <option value="Jardin"  <?= $chambre['vue']=='Jardin'  ? 'selected':'' ?>>🌿 Vue sur Jardin</option>
                <option value="Piscine" <?= $chambre['vue']=='Piscine' ? 'selected':'' ?>>🏊 Vue sur Piscine</option>
                <option value="Ville"   <?= $chambre['vue']=='Ville'   ? 'selected':'' ?>>🏙️ Vue sur Ville</option>
            </select>

            <label>💰 Prix de base (MAD/nuit)</label>
            <input type="number" name="prix_base" 
                   value="<?= $chambre['prix_base'] ?>" required step="0.01">

            <label>📊 État</label>
            <select name="etat" required>
                <option value="vide"     <?= $chambre['etat']=='vide'     ? 'selected':'' ?>>🟢 Disponible</option>
                <option value="reservee" <?= $chambre['etat']=='reservee' ? 'selected':'' ?>>🔴 Réservée</option>
                <option value="occupee"  <?= $chambre['etat']=='occupee'  ? 'selected':'' ?>>🟡 Occupée</option>
            </select>

            <button type="submit" class="btn-submit">
                💾 Enregistrer les modifications
            </button>
        </form>
    </div>
</div>
</body>
</html>