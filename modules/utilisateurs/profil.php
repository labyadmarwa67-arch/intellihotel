<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
require_once '../../config/database.php';

$id   = (int)$_GET['id'];
$user = $conn->query("SELECT * FROM utilisateurs WHERE id=$id")->fetch_assoc();
if (!$user) { header("Location: index.php"); exit; }

// ===== STATS =====
$stats = $conn->query("
    SELECT
        COUNT(*) as nb_total,
        SUM(CASE WHEN statut='checkout'  THEN 1 ELSE 0 END) as nb_done,
        SUM(CASE WHEN statut='checkin'   THEN 1 ELSE 0 END) as nb_encours,
        SUM(CASE WHEN statut='confirmee' THEN 1 ELSE 0 END) as nb_confirmee,
        SUM(CASE WHEN statut='annulee'   THEN 1 ELSE 0 END) as nb_annulee,
        SUM(CASE WHEN statut!='annulee'  THEN prix_total ELSE 0 END) as ca_total,
        SUM(CASE WHEN statut='annulee'   THEN prix_total ELSE 0 END) as pertes,
        SUM(CASE WHEN statut='checkout'  THEN prix_total ELSE 0 END) as encaisse,
        SUM(CASE WHEN statut='checkin'   THEN prix_total ELSE 0 END) as en_cours,
        SUM(CASE WHEN statut='confirmee' THEN prix_total ELSE 0 END) as a_venir
    FROM reservations WHERE receptionniste_id=$id
")->fetch_assoc();

// ===== RESERVATIONS PAR MOIS =====
$mois_q = $conn->query("
    SELECT DATE_FORMAT(date_debut,'%b %Y') as mois,
           COUNT(*) as nb,
           SUM(prix_total) as rev
    FROM reservations
    WHERE receptionniste_id=$id
    GROUP BY YEAR(date_debut),MONTH(date_debut)
    ORDER BY YEAR(date_debut),MONTH(date_debut)
");
$ml=$mn=$mr=[];
while($r=$mois_q->fetch_assoc()){$ml[]=$r['mois'];$mn[]=$r['nb'];$mr[]=$r['rev'];}

// ===== DERNIÈRES RÉSERVATIONS =====
$last_res = $conn->query("
    SELECT r.*, c.nom as client_nom, ch.numero as chambre_num, ch.type
    FROM reservations r
    JOIN clients c ON r.client_id=c.id
    JOIN chambres ch ON r.chambre_id=ch.id
    WHERE r.receptionniste_id=$id
    ORDER BY r.date_debut DESC
    LIMIT 8
");

$nb    = $stats['nb_total'] ?? 0;
$ann   = $stats['nb_annulee'] ?? 0;
$ca    = $stats['ca_total'] ?? 0;
$pt    = $stats['pertes'] ?? 0;
$enc   = $stats['encaisse'] ?? 0;
$taux  = $nb>0 ? round((($nb-$ann)/$nb)*100) : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Profil <?= $user['nom'] ?> — IntelliHotel</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:Arial;background:#07071a;color:white;display:flex;}
.sidebar{width:240px;min-height:100vh;position:fixed;background:linear-gradient(180deg,#0d0d1f,#1a1a2e);border-right:1px solid rgba(240,192,64,0.1);z-index:100;}
.logo{padding:20px;text-align:center;border-bottom:1px solid rgba(255,255,255,0.07);}
.logo .ico{font-size:36px;}
.logo h2{color:#f0c040;font-size:14px;letter-spacing:3px;margin-top:6px;}
.logo .stars{color:#f0c040;font-size:11px;margin-top:4px;}
.ns{padding:12px 18px 4px;color:rgba(255,255,255,0.25);font-size:10px;text-transform:uppercase;letter-spacing:2px;}
.sidebar a{display:flex;align-items:center;gap:10px;color:rgba(255,255,255,0.55);text-decoration:none;padding:11px 18px;font-size:13px;border-left:3px solid transparent;transition:0.2s;}
.sidebar a:hover,.sidebar a.active{background:rgba(240,192,64,0.07);color:#f0c040;border-left-color:#f0c040;}
.sidebar a i{width:16px;text-align:center;}
.sf{position:absolute;bottom:0;width:100%;padding:14px 18px;border-top:1px solid rgba(255,255,255,0.07);}
.sf a{color:rgba(255,255,255,0.35);font-size:12px;display:flex;align-items:center;gap:8px;}
.main{margin-left:240px;flex:1;padding:22px;}

/* TOPBAR */
.topbar{display:flex;justify-content:space-between;align-items:center;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:14px;padding:14px 22px;margin-bottom:22px;}
.topbar h1{font-size:17px;}
.btn-back{display:flex;align-items:center;gap:8px;color:#f0c040;text-decoration:none;font-size:13px;background:rgba(240,192,64,0.1);border:1px solid rgba(240,192,64,0.2);padding:9px 16px;border-radius:10px;transition:0.2s;}
.btn-back:hover{background:rgba(240,192,64,0.2);}

/* PROFILE HERO */
.hero{
    background:linear-gradient(135deg,rgba(240,192,64,0.1),rgba(240,192,64,0.02));
    border:1px solid rgba(240,192,64,0.2);
    border-radius:20px;padding:30px;
    display:flex;align-items:center;gap:25px;
    margin-bottom:22px;position:relative;overflow:hidden;
}
.hero::before{
    content:'';position:absolute;top:-50px;right:-50px;
    width:200px;height:200px;border-radius:50%;
    background:rgba(240,192,64,0.05);
}
.hero-av{
    width:90px;height:90px;border-radius:50%;flex-shrink:0;
    display:flex;align-items:center;justify-content:center;
    font-weight:bold;font-size:36px;
    border:3px solid rgba(240,192,64,0.4);
}
.hero-av.admin{background:linear-gradient(135deg,#dc3545,#a71d2a);}
.hero-av.receptionniste{background:linear-gradient(135deg,#17a2b8,#0c5460);}
.hero-av.technicien{background:linear-gradient(135deg,#28a745,#155724);}
.hero-name{color:white;font-size:24px;font-weight:bold;margin-bottom:5px;}
.hero-email{color:rgba(255,255,255,0.45);font-size:13px;margin-bottom:8px;}
.hero-badge{font-size:11px;padding:4px 14px;border-radius:20px;display:inline-block;font-weight:bold;}
.hero-badge.admin{background:rgba(220,53,69,0.2);color:#ff6b6b;border:1px solid rgba(220,53,69,0.3);}
.hero-badge.receptionniste{background:rgba(23,162,184,0.2);color:#17a2b8;border:1px solid rgba(23,162,184,0.3);}
.hero-badge.technicien{background:rgba(40,167,69,0.2);color:#28a745;border:1px solid rgba(40,167,69,0.3);}
.hero-actions{margin-left:auto;display:flex;flex-direction:column;gap:10px;}
.btn-edit-hero{
    background:linear-gradient(135deg,#f0c040,#e0a020);
    color:#1a1a2e;padding:10px 20px;border-radius:10px;
    text-decoration:none;font-size:13px;font-weight:bold;
    display:flex;align-items:center;gap:8px;transition:0.3s;
}
.btn-edit-hero:hover{transform:translateY(-2px);}

/* TAUX CERCLE */
.taux-circle{
    width:80px;height:80px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    flex-direction:column;
    background:conic-gradient(
        <?= $taux>=70?'#28a745':($taux>=50?'#f0c040':'#dc3545') ?> <?= $taux*3.6 ?>deg,
        rgba(255,255,255,0.1) 0deg
    );
    flex-shrink:0;
}
.taux-inner{
    width:64px;height:64px;border-radius:50%;
    background:#07071a;display:flex;align-items:center;
    justify-content:center;flex-direction:column;
}
.taux-val{font-size:15px;font-weight:bold;color:<?= $taux>=70?'#28a745':($taux>=50?'#f0c040':'#dc3545') ?>;}
.taux-lbl{font-size:9px;color:rgba(255,255,255,0.4);}

/* KPI */
.kpi4{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px;}
.kpi{border-radius:14px;padding:18px;transition:0.3s;}
.kpi:hover{transform:translateY(-3px);}
.kpi.g{background:rgba(40,167,69,0.1);border:1px solid rgba(40,167,69,0.2);}
.kpi.y{background:rgba(240,192,64,0.1);border:1px solid rgba(240,192,64,0.2);}
.kpi.r{background:rgba(220,53,69,0.1);border:1px solid rgba(220,53,69,0.2);}
.kpi.b{background:rgba(23,162,184,0.1);border:1px solid rgba(23,162,184,0.2);}
.kpi .ico{font-size:26px;margin-bottom:8px;}
.kpi .val{font-size:22px;font-weight:bold;}
.kpi.g .val{color:#28a745;}
.kpi.y .val{color:#f0c040;}
.kpi.r .val{color:#dc3545;}
.kpi.b .val{color:#17a2b8;}
.kpi .lbl{font-size:11px;color:rgba(255,255,255,0.45);margin-top:3px;}

/* BILAN */
.bilan2{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:22px;}
.bx{border-radius:14px;padding:20px;text-align:center;}
.bx.g{background:linear-gradient(135deg,rgba(40,167,69,0.15),rgba(40,167,69,0.03));border:1px solid rgba(40,167,69,0.3);}
.bx.r{background:linear-gradient(135deg,rgba(220,53,69,0.15),rgba(220,53,69,0.03));border:1px solid rgba(220,53,69,0.3);}
.bx .bi{font-size:30px;margin-bottom:8px;}
.bx .bl{font-size:11px;color:rgba(255,255,255,0.4);margin-bottom:6px;}
.bx .bv{font-size:26px;font-weight:bold;}
.bx.g .bv{color:#28a745;}
.bx.r .bv{color:#dc3545;}

/* BOX */
.box{background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:14px;padding:20px;}
.box h3{color:#f0c040;font-size:13px;margin-bottom:16px;display:flex;align-items:center;gap:8px;}
.r2{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:22px;}

/* PROGRESS */
.pgrow{margin-bottom:10px;}
.pgl{display:flex;justify-content:space-between;font-size:11px;color:rgba(255,255,255,0.45);margin-bottom:4px;}
.pgbar{height:6px;background:rgba(255,255,255,0.07);border-radius:6px;overflow:hidden;}
.pgf{height:100%;border-radius:6px;}

/* TABLE */
.tbl{width:100%;border-collapse:collapse;}
.tbl th{background:rgba(255,255,255,0.04);color:rgba(255,255,255,0.4);padding:10px 14px;text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:0.5px;border-bottom:1px solid rgba(255,255,255,0.06);}
.tbl td{padding:11px 14px;font-size:12px;color:rgba(255,255,255,0.7);border-bottom:1px solid rgba(255,255,255,0.04);}
.tbl tr:hover td{background:rgba(255,255,255,0.02);}
.badge{padding:3px 9px;border-radius:20px;font-size:10px;font-weight:bold;}
.bg{background:rgba(40,167,69,0.15);color:#28a745;}
.by{background:rgba(240,192,64,0.15);color:#f0c040;}
.br{background:rgba(220,53,69,0.15);color:#dc3545;}
.bb{background:rgba(23,162,184,0.15);color:#17a2b8;}
.sec{color:white;font-size:14px;font-weight:bold;margin-bottom:14px;display:flex;align-items:center;gap:8px;}
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="logo">
        <div class="ico">🏨</div>
        <h2>INTELLI HOTEL</h2>
        <div class="stars">★ ★ ★ ★ ★</div>
    </div>
    <div class="ns">Principal</div>
    <a href="../../index.php"><i class="fa fa-home"></i> Dashboard</a>
    <a href="../../admin/dashboard.php"><i class="fa fa-chart-line"></i> Admin Analytics</a>
    <div class="ns">Gestion</div>
    <a href="../chambres/index.php"><i class="fa fa-bed"></i> Chambres</a>
    <a href="../clients/index.php"><i class="fa fa-users"></i> Clients</a>
    <a href="../reservations/index.php"><i class="fa fa-calendar"></i> Réservations</a>
    <a href="../paiements/index.php"><i class="fa fa-credit-card"></i> Paiements</a>
    <a href="../maintenance/index.php"><i class="fa fa-wrench"></i> Maintenance</a>
    <div class="ns">Équipe</div>
    <a href="index.php" class="active"><i class="fa fa-user-tie"></i> Utilisateurs</a>
    <div class="sf">
        <a href="../auth/logout.php"><i class="fa fa-sign-out-alt"></i> Déconnexion</a>
    </div>
</div>

<!-- MAIN -->
<div class="main">

    <div class="topbar">
        <h1>👤 Profil — <?= $user['nom'] ?></h1>
        <a href="index.php" class="btn-back"><i class="fa fa-arrow-left"></i> Retour</a>
    </div>

    <!-- HERO -->
    <div class="hero">
        <div class="hero-av <?= $user['role'] ?>"><?= strtoupper(substr($user['nom'],0,1)) ?></div>
        <div>
            <div class="hero-name"><?= $user['nom'] ?></div>
            <div class="hero-email">✉️ <?= $user['email'] ?></div>
            <?php $icons=['admin'=>'🛡️ Administrateur','receptionniste'=>'🎫 Réceptionniste','technicien'=>'🔧 Technicien']; ?>
            <div class="hero-badge <?= $user['role'] ?>"><?= $icons[$user['role']] ?></div>
        </div>

        <?php if($user['role']==='receptionniste'): ?>
        <!-- Taux Cercle -->
        <div style="margin-left:20px">
            <div class="taux-circle">
                <div class="taux-inner">
                    <div class="taux-val"><?= $taux ?>%</div>
                    <div class="taux-lbl">Succès</div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="hero-actions">
            <a href="modifier.php?id=<?= $user['id'] ?>" class="btn-edit-hero">
                <i class="fa fa-edit"></i> Modifier
            </a>
        </div>
    </div>

    <?php if($user['role']==='receptionniste'): ?>

    <!-- KPIs -->
    <div class="kpi4">
        <div class="kpi y">
            <div class="ico">📅</div>
            <div class="val"><?= $nb ?></div>
            <div class="lbl">Total Réservations</div>
        </div>
        <div class="kpi b">
            <div class="ico">✅</div>
            <div class="val"><?= $stats['nb_done'] ?></div>
            <div class="lbl">Terminées</div>
        </div>
        <div class="kpi g">
            <div class="ico">💰</div>
            <div class="val"><?= number_format($enc,0) ?></div>
            <div class="lbl">Encaissé (MAD)</div>
        </div>
        <div class="kpi r">
            <div class="ico">❌</div>
            <div class="val"><?= $ann ?></div>
            <div class="lbl">Annulées</div>
        </div>
    </div>

    <!-- BILAN -->
    <div class="sec">💰 Bilan Financier Personnel</div>
    <div class="bilan2">
        <div class="bx g">
            <div class="bi">💰</div>
            <div class="bl">CA Total Généré</div>
            <div class="bv">+<?= number_format($ca,0,',',' ') ?> MAD</div>
        </div>
        <div class="bx r">
            <div class="bi">📉</div>
            <div class="bl">Pertes (Annulations)</div>
            <div class="bv">-<?= number_format($pt,0,',',' ') ?> MAD</div>
        </div>
    </div>

    <!-- CHARTS + PROGRESS -->
    <div class="r2">
        <div class="box">
            <h3><i class="fa fa-chart-bar"></i> Réservations par Mois</h3>
            <canvas id="c1" height="140"></canvas>
        </div>
        <div class="box">
            <h3><i class="fa fa-tasks"></i> Détail des Performances</h3>

            <div class="pgrow">
                <div class="pgl"><span>✅ Encaissé</span><span style="color:#28a745"><?= number_format($enc,0) ?> MAD</span></div>
                <div class="pgbar"><div class="pgf" style="width:<?= $ca>0?round(($enc/$ca)*100):0 ?>%;background:#28a745"></div></div>
            </div>
            <div class="pgrow">
                <div class="pgl"><span>🔄 En Cours</span><span style="color:#17a2b8"><?= number_format($stats['en_cours'],0) ?> MAD</span></div>
                <div class="pgbar"><div class="pgf" style="width:<?= $ca>0?round(($stats['en_cours']/$ca)*100):0 ?>%;background:#17a2b8"></div></div>
            </div>
            <div class="pgrow">
                <div class="pgl"><span>📅 À Venir</span><span style="color:#f0c040"><?= number_format($stats['a_venir'],0) ?> MAD</span></div>
                <div class="pgbar"><div class="pgf" style="width:<?= $ca>0?round(($stats['a_venir']/$ca)*100):0 ?>%;background:#f0c040"></div></div>
            </div>
            <div class="pgrow">
                <div class="pgl"><span>📉 Pertes</span><span style="color:#dc3545"><?= number_format($pt,0) ?> MAD</span></div>
                <div class="pgbar"><div class="pgf" style="width:<?= $ca>0?round(($pt/$ca)*100):0 ?>%;background:#dc3545"></div></div>
            </div>

            <div style="margin-top:20px;background:rgba(255,255,255,0.04);border-radius:10px;padding:15px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <span style="font-size:12px;color:rgba(255,255,255,0.4)">Taux de Succès Global</span>
                    <span style="font-size:18px;font-weight:bold;color:<?= $taux>=70?'#28a745':($taux>=50?'#f0c040':'#dc3545') ?>"><?= $taux ?>%</span>
                </div>
                <div class="pgbar" style="height:8px">
                    <div class="pgf" style="width:<?= $taux ?>%;background:<?= $taux>=70?'#28a745':($taux>=50?'#f0c040':'#dc3545') ?>"></div>
                </div>
                <div style="margin-top:8px;text-align:center;font-size:12px;color:<?= $taux>=70?'#28a745':($taux>=50?'#f0c040':'#dc3545') ?>">
                    <?= $taux>=70?'✅ Excellent':($taux>=50?'⚠️ Moyen':'❌ Faible') ?>
                </div>
            </div>
        </div>
    </div>

    <!-- DERNIÈRES RÉSERVATIONS -->
    <div class="box" style="margin-bottom:22px">
        <h3><i class="fa fa-list"></i> Dernières Réservations</h3>
        <table class="tbl">
            <thead>
                <tr><th>#</th><th>Client</th><th>Chambre</th><th>Dates</th><th>Prix</th><th>Statut</th></tr>
            </thead>
            <tbody>
            <?php while($r=$last_res->fetch_assoc()):
                $statuts=['checkout'=>['⬜','bg'],'checkin'=>['🟢','bb'],'confirmee'=>['✅','by'],'annulee'=>['❌','br'],'en_attente'=>['⏳','by']];
                $s=$statuts[$r['statut']]??['—','by'];
            ?>
            <tr>
                <td><strong style="color:#f0c040">#<?= $r['id'] ?></strong></td>
                <td><?= $r['client_nom'] ?></td>
                <td>🚪 N°<?= $r['chambre_num'] ?> <small style="color:rgba(255,255,255,0.4)"><?= $r['type'] ?></small></td>
                <td style="font-size:11px"><?= $r['date_debut'] ?> → <?= $r['date_fin'] ?></td>
                <td style="color:#f0c040;font-weight:bold"><?= number_format($r['prix_total'],0) ?> MAD</td>
                <td><span class="badge <?= $s[1] ?>"><?= $s[0] ?> <?= ucfirst($r['statut']) ?></span></td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php elseif($user['role']==='technicien'): ?>

    <?php
    $maintenances = $conn->query("
        SELECT m.*, ch.numero as chambre_num
        FROM maintenance m
        JOIN chambres ch ON m.chambre_id=ch.id
        WHERE m.technicien_id=$id
        ORDER BY m.date_debut DESC
    ");
    $nb_m = $conn->query("SELECT COUNT(*) as n FROM maintenance WHERE technicien_id=$id")->fetch_assoc()['n'];
    $nb_done_m = $conn->query("SELECT COUNT(*) as n FROM maintenance WHERE technicien_id=$id AND statut='terminee'")->fetch_assoc()['n'];
    ?>

    <div class="kpi4">
        <div class="kpi g"><div class="ico">🔧</div><div class="val"><?= $nb_m ?></div><div class="lbl">Total Interventions</div></div>
        <div class="kpi b"><div class="ico">✅</div><div class="val"><?= $nb_done_m ?></div><div class="lbl">Terminées</div></div>
        <div class="kpi y"><div class="ico">🔄</div><div class="val"><?= $nb_m-$nb_done_m ?></div><div class="lbl">En Cours</div></div>
        <div class="kpi r"><div class="ico">📊</div><div class="val"><?= $nb_m>0?round(($nb_done_m/$nb_m)*100):0 ?>%</div><div class="lbl">Taux Completion</div></div>
    </div>

    <div class="box" style="margin-bottom:22px">
        <h3><i class="fa fa-wrench"></i> Historique Maintenance</h3>
        <table class="tbl">
            <thead><tr><th>#</th><th>Chambre</th><th>Description</th><th>Début</th><th>Fin</th><th>Statut</th></tr></thead>
            <tbody>
            <?php while($m=$maintenances->fetch_assoc()): ?>
            <tr>
                <td><strong style="color:#f0c040">#<?= $m['id'] ?></strong></td>
                <td>🚪 N°<?= $m['chambre_num'] ?></td>
                <td style="max-width:200px"><?= substr($m['description'],0,50) ?>...</td>
                <td style="font-size:11px"><?= $m['date_debut'] ?></td>
                <td style="font-size:11px"><?= $m['date_fin'] ?? '—' ?></td>
                <td>
                    <?php
                    $ms=['terminee'=>['bg','✅ Terminée'],'en_cours'=>['bb','🔄 En Cours'],'prolongee'=>['br','⚠️ Prolongée']];
                    $ms2=$ms[$m['statut']]??['by',$m['statut']];
                    ?>
                    <span class="badge <?= $ms2[0] ?>"><?= $ms2[1] ?></span>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php else: ?>

    <div style="background:rgba(220,53,69,0.08);border:1px solid rgba(220,53,69,0.15);border-radius:14px;padding:30px;text-align:center;margin-bottom:22px;">
        <div style="font-size:50px;margin-bottom:15px;">🛡️</div>
        <div style="color:#ff6b6b;font-size:18px;font-weight:bold;margin-bottom:8px;">Super Administrateur</div>
        <div style="color:rgba(255,255,255,0.4);font-size:13px;">Accès complet à tous les modules du système IntelliHotel</div>
    </div>

    <?php endif; ?>

</div>

<script>
<?php if($user['role']==='receptionniste' && count($ml)>0): ?>
new Chart(document.getElementById('c1'),{
    type:'bar',
    data:{
        labels:<?= json_encode($ml) ?>,
        datasets:[
            {label:'Réservations',data:<?= json_encode($mn) ?>,backgroundColor:'rgba(240,192,64,0.6)',borderColor:'#f0c040',borderWidth:2,borderRadius:6,yAxisID:'y'},
            {label:'Revenus MAD',data:<?= json_encode($mr) ?>,type:'line',borderColor:'#28a745',backgroundColor:'rgba(40,167,69,0.1)',borderWidth:2,fill:true,tension:0.4,yAxisID:'y1',pointBackgroundColor:'#28a745',pointRadius:4}
        ]
    },
    options:{
        responsive:true,
        plugins:{legend:{labels:{color:'rgba(255,255,255,0.6)'}}},
        scales:{
            x:{ticks:{color:'rgba(255,255,255,0.45)'},grid:{color:'rgba(255,255,255,0.04)'}},
            y:{ticks:{color:'rgba(255,255,255,0.45)'},grid:{color:'rgba(255,255,255,0.04)'}},
            y1:{position:'right',ticks:{color:'rgba(40,167,69,0.7)'},grid:{display:false}}
        }
    }
});
<?php endif; ?>
</script>
</body>
</html>