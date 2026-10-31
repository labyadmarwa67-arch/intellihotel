<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../modules/auth/login.php");
    exit;
}
require_once '../../config/database.php';

$id = (int)$_GET['id'];
$res = $conn->query("
    SELECT r.*, c.nom as client_nom, ch.numero as chambre_num
    FROM reservations r
    JOIN clients c ON r.client_id = c.id
    JOIN chambres ch ON r.chambre_id = ch.id
    WHERE r.id = $id
")->fetch_assoc();

if (!$res) {
    header("Location: index.php");
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_debut = $_POST['date_debut'];
    $date_fin   = $_POST['date_fin'];
    $statut     = $conn->real_escape_string($_POST['statut']);

    // تحقق التواريخ
    if ($date_fin <= $date_debut) {
        $message = "erreur|❌ La date de fin doit être après la date de début !";
    } else {
        $conn->query("UPDATE reservations SET 
            date_debut='$date_debut', 
            date_fin='$date_fin',
            statut='$statut'
            WHERE id=$id");
        $message = "success|✅ Réservation modifiée avec succès !";
        $res = $conn->query("
            SELECT r.*, c.nom as client_nom, ch.numero as chambre_num
            FROM reservations r
            JOIN clients c ON r.client_id = c.id
            JOIN chambres ch ON r.chambre_id = ch.id
            WHERE r.id = $id
        ")->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Réservation — IntelliHotel</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: Arial;
            min-height: 100vh;
            background: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460);
        }
        .navbar {
            background: rgba(26,26,46,0.98);
            color: white; padding: 15px 30px;
            display: flex; justify-content: space-between; align-items: center;
            border-bottom: 2px solid #f0c040;
        }
        .navbar h1 { font-size: 20px; }
        .navbar a { color: #f0c040; text-decoration: none; font-size: 14px; }
        .container { padding: 30px; max-width: 650px; margin: 0 auto; }
        .btn-back {
            display: inline-block; margin-bottom: 20px;
            color: #f0c040; text-decoration: none; font-size: 14px;
        }
        h2 { color: white; margin-bottom: 25px; font-size: 24px; }

        /* Info Box */
        .info-box {
            background: rgba(240,192,64,0.15);
            border: 1px solid rgba(240,192,64,0.4);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
            text-align: center;
        }
        .info-item .label { color: rgba(255,255,255,0.6); font-size: 11px; margin-bottom: 5px; }
        .info-item .value { color: #f0c040; font-size: 16px; font-weight: bold; }

        /* Form */
        .form-card {
            background: rgba(255,255,255,0.95);
            padding: 30px; border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }
        .section-title {
            font-size: 15px; font-weight: bold;
            color: #1a1a2e; margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #f0c040;
        }
        label { display: block; font-size: 13px; color: #555; margin-bottom: 6px; font-weight: bold; }
        select, input {
            width: 100%; padding: 12px;
            border: 1px solid #ddd; border-radius: 8px;
            margin-bottom: 20px; font-size: 14px; color: #333;
        }
        select:focus, input:focus { outline: none; border-color: #f0c040; }
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .btn-submit {
            width: 100%; padding: 14px;
            background: linear-gradient(135deg, #f0c040, #e0a020);
            color: #1a1a2e; border: none; border-radius: 10px;
            font-size: 15px; font-weight: bold; cursor: pointer;
            transition: 0.3s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(240,192,64,0.4);
        }
        .success {
            background: #d4edda; border: 1px solid #c3e6cb;
            color: #155724; padding: 15px; border-radius: 10px;
            margin-bottom: 20px; text-align: center;
        }
        .erreur {
            background: #f8d7da; border: 1px solid #f5c6cb;
            color: #721c24; padding: 15px; border-radius: 10px;
            margin-bottom: 20px; text-align: center;
        }

        /* Prix */
        .prix-total {
            background: linear-gradient(135deg, #f0c040, #e0a020);
            padding: 15px; border-radius: 10px;
            text-align: center; margin-bottom: 20px;
        }
        .prix-total span { font-size: 22px; font-weight: bold; color: #1a1a2e; }
    </style>
</head>
<body>
<div class="navbar">
    <h1>🏨 IntelliHotel</h1>
    <a href="../../index.php">🏠 Dashboard</a>
</div>

<div class="container">
    <a href="index.php" class="btn-back">← Retour aux réservations</a>
    <h2>✏️ Modifier Réservation #<?= $res['id'] ?></h2>

    <?php if($message):
        [$type, $texte] = explode('|', $message);
    ?>
        <div class="<?= $type ?>"><?= $texte ?></div>
    <?php endif; ?>

    <!-- Info Réservation -->
    <div class="info-box">
        <div class="info-item">
            <div class="label">👤 Client</div>
            <div class="value"><?= $res['client_nom'] ?></div>
        </div>
        <div class="info-item">
            <div class="label">🚪 Chambre</div>
            <div class="value">N° <?= $res['chambre_num'] ?></div>
        </div>
        <div class="info-item">
            <div class="label">💰 Prix Total</div>
            <div class="value"><?= number_format($res['prix_total'], 0) ?> MAD</div>
        </div>
    </div>

    <div class="form-card">
        <form method="POST">

            <div class="section-title">📆 Modifier les Dates</div>
            <div class="row">
                <div>
                    <label>📅 Date de début</label>
                    <input type="date" name="date_debut"
                           value="<?= $res['date_debut'] ?>" required>
                </div>
                <div>
                    <label>📅 Date de fin</label>
                    <input type="date" name="date_fin"
                           value="<?= $res['date_fin'] ?>" required>
                </div>
            </div>

            <div class="section-title">📊 Statut</div>
            <label>Statut de la réservation</label>
            <select name="statut" required>
                <option value="confirmee"  <?= $res['statut']=='confirmee'  ? 'selected':'' ?>>✅ Confirmée</option>
                <option value="checkin"    <?= $res['statut']=='checkin'    ? 'selected':'' ?>>🟢 Check-in</option>
                <option value="checkout"   <?= $res['statut']=='checkout'   ? 'selected':'' ?>>⬜ Check-out</option>
                <option value="annulee"    <?= $res['statut']=='annulee'    ? 'selected':'' ?>>❌ Annulée</option>
                <option value="en_attente" <?= $res['statut']=='en_attente' ? 'selected':'' ?>>⏳ En attente</option>
            </select>

            <button type="submit" class="btn-submit">
                💾 ENREGISTRER LES MODIFICATIONS
            </button>
        </form>
    </div>
</div>
</body>
</html>