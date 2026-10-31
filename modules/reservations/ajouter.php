<<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../modules/auth/login.php");
    exit;
}
require_once '../../config/database.php';

// دالة حساب السعر الذكي
function calculerPrix($conn, $chambre_id, $date_debut, $date_fin) {
    $ch = $conn->query("SELECT * FROM chambres WHERE id=$chambre_id")->fetch_assoc();
    
    // معامل Vue
    $coef_vue = ['Mer'=>1.30, 'Jardin'=>1.00, 'Piscine'=>1.15, 'Ville'=>1.10];
    $cv = $coef_vue[$ch['vue']] ?? 1.0;
    
    // معامل Saison
    $mois = (int)date('m', strtotime($date_debut));
    $saison = $conn->query("SELECT coefficient FROM saisons 
        WHERE (mois_debut <= $mois AND mois_fin >= $mois)
        OR (mois_debut > mois_fin AND ($mois >= mois_debut OR $mois <= mois_fin))"
    )->fetch_assoc();
    $cs = $saison ? $saison['coefficient'] : 1.0;
    
    // عدد الليالي
    $nuits = max(1, (strtotime($date_fin) - strtotime($date_debut)) / 86400);
    
    return round($ch['prix_base'] * $cv * $cs * $nuits, 2);
}

$message = '';
$prix_calcule = 0;

// حساب السعر بـ AJAX
if (isset($_GET['calculer'])) {
    $chambre_id = (int)$_GET['chambre_id'];
    $date_debut = $_GET['date_debut'];
    $date_fin   = $_GET['date_fin'];
    if ($chambre_id && $date_debut && $date_fin) {
        echo calculerPrix($conn, $chambre_id, $date_debut, $date_fin);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id  = (int)$_POST['client_id'];
    $chambre_id = (int)$_POST['chambre_id'];
    $date_debut = $_POST['date_debut'];
    $date_fin   = $_POST['date_fin'];
    $receptionniste_id = (int)$_POST['receptionniste_id'];

    // تحقق Double Booking
    $check = $conn->query("SELECT id FROM reservations 
        WHERE chambre_id=$chambre_id 
        AND statut NOT IN ('annulee','checkout')
        AND NOT (date_fin <= '$date_debut' OR date_debut >= '$date_fin')");

    if ($check->num_rows > 0) {
        $message = "❌ Double Réservation ! Chambre déjà réservée pour ces dates !";
    } elseif ($date_fin <= $date_debut) {
        $message = "❌ La date de fin doit être après la date de début !";
    } elseif ($receptionniste_id <= 0) {
        $message = "❌ Veuillez sélectionner un réceptionniste !";
    } else {
        $prix = calculerPrix($conn, $chambre_id, $date_debut, $date_fin);
        
        // ✅ التصحيح هنا: استخدم $receptionniste_id وليس $recep_id
        $conn->query("INSERT INTO reservations 
            (client_id, chambre_id, receptionniste_id, date_debut, date_fin, prix_total, statut)
            VALUES ($client_id, $chambre_id, $receptionniste_id, '$date_debut', '$date_fin', $prix, 'confirmee')");
        $res_id = $conn->insert_id;
        
        // تحديث حالة الغرفة
        $conn->query("UPDATE chambres SET etat='reservee' WHERE id=$chambre_id");
        
        // إنشاء paiement
        $conn->query("INSERT INTO paiements (reservation_id, montant, statut) 
                      VALUES ($res_id, $prix, 'en_attente')");

        $message = "✅ Réservation créée ! Prix total : " . number_format($prix, 2) . " MAD";
    }
}

$clients  = $conn->query("SELECT * FROM clients ORDER BY nom");
$chambres = $conn->query("SELECT * FROM chambres WHERE etat='vide' ORDER BY numero");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouvelle Réservation — IntelliHotel</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: url('https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&dpr=2') no-repeat center center fixed;
            background-size: cover;
            position: relative;
        }
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.55);
            backdrop-filter: blur(1px);
            z-index: -1;
        }
        .navbar {
            background: rgba(10, 12, 21, 0.88);
            backdrop-filter: blur(12px);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #f0c040;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .navbar h1 { font-size: 24px; letter-spacing: 1px; color: white; }
        .navbar h1 span { color: #f0c040; }
        .navbar a {
            color: #f0c040;
            text-decoration: none;
            font-size: 14px;
            padding: 8px 16px;
            border-radius: 30px;
            transition: 0.2s;
        }
        .navbar a:hover { background: rgba(240,192,64,0.2); }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
        }
        .btn-back {
            display: inline-block;
            margin-bottom: 20px;
            color: white;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
            padding: 8px 18px;
            border-radius: 40px;
            text-decoration: none;
            font-size: 14px;
            transition: 0.2s;
        }
        .btn-back:hover { background: rgba(0,0,0,0.7); }
        h2 {
            color: #f0c040;
            font-size: 28px;
            margin-bottom: 25px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-card {
            background: rgba(20, 20, 35, 0.75);
            backdrop-filter: blur(10px);
            padding: 35px;
            border-radius: 28px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            border: 1px solid rgba(240,192,64,0.3);
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #f0c040;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid rgba(240,192,64,0.4);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        label {
            display: block;
            font-size: 13px;
            font-weight: bold;
            color: rgba(255,255,255,0.8);
            margin-bottom: 6px;
        }
        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 16px;
            margin-bottom: 20px;
            font-size: 14px;
            background: rgba(255,255,255,0.9);
            transition: 0.2s;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #f0c040;
            box-shadow: 0 0 0 3px rgba(240,192,64,0.3);
        }
        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border: none;
            border-radius: 40px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            background: linear-gradient(135deg, #2a5298 0%, #1e3c72 100%);
        }
        .prix-box {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(240,192,64,0.5);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: center;
        }
        .prix-box .label {
            color: rgba(255,255,255,0.8);
            font-size: 13px;
            margin-bottom: 8px;
        }
        .prix-box .montant {
            font-size: 34px;
            font-weight: bold;
            color: #f0c040;
            text-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
        .prix-box .details {
            font-size: 11px;
            color: rgba(255,255,255,0.6);
            margin-top: 8px;
        }
        .success {
            background: #155724cc;
            border-left: 5px solid #28a745;
            color: white;
            padding: 12px;
            border-radius: 16px;
            margin-bottom: 20px;
            font-weight: bold;
            backdrop-filter: blur(4px);
        }
        .erreur {
            background: #721c24cc;
            border-left: 5px solid #dc3545;
            color: white;
            padding: 12px;
            border-radius: 16px;
            margin-bottom: 20px;
            font-weight: bold;
            backdrop-filter: blur(4px);
        }
        @media (max-width: 650px) {
            .container { padding: 20px; }
            .form-card { padding: 20px; }
            .row { grid-template-columns: 1fr; gap: 0; }
            h2 { font-size: 24px; }
        }
    </style>
</head>
<body>
<div class="navbar">
    <h1>🏨 <span>IntelliHotel</span></h1>
    <a href="../../index.php">🏠 Tableau de bord</a>
</div>

<div class="container">
    <a href="index.php" class="btn-back">← Retour aux réservations</a>
    <h2>📅 Nouvelle Réservation</h2>

    <?php if($message): ?>
        <div class="<?= str_starts_with($message,'✅') ? 'success' : 'erreur' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <div class="form-card">

        <!-- Prix calculé automatiquement -->
        <div class="prix-box">
            <div class="label">💰 Prix Total Calculé</div>
            <div class="montant" id="prixTotal">— MAD</div>
            <div class="details">Prix Base × Vue × Saison × Nuits</div>
        </div>

        <form method="POST" id="formReservation">

            <div class="section-title">👤 Client</div>
            <label>Sélectionner un client</label>
            <select name="client_id" required>
                <option value="">-- Choisir un client --</option>
                <?php while($cl = $clients->fetch_assoc()): ?>
                    <option value="<?= $cl['id'] ?>">
                        <?= htmlspecialchars($cl['nom']) ?> — CIN: <?= htmlspecialchars($cl['cin']) ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <div class="section-title">🚪 Chambre</div>
            <label>Sélectionner une chambre disponible</label>
            <select name="chambre_id" id="chambre_id" required onchange="calculerPrix()">
                <option value="">-- Choisir une chambre --</option>
                <?php while($ch = $chambres->fetch_assoc()): ?>
                    <option value="<?= $ch['id'] ?>">
                        N°<?= $ch['numero'] ?> — <?= $ch['type'] ?> / 
                        <?= $ch['vue'] ?> — <?= $ch['prix_base'] ?> MAD/nuit
                    </option>
                <?php endwhile; ?>
            </select>

            <div class="section-title">📆 Dates</div>
            <div class="row">
                <div>
                    <label>Date de début</label>
                    <input type="date" name="date_debut" id="date_debut"
                           min="<?= date('Y-m-d') ?>" required onchange="calculerPrix()">
                </div>
                <div>
                    <label>Date de fin</label>
                    <input type="date" name="date_fin" id="date_fin"
                           min="<?= date('Y-m-d') ?>" required onchange="calculerPrix()">
                </div>
            </div>

            <label>Réceptionniste</label>
            <select name="receptionniste_id" required style="width:100%; padding:12px; border-radius:10px; margin-bottom:20px;">
                <option value="">Choisir Réceptionniste</option>
                <?php
                $req = $conn->query("SELECT id, nom FROM utilisateurs WHERE role='receptionniste'");
                while($u = $req->fetch_assoc()){
                ?>
                    <option value="<?= $u['id'] ?>"><?= $u['nom'] ?></option>
                <?php } ?>
            </select>
            
            <button type="submit" class="btn-submit">
                ✅ Confirmer la Réservation
            </button>
        </form>
    </div>
</div>

<script>
function calculerPrix() {
    const chambre = document.getElementById('chambre_id').value;
    const debut   = document.getElementById('date_debut').value;
    const fin     = document.getElementById('date_fin').value;

    if (chambre && debut && fin && fin > debut) {
        fetch(`ajouter.php?calculer=1&chambre_id=${chambre}&date_debut=${debut}&date_fin=${fin}`)
            .then(r => r.text())
            .then(prix => {
                document.getElementById('prixTotal').innerText = 
                    parseFloat(prix).toLocaleString('fr-MA') + ' MAD';
            });
    } else {
        document.getElementById('prixTotal').innerText = '— MAD';
    }
}
</script>
</body>
</html>