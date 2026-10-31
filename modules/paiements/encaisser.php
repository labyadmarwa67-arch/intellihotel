<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
require_once '../../config/database.php';

if (isset($_GET['id'])) {
    $id     = (int)$_GET['id'];
    $methode = $conn->real_escape_string($_GET['methode'] ?? 'Especes');

    $conn->query("UPDATE paiements SET 
        statut='paye',
        methode='$methode',
        date_paiement=CURDATE()
        WHERE id=$id");

    header("Location: index.php?success=1");
    exit;
}

$id      = (int)$_GET['paiement_id'];
$paie    = $conn->query("
    SELECT p.*, c.nom as client_nom,
           ch.numero as chambre_num,
           r.date_debut, r.date_fin
    FROM paiements p
    JOIN reservations r ON p.reservation_id=r.id
    JOIN clients c ON r.client_id=c.id
    JOIN chambres ch ON r.chambre_id=ch.id
    WHERE p.id=$id
")->fetch_assoc();

if (!$paie) { header("Location: index.php"); exit; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Encaisser Paiement — IntelliHotel</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{
    font-family:Arial;
    min-height:100vh;
    background:linear-gradient(135deg,#07071a,#1a1a2e);
    display:flex;align-items:center;justify-content:center;
}
.card{
    background:rgba(255,255,255,0.05);
    border:1px solid rgba(255,255,255,0.1);
    border-radius:20px;padding:35px;
    width:500px;
    box-shadow:0 20px 60px rgba(0,0,0,0.5);
}
.card-title{
    color:#f0c040;font-size:18px;font-weight:bold;
    margin-bottom:25px;text-align:center;
    display:flex;align-items:center;justify-content:center;gap:10px;
}

/* Info Box */
.info-box{
    background:rgba(255,255,255,0.04);
    border:1px solid rgba(255,255,255,0.08);
    border-radius:14px;padding:20px;margin-bottom:25px;
}
.info-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;}
.info-row:last-child{margin-bottom:0;}
.info-lbl{color:rgba(255,255,255,0.4);font-size:12px;}
.info-val{color:white;font-size:14px;font-weight:bold;}

/* Montant */
.montant-box{
    background:linear-gradient(135deg,rgba(240,192,64,0.2),rgba(240,192,64,0.05));
    border:1px solid rgba(240,192,64,0.4);
    border-radius:14px;padding:20px;
    text-align:center;margin-bottom:25px;
}
.montant-lbl{color:rgba(255,255,255,0.5);font-size:12px;margin-bottom:8px;}
.montant-val{font-size:36px;font-weight:bold;color:#f0c040;}

/* Méthode */
.methode-title{color:rgba(255,255,255,0.5);font-size:12px;margin-bottom:12px;text-transform:uppercase;letter-spacing:1px;}
.methodes{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:25px;}
.methode-btn{
    border-radius:12px;padding:14px;text-align:center;
    cursor:pointer;transition:0.2s;
    background:rgba(255,255,255,0.04);
    border:2px solid rgba(255,255,255,0.1);
    color:rgba(255,255,255,0.6);font-size:12px;
    text-decoration:none;display:block;
}
.methode-btn:hover,.methode-btn.selected{
    border-color:#f0c040;
    background:rgba(240,192,64,0.1);
    color:#f0c040;
}
.methode-ico{font-size:24px;margin-bottom:6px;}

.btn-confirm{
    width:100%;padding:14px;
    background:linear-gradient(135deg,#28a745,#20c997);
    color:white;border:none;border-radius:12px;
    font-size:15px;font-weight:bold;cursor:pointer;
    transition:0.3s;letter-spacing:1px;
    text-decoration:none;display:block;text-align:center;
}
.btn-confirm:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(40,167,69,0.4);}
.btn-back{
    display:block;text-align:center;margin-top:12px;
    color:rgba(255,255,255,0.4);text-decoration:none;font-size:13px;
}
</style>
</head>
<body>
<div class="card">
    <div class="card-title">💰 Encaisser le Paiement</div>

    <!-- Info -->
    <div class="info-box">
        <div class="info-row">
            <span class="info-lbl">👤 Client</span>
            <span class="info-val"><?= $paie['client_nom'] ?></span>
        </div>
        <div class="info-row">
            <span class="info-lbl">🚪 Chambre</span>
            <span class="info-val">N° <?= $paie['chambre_num'] ?></span>
        </div>
        <div class="info-row">
            <span class="info-lbl">📅 Séjour</span>
            <span class="info-val"><?= $paie['date_debut'] ?> → <?= $paie['date_fin'] ?></span>
        </div>
    </div>

    <!-- Montant -->
    <div class="montant-box">
        <div class="montant-lbl">Montant à Encaisser</div>
        <div class="montant-val"><?= number_format($paie['montant'],2,',',' ') ?> MAD</div>
    </div>

    <!-- Méthode -->
    <div class="methode-title">Choisir la méthode de paiement</div>
    <div class="methodes">
        <a href="encaisser.php?id=<?= $paie['id'] ?>&methode=Especes" class="methode-btn">
            <div class="methode-ico">💵</div>
            Espèces
        </a>
        <a href="encaisser.php?id=<?= $paie['id'] ?>&methode=Carte bancaire" class="methode-btn">
            <div class="methode-ico">💳</div>
            Carte
        </a>
        <a href="encaisser.php?id=<?= $paie['id'] ?>&methode=Virement" class="methode-btn">
            <div class="methode-ico">🏦</div>
            Virement
        </a>
    </div>

    <a href="index.php" class="btn-back">← Annuler</a>
</div>
</body>
</html>