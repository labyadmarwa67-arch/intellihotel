<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
require_once '../../config/database.php';

// حذف
if (isset($_GET['supprimer'])) {
    $id = (int)$_GET['supprimer'];
    if ($id != $_SESSION['user_id']) {
        $conn->query("DELETE FROM utilisateurs WHERE id=$id");
    }
    header("Location: index.php");
    exit;
}

$users = $conn->query("SELECT * FROM utilisateurs ORDER BY role, nom");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Utilisateurs — IntelliHotel</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:Arial;background:#07071a;color:white;display:flex;}

/* SIDEBAR */
.sidebar{
    width:240px;min-height:100vh;position:fixed;
    background:linear-gradient(180deg,#0d0d1f,#1a1a2e);
    border-right:1px solid rgba(240,192,64,0.1);z-index:100;
}
.logo{padding:20px;text-align:center;border-bottom:1px solid rgba(255,255,255,0.07);}
.logo .ico{font-size:36px;}
.logo h2{color:#f0c040;font-size:14px;letter-spacing:3px;margin-top:6px;}
.logo .stars{color:#f0c040;font-size:11px;margin-top:4px;}
.ns{padding:12px 18px 4px;color:rgba(255,255,255,0.25);font-size:10px;text-transform:uppercase;letter-spacing:2px;}
.sidebar a{
    display:flex;align-items:center;gap:10px;
    color:rgba(255,255,255,0.55);text-decoration:none;
    padding:11px 18px;font-size:13px;
    border-left:3px solid transparent;transition:0.2s;
}
.sidebar a:hover,.sidebar a.active{
    background:rgba(240,192,64,0.07);
    color:#f0c040;border-left-color:#f0c040;
}
.sidebar a i{width:16px;text-align:center;}
.sf{position:absolute;bottom:0;width:100%;padding:14px 18px;border-top:1px solid rgba(255,255,255,0.07);}
.sf a{color:rgba(255,255,255,0.35);font-size:12px;display:flex;align-items:center;gap:8px;}

/* MAIN */
.main{margin-left:240px;flex:1;padding:22px;}

/* TOPBAR */
.topbar{
    display:flex;justify-content:space-between;align-items:center;
    background:rgba(255,255,255,0.03);
    border:1px solid rgba(255,255,255,0.07);
    border-radius:14px;padding:14px 22px;margin-bottom:22px;
}
.topbar h1{font-size:17px;display:flex;align-items:center;gap:10px;}
.topbar .dt{color:rgba(255,255,255,0.35);font-size:11px;margin-top:4px;}
.btn-add{
    background:linear-gradient(135deg,#f0c040,#e0a020);
    color:#1a1a2e;padding:10px 20px;border-radius:10px;
    text-decoration:none;font-size:13px;font-weight:bold;
    display:flex;align-items:center;gap:8px;transition:0.3s;
}
.btn-add:hover{transform:translateY(-2px);box-shadow:0 5px 15px rgba(240,192,64,0.3);}

/* STATS */
.stats4{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px;}
.scard{
    border-radius:14px;padding:18px;text-align:center;transition:0.3s;
}
.scard:hover{transform:translateY(-3px);}
.scard.all{background:rgba(240,192,64,0.1);border:1px solid rgba(240,192,64,0.2);}
.scard.adm{background:rgba(220,53,69,0.1);border:1px solid rgba(220,53,69,0.2);}
.scard.rec{background:rgba(23,162,184,0.1);border:1px solid rgba(23,162,184,0.2);}
.scard.tec{background:rgba(40,167,69,0.1);border:1px solid rgba(40,167,69,0.2);}
.scard .ico{font-size:28px;margin-bottom:8px;}
.scard .val{font-size:26px;font-weight:bold;}
.scard.all .val{color:#f0c040;}
.scard.adm .val{color:#dc3545;}
.scard.rec .val{color:#17a2b8;}
.scard.tec .val{color:#28a745;}
.scard .lbl{font-size:11px;color:rgba(255,255,255,0.45);margin-top:3px;}

/* USER CARDS */
.ugrid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;}
.ucard{
    background:rgba(255,255,255,0.03);
    border:1px solid rgba(255,255,255,0.07);
    border-radius:16px;padding:22px;transition:0.3s;
    position:relative;overflow:hidden;
}
.ucard::before{
    content:'';position:absolute;top:0;left:0;right:0;height:3px;
}
.ucard.admin::before{background:linear-gradient(90deg,#dc3545,#e83e8c);}
.ucard.receptionniste::before{background:linear-gradient(90deg,#17a2b8,#6610f2);}
.ucard.technicien::before{background:linear-gradient(90deg,#28a745,#20c997);}
.ucard:hover{transform:translateY(-4px);border-color:rgba(240,192,64,0.2);}

.uhead{display:flex;align-items:center;gap:14px;margin-bottom:18px;}
.uav{
    width:52px;height:52px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    font-weight:bold;font-size:22px;flex-shrink:0;
}
.uav.admin{background:linear-gradient(135deg,#dc3545,#a71d2a);}
.uav.receptionniste{background:linear-gradient(135deg,#17a2b8,#0c5460);}
.uav.technicien{background:linear-gradient(135deg,#28a745,#155724);}
.uname{color:white;font-size:15px;font-weight:bold;}
.uemail{color:rgba(255,255,255,0.35);font-size:11px;margin-top:3px;}
.ubadge{
    font-size:10px;padding:3px 10px;border-radius:20px;
    display:inline-block;margin-top:5px;font-weight:bold;
}
.ubadge.admin{background:rgba(220,53,69,0.2);color:#ff6b6b;border:1px solid rgba(220,53,69,0.3);}
.ubadge.receptionniste{background:rgba(23,162,184,0.2);color:#17a2b8;border:1px solid rgba(23,162,184,0.3);}
.ubadge.technicien{background:rgba(40,167,69,0.2);color:#28a745;border:1px solid rgba(40,167,69,0.3);}

/* STATS RECEP */
.ustats{display:grid;grid-template-columns:repeat(2,1fr);gap:8px;margin-bottom:14px;}
.ust{background:rgba(255,255,255,0.04);border-radius:10px;padding:10px;text-align:center;}
.usv{font-size:18px;font-weight:bold;color:#f0c040;}
.usl{font-size:10px;color:rgba(255,255,255,0.4);margin-top:2px;}

/* PROFIT MINI */
.uprofit{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px;}
.upb{border-radius:10px;padding:10px;text-align:center;}
.upb.g{background:rgba(40,167,69,0.12);border:1px solid rgba(40,167,69,0.2);}
.upb.r{background:rgba(220,53,69,0.12);border:1px solid rgba(220,53,69,0.2);}
.upb .upv{font-size:14px;font-weight:bold;}
.upb.g .upv{color:#28a745;}
.upb.r .upv{color:#dc3545;}
.upb .upl{font-size:10px;color:rgba(255,255,255,0.4);margin-top:2px;}

/* TAUX */
.utaux{
    display:flex;justify-content:space-between;align-items:center;
    background:rgba(255,255,255,0.04);border-radius:8px;
    padding:9px 12px;margin-bottom:12px;
}
.utaux .tl{font-size:11px;color:rgba(255,255,255,0.4);}
.utaux .tv{font-size:15px;font-weight:bold;}

/* ACTIONS */
.uactions{display:flex;gap:8px;}
.btn-sm{
    flex:1;padding:9px;border-radius:8px;
    text-decoration:none;font-size:12px;font-weight:bold;
    text-align:center;transition:0.2s;display:flex;
    align-items:center;justify-content:center;gap:6px;
}
.btn-edit{background:rgba(23,162,184,0.15);color:#17a2b8;border:1px solid rgba(23,162,184,0.3);}
.btn-edit:hover{background:rgba(23,162,184,0.3);}
.btn-view{background:rgba(240,192,64,0.15);color:#f0c040;border:1px solid rgba(240,192,64,0.3);}
.btn-view:hover{background:rgba(240,192,64,0.3);}
.btn-del{background:rgba(220,53,69,0.15);color:#dc3545;border:1px solid rgba(220,53,69,0.3);}
.btn-del:hover{background:rgba(220,53,69,0.3);}

/* SEARCH */
.search-box{
    display:flex;align-items:center;gap:10px;
    background:rgba(255,255,255,0.04);
    border:1px solid rgba(255,255,255,0.08);
    border-radius:10px;padding:10px 16px;margin-bottom:20px;
    width:350px;
}
.search-box i{color:rgba(255,255,255,0.35);}
.search-box input{
    background:none;border:none;color:white;
    font-size:13px;outline:none;width:100%;
}
.search-box input::placeholder{color:rgba(255,255,255,0.3);}

.no-recep{
    text-align:center;padding:30px;
    color:rgba(255,255,255,0.3);font-size:13px;
}
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

    <!-- TOPBAR -->
    <div class="topbar">
        <div>
            <h1>👨‍💼 Gestion des Utilisateurs</h1>
            <div class="dt">📅 <?= date('d/m/Y H:i') ?> — Admin uniquement</div>
        </div>
        <a href="ajouter.php" class="btn-add">
            <i class="fa fa-plus"></i> Ajouter Utilisateur
        </a>
    </div>

    <?php
    // Stats
    $nb_all  = $conn->query("SELECT COUNT(*) as n FROM utilisateurs")->fetch_assoc()['n'];
    $nb_adm  = $conn->query("SELECT COUNT(*) as n FROM utilisateurs WHERE role='admin'")->fetch_assoc()['n'];
    $nb_rec  = $conn->query("SELECT COUNT(*) as n FROM utilisateurs WHERE role='receptionniste'")->fetch_assoc()['n'];
    $nb_tec  = $conn->query("SELECT COUNT(*) as n FROM utilisateurs WHERE role='technicien'")->fetch_assoc()['n'];
    ?>

    <!-- STATS -->
    <div class="stats4">
        <div class="scard all"><div class="ico">👥</div><div class="val"><?= $nb_all ?></div><div class="lbl">Total Utilisateurs</div></div>
        <div class="scard adm"><div class="ico">🛡️</div><div class="val"><?= $nb_adm ?></div><div class="lbl">Administrateurs</div></div>
        <div class="scard rec"><div class="ico">🎫</div><div class="val"><?= $nb_rec ?></div><div class="lbl">Réceptionnistes</div></div>
        <div class="scard tec"><div class="ico">🔧</div><div class="val"><?= $nb_tec ?></div><div class="lbl">Techniciens</div></div>
    </div>

    <!-- SEARCH -->
    <div class="search-box">
        <i class="fa fa-search"></i>
        <input type="text" id="search" placeholder="Rechercher un utilisateur...">
    </div>

    <!-- USER CARDS -->
    <div class="ugrid" id="usersGrid">
    <?php
    $users->data_seek(0);
    while($u = $users->fetch_assoc()):
        // Stats réservations
        $stats = $conn->query("
            SELECT
                COUNT(*) as nb,
                SUM(CASE WHEN statut='checkout'  THEN 1 ELSE 0 END) as done,
                SUM(CASE WHEN statut='annulee'   THEN 1 ELSE 0 END) as ann,
                SUM(CASE WHEN statut!='annulee'  THEN prix_total ELSE 0 END) as ca,
                SUM(CASE WHEN statut='annulee'   THEN prix_total ELSE 0 END) as perte,
                SUM(CASE WHEN statut='checkout'  THEN prix_total ELSE 0 END) as enc
            FROM reservations WHERE receptionniste_id={$u['id']}
        ")->fetch_assoc();
        $nb  = $stats['nb']   ?? 0;
        $ann = $stats['ann']  ?? 0;
        $ca  = $stats['ca']   ?? 0;
        $pt  = $stats['perte']?? 0;
        $enc = $stats['enc']  ?? 0;
        $taux= $nb>0 ? round((($nb-$ann)/$nb)*100) : 0;
    ?>
    <div class="ucard <?= $u['role'] ?>" data-name="<?= strtolower($u['nom']) ?>">
        <div class="uhead">
            <div class="uav <?= $u['role'] ?>"><?= strtoupper(substr($u['nom'],0,1)) ?></div>
            <div>
                <div class="uname"><?= $u['nom'] ?></div>
                <div class="uemail">✉️ <?= $u['email'] ?></div>
                <div class="ubadge <?= $u['role'] ?>">
                    <?php
                    $icons=['admin'=>'🛡️ Admin','receptionniste'=>'🎫 Réceptionniste','technicien'=>'🔧 Technicien'];
                    echo $icons[$u['role']] ?? $u['role'];
                    ?>
                </div>
            </div>
        </div>

        <?php if($u['role'] === 'receptionniste'): ?>
        <!-- Stats Réceptionniste -->
        <div class="ustats">
            <div class="ust"><div class="usv"><?= $nb ?></div><div class="usl">Total Résa</div></div>
            <div class="ust"><div class="usv" style="color:#17a2b8"><?= $stats['done'] ?></div><div class="usl">Terminées</div></div>
            <div class="ust"><div class="usv" style="color:#dc3545"><?= $ann ?></div><div class="usl">Annulées</div></div>
            <div class="ust"><div class="usv" style="color:#28a745"><?= number_format($enc,0) ?></div><div class="usl">Encaissé MAD</div></div>
        </div>

        <!-- Profits & Pertes -->
        <div class="uprofit">
            <div class="upb g"><div class="upv">+<?= number_format($ca,0) ?></div><div class="upl">💰 CA (MAD)</div></div>
            <div class="upb r"><div class="upv">-<?= number_format($pt,0) ?></div><div class="upl">📉 Pertes</div></div>
        </div>

        <!-- Taux -->
        <div class="utaux">
            <span class="tl">Taux de succès</span>
            <span class="tv" style="color:<?= $taux>=70?'#28a745':($taux>=50?'#f0c040':'#dc3545') ?>">
                <?= $taux ?>%
            </span>
        </div>
        <?php elseif($u['role'] === 'admin'): ?>
        <div style="background:rgba(220,53,69,0.08);border:1px solid rgba(220,53,69,0.15);border-radius:10px;padding:14px;margin-bottom:14px;text-align:center;">
            <div style="font-size:28px;margin-bottom:6px;">🛡️</div>
            <div style="color:#ff6b6b;font-size:13px;font-weight:bold;">Super Administrateur</div>
            <div style="color:rgba(255,255,255,0.35);font-size:11px;margin-top:4px;">Accès complet au système</div>
        </div>
        <?php elseif($u['role'] === 'technicien'): ?>
        <?php
        $nb_maint = $conn->query("SELECT COUNT(*) as n FROM maintenance WHERE technicien_id={$u['id']}")->fetch_assoc()['n'];
        $nb_done  = $conn->query("SELECT COUNT(*) as n FROM maintenance WHERE technicien_id={$u['id']} AND statut='terminee'")->fetch_assoc()['n'];
        ?>
        <div class="ustats">
            <div class="ust"><div class="usv" style="color:#28a745"><?= $nb_maint ?></div><div class="usl">Interventions</div></div>
            <div class="ust"><div class="usv" style="color:#17a2b8"><?= $nb_done ?></div><div class="usl">Terminées</div></div>
        </div>
        <div style="background:rgba(40,167,69,0.08);border:1px solid rgba(40,167,69,0.15);border-radius:10px;padding:12px;margin-bottom:14px;text-align:center;">
            <div style="color:#28a745;font-size:12px;">🔧 Technicien de Maintenance</div>
        </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="uactions">
            <a href="modifier.php?id=<?= $u['id'] ?>" class="btn-sm btn-edit">
                <i class="fa fa-edit"></i> Modifier
            </a>
            <a href="profil.php?id=<?= $u['id'] ?>" class="btn-sm btn-view">
                <i class="fa fa-eye"></i> Profil
            </a>
            <?php if($u['id'] != $_SESSION['user_id'] && $u['role'] != 'admin'): ?>
            <a href="?supprimer=<?= $u['id'] ?>"
               class="btn-sm btn-del"
               onclick="return confirm('Supprimer <?= $u['nom'] ?> ?')">
                <i class="fa fa-trash"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endwhile; ?>
    </div>

</div>

<script>
// Recherche en temps réel
document.getElementById('search').addEventListener('keyup', function(){
    const val = this.value.toLowerCase();
    document.querySelectorAll('.ucard').forEach(card => {
        card.style.display = card.dataset.name.includes(val) ? '' : 'none';
    });
});
</script>
</body>
</html>