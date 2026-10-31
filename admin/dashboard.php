<?php
// اتصال مباشر بدون session
$conn = new mysqli('localhost', 'root', '', 'intellihotel');
$conn->set_charset("utf8mb4");

// ===== DATA =====
$total_revenus   = $conn->query("SELECT SUM(montant) as t FROM paiements WHERE statut='paye'")->fetch_assoc()['t'] ?? 0;
$revenus_attente = $conn->query("SELECT SUM(montant) as t FROM paiements WHERE statut='en_attente'")->fetch_assoc()['t'] ?? 0;
$pertes          = $conn->query("SELECT SUM(prix_total) as t FROM reservations WHERE statut='annulee'")->fetch_assoc()['t'] ?? 0;
$total_res       = $conn->query("SELECT COUNT(*) as n FROM reservations")->fetch_assoc()['n'];
$res_annulees    = $conn->query("SELECT COUNT(*) as n FROM reservations WHERE statut='annulee'")->fetch_assoc()['n'];
$total_clients   = $conn->query("SELECT COUNT(*) as n FROM clients")->fetch_assoc()['n'];
$total_chambres  = $conn->query("SELECT COUNT(*) as n FROM chambres")->fetch_assoc()['n'];
$chambres_vides  = $conn->query("SELECT COUNT(*) as n FROM chambres WHERE etat='vide'")->fetch_assoc()['n'];
$chambres_occupees = $conn->query("SELECT COUNT(*) as n FROM chambres WHERE etat='occupee'")->fetch_assoc()['n'];
$taux_occupation = $total_chambres > 0 ? round(($chambres_occupees/$total_chambres)*100,1) : 0;
$profit_net      = $total_revenus - $pertes;
$cout_maintenance = $conn->query("SELECT SUM(DATEDIFF(IFNULL(date_fin,CURDATE()),date_debut)*500) as t FROM maintenance")->fetch_assoc()['t'] ?? 0;
$profit_final    = $profit_net - $cout_maintenance;
$revenu_par_chambre = $total_chambres > 0 ? round($total_revenus/$total_chambres,2) : 0;
$revenu_par_client  = $total_clients  > 0 ? round($total_revenus/$total_clients,2)  : 0;

// Meilleur mois
$best = $conn->query("SELECT DATE_FORMAT(date_paiement,'%M %Y') as mois, SUM(montant) as total FROM paiements WHERE statut='paye' GROUP BY YEAR(date_paiement),MONTH(date_paiement) ORDER BY total DESC LIMIT 1")->fetch_assoc();

// Revenus par mois
$mois_q = $conn->query("SELECT DATE_FORMAT(date_paiement,'%b') as mois, SUM(montant) as rev FROM paiements WHERE statut='paye' GROUP BY YEAR(date_paiement),MONTH(date_paiement) ORDER BY YEAR(date_paiement),MONTH(date_paiement)");
$m_l=$m_r=[];
while($r=$mois_q->fetch_assoc()){$m_l[]=$r['mois'];$m_r[]=$r['rev'];}

$receps = $conn->query("
    SELECT 
        u.id,
        u.nom,
        u.email,
        COUNT(r.id) as nb,
        SUM(CASE WHEN r.statut != 'annulee' THEN r.prix_total ELSE 0 END) as ca,
        SUM(CASE WHEN r.statut = 'checkout' THEN r.prix_total ELSE 0 END) as encaisse,
        COUNT(CASE WHEN r.statut = 'annulee' THEN 1 END) as nb_ann,
        SUM(CASE WHEN r.statut = 'annulee' THEN r.prix_total ELSE 0 END) as perte,
        MAX(r.date_debut) as derniere_reservation_date,
        (SELECT c.nom FROM reservations r2 
         JOIN clients c ON r2.client_id = c.id 
         WHERE r2.receptionniste_id = u.id 
         ORDER BY r2.date_debut DESC LIMIT 1) as dernier_client,
        (SELECT ch.numero FROM reservations r2 
         JOIN chambres ch ON r2.chambre_id = ch.id 
         WHERE r2.receptionniste_id = u.id 
         ORDER BY r2.date_debut DESC LIMIT 1) as derniere_chambre,
        (SELECT r2.statut FROM reservations r2 
         WHERE r2.receptionniste_id = u.id 
         ORDER BY r2.date_debut DESC LIMIT 1) as dernier_statut
    FROM utilisateurs u
    LEFT JOIN reservations r ON u.id = r.receptionniste_id
    WHERE u.role = 'receptionniste'
    GROUP BY u.id, u.nom, u.email
    ORDER BY ca DESC
");
$rd=[];
while($r=$receps->fetch_assoc()) $rd[]=$r;
$rn=$rca=$rp=[];
foreach($rd as $r){ $rn[]  = $r['nom'];
    $rca[] = $r['encaisse'] ?? 0;
    $rp[]  = $r['nb_ann'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard — IntelliHotel</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{
    font-family:'Segoe UI',Arial,sans-serif;
    background:url('https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&dpr=2') no-repeat center center fixed;
    background-size:cover;
    position:relative;
}
body::before{
    content:"";
    position:fixed;
    top:0;left:0;width:100%;height:100%;
    background:rgba(0,0,0,0.65);
    backdrop-filter:blur(3px);
    z-index:-1;
}
body{color:white;}

/* SIDEBAR */
.sidebar{
    position:fixed;left:0;top:0;
    width:260px;height:100vh;
    background:rgba(10,12,21,0.9);
    backdrop-filter:blur(12px);
    border-right:1px solid rgba(212,175,55,0.25);
    z-index:100;overflow-y:auto;
}
.logo{
    padding:25px 20px;text-align:center;
    border-bottom:1px solid rgba(212,175,55,0.2);
}
.logo .icon{font-size:48px;}
.logo h2{color:#d4af37;font-size:18px;letter-spacing:3px;margin-top:8px;}
.logo .stars{color:#d4af37;font-size:12px;margin-top:5px;}
.logo .badge-admin{
    background:rgba(220,53,69,0.25);
    border:1px solid rgba(220,53,69,0.5);
    color:#ff8a8a;font-size:10px;padding:4px 12px;
    border-radius:30px;display:inline-block;margin-top:10px;
}
.nav-sec{
    padding:15px 20px 6px;
    color:rgba(255,255,255,0.35);
    font-size:10px;text-transform:uppercase;letter-spacing:2px;
}
.sidebar a{
    display:flex;align-items:center;gap:12px;
    color:rgba(255,255,255,0.7);text-decoration:none;
    padding:11px 20px;font-size:13px;
    border-left:3px solid transparent;transition:0.2s;
}
.sidebar a:hover,.sidebar a.active{
    background:rgba(212,175,55,0.12);
    color:#d4af37;border-left-color:#d4af37;
}
.sidebar a i{width:18px;text-align:center;}

/* MAIN */
.main{margin-left:260px;padding:25px;min-height:100vh;}

/* TOP */
.topbar{
    display:flex;justify-content:space-between;align-items:center;
    background:rgba(20,20,35,0.6);
    backdrop-filter:blur(10px);
    border:1px solid rgba(212,175,55,0.25);
    border-radius:20px;
    padding:15px 25px;margin-bottom:25px;
}
.topbar h1{font-size:18px;color:#d4af37;}
.topbar .dt{color:rgba(255,255,255,0.5);font-size:12px;margin-top:4px;}
.topbar .user{display:flex;align-items:center;gap:12px;}
.avatar{
    width:44px;height:44px;border-radius:50%;
    background:linear-gradient(135deg,#d4af37,#b8960c);
    display:flex;align-items:center;justify-content:center;
    font-weight:bold;font-size:18px;color:#1a1a2e;
}

/* KPI */
.kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-bottom:25px;}
.kpi{
    border-radius:20px;
    padding:20px;
    transition:0.3s;
    backdrop-filter:blur(8px);
}
.kpi:hover{transform:translateY(-3px);}
.kpi.g{background:rgba(40,167,69,0.15);border:1px solid rgba(40,167,69,0.4);}
.kpi.y{background:rgba(212,175,55,0.15);border:1px solid rgba(212,175,55,0.4);}
.kpi.r{background:rgba(220,53,69,0.15);border:1px solid rgba(220,53,69,0.4);}
.kpi.b{background:rgba(23,162,184,0.15);border:1px solid rgba(23,162,184,0.4);}
.kpi .ico{font-size:32px;margin-bottom:10px;}
.kpi .val{font-size:26px;font-weight:bold;}
.kpi.g .val{color:#28a745;}
.kpi.y .val{color:#d4af37;}
.kpi.r .val{color:#dc3545;}
.kpi.b .val{color:#17a2b8;}
.kpi .lbl{font-size:12px;color:rgba(255,255,255,0.7);margin-top:5px;}
.kpi .sub{font-size:10px;color:rgba(255,255,255,0.4);margin-top:6px;}
.bar{height:4px;background:rgba(255,255,255,0.1);border-radius:4px;margin-top:10px;}
.bar-f{height:100%;border-radius:4px;}

/* BILAN */
.bilan-3{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-bottom:25px;}
.bilan-box{
    border-radius:20px;
    padding:22px;text-align:center;
    backdrop-filter:blur(10px);
}
.bilan-box.profit{background:linear-gradient(135deg,rgba(40,167,69,0.2),rgba(40,167,69,0.05));border:1px solid rgba(40,167,69,0.5);}
.bilan-box.perte{background:linear-gradient(135deg,rgba(220,53,69,0.2),rgba(220,53,69,0.05));border:1px solid rgba(220,53,69,0.5);}
.bilan-box.net{
    background:linear-gradient(135deg,
    <?= $profit_final>=0?'rgba(212,175,55,0.2),rgba(212,175,55,0.05)':'rgba(220,53,69,0.2),rgba(220,53,69,0.05)' ?>);
    border:1px solid rgba(<?= $profit_final>=0?'212,175,55':'220,53,69' ?>,0.5);
}
.bilan-ico{font-size:38px;margin-bottom:12px;}
.bilan-lbl{font-size:12px;color:rgba(255,255,255,0.5);margin-bottom:8px;}
.bilan-val{font-size:28px;font-weight:bold;}
.bilan-box.profit .bilan-val{color:#28a745;}
.bilan-box.perte .bilan-val{color:#dc3545;}
.bilan-box.net .bilan-val{color:<?= $profit_final>=0?'#d4af37':'#dc3545' ?>;}
.bilan-sub{font-size:11px;color:rgba(255,255,255,0.35);margin-top:6px;}

/* Box */
.box{
    background:rgba(20,20,35,0.55);
    backdrop-filter:blur(10px);
    border:1px solid rgba(212,175,55,0.2);
    border-radius:20px;
    padding:22px;
    margin-bottom:20px;
}
.box h3{color:#d4af37;font-size:14px;margin-bottom:18px;display:flex;align-items:center;gap:10px;}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;}
.row3{display:grid;grid-template-columns:3fr 2fr;gap:20px;margin-bottom:20px;}

/* Tables */
.tbl{width:100%;border-collapse:collapse;}
.tbl th{
    background:rgba(0,0,0,0.4);color:#d4af37;
    padding:12px 15px;text-align:left;font-size:11px;
    text-transform:uppercase;border-bottom:1px solid rgba(212,175,55,0.25);
}
.tbl td{
    padding:12px 15px;font-size:13px;
    color:rgba(255,255,255,0.85);
    border-bottom:1px solid rgba(255,255,255,0.05);
}
.tbl tr:hover td{background:rgba(212,175,55,0.08);}
.badge{padding:4px 12px;border-radius:30px;font-size:10px;font-weight:bold;}
.bg{background:rgba(40,167,69,0.25);color:#28a745;}
.by{background:rgba(212,175,55,0.25);color:#d4af37;}
.br{background:rgba(220,53,69,0.25);color:#dc3545;}

/* Réceptionnistes */
.recep-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:20px;margin-bottom:25px;}
.recep-card{
    background:rgba(20,20,35,0.6);
    backdrop-filter:blur(10px);
    border:1px solid rgba(212,175,55,0.2);
    border-radius:20px;
    padding:22px;
    transition:0.3s;
}
.recep-card:hover{border-color:rgba(212,175,55,0.5);transform:translateY(-3px);}
.recep-head{display:flex;align-items:center;gap:15px;margin-bottom:18px;}
.recep-av{
    width:50px;height:50px;border-radius:50%;
    background:linear-gradient(135deg,#d4af37,#b8960c);
    display:flex;align-items:center;justify-content:center;
    color:#1a1a2e;font-weight:bold;font-size:20px;
}
.recep-name{color:#d4af37;font-size:15px;font-weight:bold;}
.recep-badge{
    background:rgba(212,175,55,0.2);color:#d4af37;
    font-size:10px;padding:3px 10px;border-radius:20px;
    margin-top:5px;display:inline-block;
}
.stat4{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-bottom:15px;}
.stat-it{
    background:rgba(255,255,255,0.05);
    border-radius:12px;padding:12px;text-align:center;
}
.stat-v{font-size:20px;font-weight:bold;}
.stat-l{font-size:10px;color:rgba(255,255,255,0.5);margin-top:4px;}
.prog-row{margin-bottom:10px;}
.prog-lbl{display:flex;justify-content:space-between;font-size:10px;color:rgba(255,255,255,0.5);margin-bottom:4px;}
.prog-bar{height:5px;background:rgba(255,255,255,0.1);border-radius:5px;overflow:hidden;}
.prog-fill{height:100%;border-radius:5px;}
.taux-box{
    display:flex;justify-content:space-between;align-items:center;
    background:rgba(255,255,255,0.05);border-radius:12px;padding:10px 14px;margin-top:12px;
}
.taux-box .tl{font-size:11px;color:rgba(255,255,255,0.5);}
.taux-box .tv{font-size:14px;font-weight:bold;}

/* Indicateurs */
.indic-item{
    padding:12px;background:rgba(255,255,255,0.04);
    border-radius:12px;margin-bottom:10px;
}
.indic-lbl{font-size:11px;color:rgba(255,255,255,0.5);margin-bottom:5px;}
.indic-val{font-size:16px;font-weight:bold;}

.sec{font-size:16px;font-weight:bold;margin-bottom:18px;display:flex;align-items:center;gap:10px;color:#d4af37;}
.sec span{color:rgba(255,255,255,0.4);font-size:12px;font-weight:normal;}

@media (max-width:1000px){
    .sidebar{width:80px;}
    .sidebar .logo h2,.sidebar .logo .stars,.sidebar .logo .badge-admin,.nav-sec,.sidebar a span{display:none;}
    .sidebar a i{font-size:20px;}
    .main{margin-left:80px;}
    .kpi-grid{grid-template-columns:repeat(2,1fr);}
    .bilan-3{grid-template-columns:1fr;}
    .row2,.row3{grid-template-columns:1fr;}
    .recep-grid{grid-template-columns:1fr;}
}
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="logo">
        <div class="icon">🏨</div>
        <h2>INTELLI HOTEL</h2>
        <div class="stars">★ ★ ★ ★ ★</div>
        <div class="badge-admin"><i class="fa fa-shield-alt"></i> ADMIN</div>
    </div>
    <div class="nav-sec">Principal</div>
    <a href="../index.php"><i class="fa fa-home"></i> <span>Dashboard</span></a>
    <a href="dashboard.php" class="active"><i class="fa fa-chart-line"></i> <span>Admin Analytics</span></a>
    <div class="nav-sec">Gestion</div>
    <a href="../modules/chambres/index.php"><i class="fa fa-bed"></i> <span>Chambres</span></a>
    <a href="../modules/clients/index.php"><i class="fa fa-users"></i> <span>Clients</span></a>
    <a href="../modules/reservations/index.php"><i class="fa fa-calendar"></i> <span>Réservations</span></a>
    <a href="../modules/paiements/index.php"><i class="fa fa-credit-card"></i> <span>Paiements</span></a>
    <a href="../modules/maintenance/index.php"><i class="fa fa-wrench"></i> <span>Maintenance</span></a>
</div>

<!-- MAIN -->
<div class="main">

    <!-- TOP BAR -->
    <div class="topbar">
        <div>
            <h1><i class="fa fa-chart-line"></i> Admin Dashboard — Profits & Pertes</h1>
            <div class="dt">📅 <?= date('d/m/Y H:i') ?> — Données en temps réel</div>
        </div>
        <div class="user">
            <div class="avatar">A</div>
            <div>
                <div style="color:white;font-size:13px;font-weight:bold">Administrateur</div>
                <div style="color:#d4af37;font-size:11px"><i class="fa fa-shield-alt"></i> Admin</div>
            </div>
        </div>
    </div>

    <!-- KPI -->
    <div class="kpi-grid">
        <div class="kpi g">
            <div class="ico">💰</div>
            <div class="val"><?= number_format($total_revenus,0,',',' ') ?></div>
            <div class="lbl">Revenus Encaissés (MAD)</div>
            <div class="bar"><div class="bar-f" style="width:80%;background:#28a745"></div></div>
        </div>
        <div class="kpi y">
            <div class="ico">⏳</div>
            <div class="val"><?= number_format($revenus_attente,0,',',' ') ?></div>
            <div class="lbl">En Attente (MAD)</div>
            <div class="sub"><?= $total_res-$res_annulees ?> réservations actives</div>
        </div>
        <div class="kpi r">
            <div class="ico">📉</div>
            <div class="val"><?= number_format($pertes,0,',',' ') ?></div>
            <div class="lbl">Pertes — Annulations (MAD)</div>
            <div class="sub"><?= $res_annulees ?> annulation(s)</div>
        </div>
        <div class="kpi b">
            <div class="ico">📊</div>
            <div class="val"><?= $taux_occupation ?>%</div>
            <div class="lbl">Taux d'Occupation</div>
            <div class="bar"><div class="bar-f" style="width:<?= $taux_occupation ?>%;background:#17a2b8"></div></div>
        </div>
    </div>

    <!-- BILAN FINANCIER -->
    <div class="sec">📊 Bilan Financier <span>— Profits & Pertes de l'Hôtel</span></div>
    <div class="bilan-3">
        <div class="bilan-box profit">
            <div class="bilan-ico">💰</div>
            <div class="bilan-lbl">Total Revenus Encaissés</div>
            <div class="bilan-val">+<?= number_format($total_revenus,0,',',' ') ?></div>
            <div class="bilan-sub">MAD — Paiements reçus</div>
        </div>
        <div class="bilan-box perte">
            <div class="bilan-ico">📉</div>
            <div class="bilan-lbl">Total Pertes & Charges</div>
            <div class="bilan-val">-<?= number_format($pertes+$cout_maintenance,0,',',' ') ?></div>
            <div class="bilan-sub">Annulations + Maintenance</div>
        </div>
        <div class="bilan-box net">
            <div class="bilan-ico"><?= $profit_final>=0?'🏆':'⚠️' ?></div>
            <div class="bilan-lbl">Profit Net Final</div>
            <div class="bilan-val"><?= $profit_final>=0?'+':'' ?><?= number_format($profit_final,0,',',' ') ?></div>
            <div class="bilan-sub"><?= $profit_final>=0?'✅ Bénéfice':'❌ Déficit' ?> (MAD)</div>
        </div>
    </div>

    <!-- CHARTS ROW -->
    <div class="row3">
        <div class="box">
            <h3><i class="fa fa-chart-bar"></i> Revenus Mensuels Encaissés</h3>
            <canvas id="c1" height="100"></canvas>
        </div>
        <div class="box">
            <h3><i class="fa fa-chart-pie"></i> Profits vs Pertes</h3>
            <canvas id="c2" height="100"></canvas>
        </div>
    </div>

    <!-- TABLEAU BILAN -->
    <div class="row2">
        <div class="box">
            <h3><i class="fa fa-balance-scale"></i> Tableau Bilan Détaillé</h3>
            <table class="tbl">
                <thead>
                    <tr><th>Élément</th><th>Montant (MAD)</th><th>Type</th></tr>
                </thead>
                <tbody>
                    <tr><td>💰 Revenus encaissés</td><td style="color:#28a745;font-weight:bold">+<?= number_format($total_revenus,0,',',' ') ?></td><td><span class="badge bg">PROFIT</span></td></tr>
                    <tr><td>⏳ En attente</td><td style="color:#d4af37;font-weight:bold">+<?= number_format($revenus_attente,0,',',' ') ?></td><td><span class="badge by">EN COURS</span></td></tr>
                    <tr><td>❌ Pertes annulations</td><td style="color:#dc3545;font-weight:bold">-<?= number_format($pertes,0,',',' ') ?></td><td><span class="badge br">PERTE</span></td></tr>
                    <tr><td>🔧 Coût maintenance</td><td style="color:#dc3545;font-weight:bold">-<?= number_format($cout_maintenance,0,',',' ') ?></td><td><span class="badge br">CHARGE</span></td></tr>
                    <tr style="background:rgba(212,175,55,0.1)">
                        <td><strong style="color:#d4af37">PROFIT NET</strong></td>
                        <td style="font-size:18px;font-weight:bold;color:<?= $profit_final>=0?'#28a745':'#dc3545' ?>"><?= $profit_final>=0?'+':'' ?><?= number_format($profit_final,0,',',' ') ?></td>
                        <td><span class="badge <?= $profit_final>=0?'bg':'br' ?>"><?= $profit_final>=0?'✅ BÉNÉFICE':'❌ DÉFICIT' ?></span></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="box">
            <h3><i class="fa fa-chart-line"></i> Indicateurs Clés</h3>
            <div class="indic-item"><div class="indic-lbl">📈 Meilleur Mois</div><div class="indic-val" style="color:#28a745"><?= $best ? $best['mois'].' — '.number_format($best['total'],0).' MAD' : '—' ?></div></div>
            <div class="indic-item"><div class="indic-lbl">🚪 Revenu Moyen / Chambre</div><div class="indic-val" style="color:#d4af37"><?= number_format($revenu_par_chambre,0) ?> MAD</div></div>
            <div class="indic-item"><div class="indic-lbl">👤 Revenu Moyen / Client</div><div class="indic-val" style="color:#17a2b8"><?= number_format($revenu_par_client,0) ?> MAD</div></div>
            <div class="indic-item"><div class="indic-lbl">📅 Total Réservations</div><div class="indic-val" style="color:#d4af37"><?= $total_res ?> réservation(s)</div></div>
            <div class="indic-item"><div class="indic-lbl">👥 Total Clients</div><div class="indic-val" style="color:#d4af37"><?= $total_clients ?> client(s)</div></div>
        </div>
    </div>

    <!-- RÉCEPTIONNISTES -->
    <div class="sec">👨‍💼 Performance Réceptionnistes <span>— CA & Pertes par employé</span></div>
    <div class="recep-grid">
    <?php foreach($rd as $i => $r):
        $medals=['🥇','🥈','🥉'];
        $m=$medals[$i]??'🏅';
        $taux=$r['nb']>0?round((($r['nb']-$r['nb_ann'])/$r['nb'])*100):0;
        $ca=$r['encaisse']??0;
    ?>
    <div class="recep-card">
        <div class="recep-head">
            <div class="recep-av"><?= strtoupper(substr($r['nom'],0,1)) ?></div>
            <div><div class="recep-name"><?= $m ?> <?= htmlspecialchars($r['nom']) ?></div><div class="recep-badge"><i class="fa fa-star"></i> Réceptionniste</div></div>
        </div>
        <div class="stat4">
            <div class="stat-it"><div class="stat-v" style="color:#d4af37"><?= $r['nb'] ?></div><div class="stat-l">Total Résa</div></div>
            <div class="stat-it"><div class="stat-v" style="color:#17a2b8"><?= $r['nb']-$r['nb_ann'] ?></div><div class="stat-l">Réussies</div></div>
            <div class="stat-it"><div class="stat-v" style="color:#28a745"><?= number_format($r['encaisse'],0) ?></div><div class="stat-l">Encaissé MAD</div></div>
            <div class="stat-it"><div class="stat-v" style="color:#dc3545"><?= $r['nb_ann'] ?></div><div class="stat-l">Annulées</div></div>
        </div>
        <div class="prog-row">
            <div class="prog-lbl"><span>💰 CA Généré</span><span style="color:#d4af37">+<?= number_format($ca,0) ?> MAD</span></div>
            <div class="prog-bar"><div class="prog-fill" style="width:<?= $ca>0?round(($r['encaisse']/$ca)*100):0 ?>%;background:#d4af37"></div></div>
        </div>
        <div class="prog-row">
            <div class="prog-lbl"><span>📉 Pertes</span><span style="color:#dc3545">-<?= number_format($r['perte'],0) ?> MAD</span></div>
            <div class="prog-bar"><div class="prog-fill" style="width:<?= $ca>0?round(($r['perte']/$ca)*100):0 ?>%;background:#dc3545"></div></div>
        </div>
        <!-- ===== آخر نشاط للموظف ===== -->
<div style="background:rgba(255,255,255,0.08); border-radius:12px; padding:12px; margin:12px 0;">
    <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
        <i class="fa fa-clock" style="color:#d4af37;"></i>
        <span style="font-size:11px; color:rgba(255,255,255,0.7);">📅 DERNIÈRE ACTIVITÉ</span>
    </div>
    
    <?php if($r['derniere_reservation_date']): ?>
        <!-- التاريخ -->
        <div style="font-size:13px; color:#28a745; font-weight:bold;">
            <i class="fa fa-calendar-check"></i> <?= date('d/m/Y', strtotime($r['derniere_reservation_date'])) ?>
        </div>
        
        <!-- معلومات العميل والغرفة -->
        <div style="font-size:11px; color:rgba(255,255,255,0.8); margin-top:8px;">
            <div><i class="fa fa-user"></i> Client: <strong><?= htmlspecialchars($r['dernier_client'] ?? '-') ?></strong></div>
            <div style="margin-top:4px;"><i class="fa fa-bed"></i> Chambre: <strong>N°<?= htmlspecialchars($r['derniere_chambre'] ?? '-') ?></strong></div>
        </div>
        
        <!-- حالة الحجز -->
        <div style="margin-top:6px;">
            <?php 
            $statut_icon = '';
            $statut_text = '';
            $statut_color = '';
            
            switch($r['dernier_statut']) {
                case 'checkout':
                    $statut_icon = '✅';
                    $statut_text = 'Terminée';
                    $statut_color = '#28a745';
                    break;
                case 'checkin':
                    $statut_icon = '🟢';
                    $statut_text = 'En cours';
                    $statut_color = '#17a2b8';
                    break;
                case 'confirmee':
                    $statut_icon = '📅';
                    $statut_text = 'Confirmée';
                    $statut_color = '#d4af37';
                    break;
                case 'annulee':
                    $statut_icon = '❌';
                    $statut_text = 'Annulée';
                    $statut_color = '#dc3545';
                    break;
                default:
                    $statut_icon = '❓';
                    $statut_text = 'Inconnu';
                    $statut_color = '#888';
            }
            ?>
            <span style="font-size:11px; color:<?= $statut_color ?>;">
                <?= $statut_icon ?> Statut: <?= $statut_text ?>
            </span>
        </div>
    <?php else: ?>
        <!-- إذا لم يقم بأي حجز -->
        <div style="font-size:11px; color:#dc3545; text-align:center; padding:8px;">
            <i class="fa fa-warning"></i> Aucune réservation enregistrée
        </div>
    <?php endif; ?>
</div>
        <div class="taux-box"><span class="tl">Taux de succès</span><span class="tv" style="color:<?= $taux>=70?'#28a745':($taux>=50?'#d4af37':'#dc3545') ?>"><?= $taux ?>%</span></div>
    </div>
    <?php endforeach; ?>
    </div>

    <!-- CHART RÉCEPTIONNISTES -->
    <div class="box">
        <h3><i class="fa fa-chart-bar"></i> CA vs Pertes par Réceptionniste</h3>
        <canvas id="c3" height="60"></canvas>
    </div>

    <!-- TABLEAU RÉCAP -->
    <div class="box">
        <h3><i class="fa fa-table"></i> Tableau Récapitulatif Final</h3>
        <table class="tbl">
            <thead><tr><th>Réceptionniste</th><th>Réservations</th><th>CA (MAD)</th><th>Encaissé (MAD)</th><th>Pertes (MAD)</th><th>Taux</th><th>Statut</th></tr></thead>
            <tbody>
            <?php foreach($rd as $i=>$r): $t=$r['nb']>0?round((($r['nb']-$r['nb_ann'])/$r['nb'])*100):0; ?>
            <tr><td><strong><?= htmlspecialchars($r['nom']) ?></strong></td><td><span class="badge by"><?= $r['nb'] ?></span></td>
            <td style="color:#d4af37;font-weight:bold">+<?= number_format($r['ca'],0,',',' ') ?></td>
            <td style="color:#28a745;font-weight:bold"><?= number_format($r['encaisse'],0,',',' ') ?></td>
            <td style="color:#dc3545;font-weight:bold">-<?= number_format($r['perte'],0,',',' ') ?></td>
            <td><div style="display:flex;align-items:center;gap:6px"><div style="background:rgba(255,255,255,0.1);border-radius:10px;height:5px;width:70px"><div style="width:<?= $t ?>%;height:100%;border-radius:10px;background:<?= $t>=70?'#28a745':($t>=50?'#d4af37':'#dc3545') ?>"></div></div><span style="font-size:11px;color:<?= $t>=70?'#28a745':($t>=50?'#d4af37':'#dc3545') ?>"><?= $t ?>%</span></div></td>
            <td><span class="badge <?= $t>=70?'bg':($t>=50?'by':'br') ?>"><?= $t>=70?'✅ Excellent':($t>=50?'⚠️ Moyen':'❌ Faible') ?></span></td></tr>
            <?php endforeach; ?>
            <tr style="background:rgba(212,175,55,0.1)"><td><strong style="color:#d4af37">TOTAL</strong></td><td><span class="badge by"><?= $total_res ?></span></td>
            <td style="color:#d4af37;font-weight:bold">+<?= number_format(array_sum(array_column($rd,'ca')),0,',',' ') ?></td>
            <td style="color:#28a745;font-weight:bold"><?= number_format($total_revenus,0,',',' ') ?></td>
            <td style="color:#dc3545;font-weight:bold">-<?= number_format($pertes,0,',',' ') ?></td><td colspan="2" style="color:rgba(255,255,255,0.4)">Bilan global hôtel</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
new Chart(document.getElementById('c1'),{type:'line',data:{labels:<?= json_encode($m_l) ?>,datasets:[{label:'Revenus (MAD)',data:<?= json_encode($m_r) ?>,borderColor:'#28a745',backgroundColor:'rgba(40,167,69,0.1)',borderWidth:2,fill:true,tension:0.4,pointBackgroundColor:'#28a745',pointRadius:5}]},options:{responsive:true,plugins:{legend:{labels:{color:'rgba(255,255,255,0.6)'}}},scales:{x:{ticks:{color:'rgba(255,255,255,0.5)'},grid:{color:'rgba(255,255,255,0.05)'}},y:{ticks:{color:'rgba(255,255,255,0.5)'},grid:{color:'rgba(255,255,255,0.05)'}}}}});

new Chart(document.getElementById('c2'),{type:'doughnut',data:{labels:['💰 Revenus','⏳ En Attente','📉 Pertes'],datasets:[{data:[<?= $total_revenus ?>,<?= $revenus_attente ?>,<?= $pertes ?>],backgroundColor:['rgba(40,167,69,0.8)','rgba(212,175,55,0.8)','rgba(220,53,69,0.8)'],borderColor:['#28a745','#d4af37','#dc3545'],borderWidth:2}]},options:{responsive:true,plugins:{legend:{labels:{color:'rgba(255,255,255,0.6)'}}}}});

new Chart(document.getElementById('c3'),{type:'bar',data:{labels:<?= json_encode($rn) ?>,datasets:[{label:'💰 CA Généré',data:<?= json_encode($rca) ?>,backgroundColor:'rgba(40,167,69,0.7)',borderColor:'#28a745',borderWidth:2,borderRadius:6},{label:'📉 Pertes',data:<?= json_encode($rp) ?>,backgroundColor:'rgba(220,53,69,0.7)',borderColor:'#dc3545',borderWidth:2,borderRadius:6}]},options:{responsive:true,plugins:{legend:{labels:{color:'rgba(255,255,255,0.6)'}}},scales:{x:{ticks:{color:'rgba(255,255,255,0.5)'},grid:{color:'rgba(255,255,255,0.05)'}},y:{ticks:{color:'rgba(255,255,255,0.5)'},grid:{color:'rgba(255,255,255,0.05)'}}}}});
</script>
</body>
</html>