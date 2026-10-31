<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: modules/auth/login.php");
    exit;
}
require_once 'config/database.php';

// ========== KPIs ==========
$total_chambres   = $conn->query("SELECT COUNT(*) as n FROM chambres")->fetch_assoc()['n'];
$total_clients    = $conn->query("SELECT COUNT(*) as n FROM clients")->fetch_assoc()['n'];
$total_reservations = $conn->query("SELECT COUNT(*) as n FROM reservations")->fetch_assoc()['n'];
$total_paiements  = $conn->query("SELECT COUNT(*) as n FROM paiements WHERE statut='paye'")->fetch_assoc()['n'];
$total_maintenance = $conn->query("SELECT COUNT(*) as n FROM maintenance WHERE statut='en_cours'")->fetch_assoc()['n'];

// ========== Dernières réservations ==========
$dernieres_reservations = $conn->query("
    SELECT r.id, c.nom as client_nom, ch.numero as chambre_num,
           r.date_debut, r.date_fin, r.statut, r.prix_total
    FROM reservations r
    JOIN clients c ON r.client_id = c.id
    JOIN chambres ch ON r.chambre_id = ch.id
    ORDER BY r.id DESC
    LIMIT 5
");

// ========== Alertes (résumé) ==========
$alertes = [];

$checkout = $conn->query("SELECT COUNT(*) as n FROM reservations WHERE date_fin = CURDATE() AND statut='checkin'")->fetch_assoc();
if($checkout['n'] > 0) $alertes[] = ['danger', '🚪', 'Checkout aujourd\'hui', "{$checkout['n']} client(s) partent"];

$retard = $conn->query("SELECT COUNT(*) as n FROM paiements p JOIN reservations r ON p.reservation_id = r.id WHERE p.statut='en_attente' AND r.statut='checkout'")->fetch_assoc();
if($retard['n'] > 0) $alertes[] = ['warning', '💰', 'Paiements en retard', "{$retard['n']} client(s) non réglé(s)"];

$maint = $conn->query("SELECT COUNT(*) as n FROM maintenance WHERE statut='en_cours' AND DATEDIFF(NOW(), date_debut) > 2")->fetch_assoc();
if($maint['n'] > 0) $alertes[] = ['warning', '🔧', 'Maintenance prolongée', "{$maint['n']} chambre(s) >48h"];

$vip = $conn->query("SELECT COUNT(DISTINCT c.id) as n FROM clients c JOIN reservations r ON c.id = r.client_id GROUP BY c.id HAVING COUNT(r.id) >= 3")->num_rows;
if($vip > 0) $alertes[] = ['success', '⭐', 'Clients VIP', "$vip client(s) fidèle(s)"];

$checkin = $conn->query("SELECT COUNT(*) as n FROM reservations WHERE date_debut = CURDATE() AND statut='confirmee'")->fetch_assoc();
if($checkin['n'] > 0) $alertes[] = ['info', '✅', 'Arrivées aujourd\'hui', "{$checkin['n']} client(s) attendus"];

// ========== DONNÉES POUR STATISTIQUES ==========
// Réservations par mois (année courante)
$annee = date('Y');
$res_mois = $conn->query("
    SELECT MONTH(date_debut) as mois, COUNT(*) as nb, SUM(prix_total) as revenus
    FROM reservations
    WHERE YEAR(date_debut) = $annee AND statut != 'annulee'
    GROUP BY MONTH(date_debut)
");
$mois_labels = ['Jan','Fév','Mar','Avr','Mai','Juin','Juil','Aoû','Sep','Oct','Nov','Déc'];
$mois_data = array_fill(0, 12, 0);
$revenus_data = array_fill(0, 12, 0);
while($r = $res_mois->fetch_assoc()) {
    $mois_data[$r['mois']-1] = $r['nb'];
    $revenus_data[$r['mois']-1] = $r['revenus'];
}

// Répartition par type de chambre
$type_stats = $conn->query("
    SELECT ch.type, COUNT(r.id) as nb
    FROM reservations r
    JOIN chambres ch ON r.chambre_id = ch.id
    GROUP BY ch.type
");
$type_labels = []; $type_data = [];
while($t = $type_stats->fetch_assoc()) {
    $type_labels[] = $t['type'];
    $type_data[] = $t['nb'];
}

// Répartition par vue
$vue_stats = $conn->query("
    SELECT ch.vue, COUNT(r.id) as nb
    FROM reservations r
    JOIN chambres ch ON r.chambre_id = ch.id
    GROUP BY ch.vue
");
$vue_labels = []; $vue_data = [];
while($v = $vue_stats->fetch_assoc()) {
    $vue_labels[] = $v['vue'];
    $vue_data[] = $v['nb'];
}

// Top 5 clients
$top_clients = $conn->query("
    SELECT c.nom, COUNT(r.id) as nb_sejours, SUM(r.prix_total) as total
    FROM clients c
    JOIN reservations r ON c.id = r.client_id
    GROUP BY c.id
    ORDER BY total DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>IntelliHotel — Dashboard Principal</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
            /* Image de fond large, luxueuse et différente (hôtel de prestige) */
            background: url('https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&dpr=2') no-repeat center center fixed;
            background-size: cover;
            position: relative;
        }
        /* Overlay sophistiqué pour la lisibilité */
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
        .navbar h1 { font-size: 24px; letter-spacing: 2px; color: white; }
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
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .user-info span {
            color: rgba(255,255,255,0.8);
            font-size: 13px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px;
        }
        .page-title {
            font-size: 32px;
            font-weight: bold;
            background: linear-gradient(135deg, #fff, #f0c040);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }
        .page-subtitle {
            color: rgba(255,255,255,0.7);
            font-size: 14px;
            margin-bottom: 30px;
        }
        /* KPIs Grid */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .kpi-card {
            background: rgba(20, 20, 35, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(240,192,64,0.3);
            border-radius: 24px;
            padding: 20px;
            text-align: center;
            transition: 0.3s;
            cursor: pointer;
        }
        .kpi-card:hover {
            transform: translateY(-5px);
            border-color: #f0c040;
            background: rgba(240,192,64,0.15);
        }
        .kpi-icon { font-size: 40px; margin-bottom: 12px; }
        .kpi-value { font-size: 32px; font-weight: bold; color: #f0c040; }
        .kpi-label { font-size: 13px; color: rgba(255,255,255,0.7); margin-top: 8px; }
        /* Sections */
        .section {
            background: rgba(15, 15, 25, 0.65);
            backdrop-filter: blur(10px);
            border-radius: 28px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid rgba(240,192,64,0.2);
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(240,192,64,0.3);
            padding-bottom: 12px;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #f0c040;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .section-link {
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            font-size: 13px;
            transition: 0.2s;
        }
        .section-link:hover { color: #f0c040; }
        /* Table */
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th {
            text-align: left;
            padding: 12px 8px;
            color: #f0c040;
            font-size: 13px;
            font-weight: normal;
            border-bottom: 1px solid rgba(255,255,255,0.15);
        }
        .table td {
            padding: 12px 8px;
            font-size: 13px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.85);
        }
        .table tr:hover td { background: rgba(240,192,64,0.1); }
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            display: inline-block;
        }
        .badge-confirmee { background: #00408580; color: #9cd0ff; }
        .badge-checkin { background: #15572480; color: #90ee90; }
        .badge-checkout { background: #383d4180; color: #ccc; }
        .badge-annulee { background: #721c2480; color: #ffaaaa; }
        .badge-en_attente { background: #85640480; color: #ffe69e; }
        /* Alertes */
        .alertes-mini {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 12px;
        }
        .alerte-item {
            background: rgba(0,0,0,0.4);
            border-radius: 16px;
            padding: 12px 15px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: 0.2s;
            border-left: 3px solid;
        }
        .alerte-item.danger { border-left-color: #dc3545; }
        .alerte-item.warning { border-left-color: #ffc107; }
        .alerte-item.success { border-left-color: #28a745; }
        .alerte-item.info { border-left-color: #17a2b8; }
        .alerte-icon { font-size: 24px; }
        .alerte-content { flex: 1; }
        .alerte-title { font-size: 13px; font-weight: bold; color: white; }
        .alerte-desc { font-size: 11px; color: rgba(255,255,255,0.7); }
        /* Actions */
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }
        .action-btn {
            background: rgba(30,30,45,0.7);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(240,192,64,0.2);
            border-radius: 20px;
            padding: 16px;
            text-align: center;
            text-decoration: none;
            color: white;
            transition: 0.2s;
        }
        .action-btn:hover {
            background: rgba(240,192,64,0.2);
            border-color: #f0c040;
            transform: translateY(-3px);
        }
        .action-icon { font-size: 28px; margin-bottom: 8px; }
        .action-label { font-size: 13px; font-weight: bold; }
        .action-desc { font-size: 11px; color: rgba(255,255,255,0.6); margin-top: 5px; }
        /* Charts Grid */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        .chart-card {
            background: rgba(10,10,20,0.6);
            backdrop-filter: blur(8px);
            border-radius: 24px;
            padding: 20px;
            border: 1px solid rgba(240,192,64,0.2);
        }
        .chart-card canvas {
            max-height: 250px;
            width: 100% !important;
        }
        .top5-table {
            width: 100%;
            border-collapse: collapse;
        }
        .top5-table th {
            text-align: left;
            padding: 8px 5px;
            color: #f0c040;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        .top5-table td {
            padding: 8px 5px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.85);
        }
        @media (max-width: 800px) {
            .container { padding: 15px; }
            .kpi-grid { grid-template-columns: repeat(2, 1fr); }
            .actions-grid { grid-template-columns: repeat(2, 1fr); }
            .charts-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="navbar">
    <h1>🏨 <span>IntelliHotel</span></h1>
    <div class="user-info">
        <span>👤 <?= htmlspecialchars($_SESSION['user_nom']) ?> (<?= htmlspecialchars($_SESSION['user_role']) ?>)</span>
        <a href="modules/auth/logout.php">🚪 Déconnexion</a>
    </div>
</div>

<div class="container">
    <div class="page-title">Bienvenue, <?= htmlspecialchars($_SESSION['user_nom']) ?>!</div>
    <div class="page-subtitle">Système Intelligent de Gestion Hôtelière — IntelliHotel</div>

    <!-- KPIs -->
    <div class="kpi-grid">
        <div class="kpi-card" onclick="window.location='modules/chambres/index.php'">
            <div class="kpi-icon">🚪</div>
            <div class="kpi-value"><?= $total_chambres ?></div>
            <div class="kpi-label">Chambres</div>
        </div>
        <div class="kpi-card" onclick="window.location='modules/clients/index.php'">
            <div class="kpi-icon">👥</div>
            <div class="kpi-value"><?= $total_clients ?></div>
            <div class="kpi-label">Clients</div>
        </div>
        <div class="kpi-card" onclick="window.location='modules/reservations/index.php'">
            <div class="kpi-icon">📅</div>
            <div class="kpi-value"><?= $total_reservations ?></div>
            <div class="kpi-label">Réservations</div>
        </div>
        <div class="kpi-card" onclick="window.location='modules/paiements/index.php'">
            <div class="kpi-icon">💰</div>
            <div class="kpi-value"><?= $total_paiements ?></div>
            <div class="kpi-label">Paiements effectués</div>
        </div>
        <div class="kpi-card" onclick="window.location='modules/maintenance/index.php'">
            <div class="kpi-icon">🔧</div>
            <div class="kpi-value"><?= $total_maintenance ?></div>
            <div class="kpi-label">Maintenance en cours</div>
        </div>
    </div>

    <!-- Alertes Intelligentes -->
    <?php if(!empty($alertes)): ?>
    <div class="section">
        <div class="section-header">
            <div class="section-title">🔔 Alertes Intelligentes</div>
            <a href="modules/dashboard/alertes.php" class="section-link">Voir toutes les alertes →</a>
        </div>
        <div class="alertes-mini">
            <?php foreach($alertes as $a): ?>
            <div class="alerte-item <?= $a[0] ?>">
                <div class="alerte-icon"><?= $a[1] ?></div>
                <div class="alerte-content">
                    <div class="alerte-title"><?= $a[2] ?></div>
                    <div class="alerte-desc"><?= $a[3] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Dernières réservations -->
    <div class="section">
        <div class="section-header">
            <div class="section-title">📋 Dernières réservations</div>
            <a href="modules/reservations/index.php" class="section-link">Toutes les réservations →</a>
        </div>
        <table class="table">
            <thead>
                <tr><th>ID</th><th>Client</th><th>Chambre</th><th>Dates</th><th>Prix</th><th>Statut</th></tr>
            </thead>
            <tbody>
            <?php if($dernieres_reservations->num_rows === 0): ?>
                <tr><td colspan="6" style="text-align:center;padding:30px">Aucune réservation</td></tr>
            <?php else: ?>
                <?php while($r = $dernieres_reservations->fetch_assoc()): ?>
                <tr onclick="window.location='modules/reservations/modifier.php?id=<?= $r['id'] ?>'" style="cursor:pointer">
                    <td><strong>#<?= $r['id'] ?></strong></td>
                    <td><?= htmlspecialchars($r['client_nom']) ?></td>
                    <td>N°<?= $r['chambre_num'] ?></td>
                    <td><?= $r['date_debut'] ?> → <?= $r['date_fin'] ?></td>
                    <td><?= number_format($r['prix_total'], 0) ?> MAD</td>
                    <td>
                        <span class="badge badge-<?= $r['statut'] ?>">
                            <?php $labels = ['confirmee'=>'Confirmée','checkin'=>'Checkin','checkout'=>'Checkout','annulee'=>'Annulée'];
                            echo $labels[$r['statut']] ?? $r['statut']; ?>
                        </span>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ========== SECTION DASHBOARD & STATISTIQUES ========== -->
    <div class="section">
        <div class="section-header">
            <div class="section-title">📊 Dashboard & Statistiques</div>
            <span class="section-link">Analyse des données <?= $annee ?></span>
        </div>

        <div class="charts-grid">
            <div class="chart-card">
                <div class="section-title" style="font-size:16px; margin-bottom:15px;">📈 Réservations par mois (<?= $annee ?>)</div>
                <canvas id="chartMois"></canvas>
            </div>
            <div class="chart-card">
                <div class="section-title" style="font-size:16px; margin-bottom:15px;">🥧 Répartition par type de chambre</div>
                <canvas id="chartType"></canvas>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-card">
                <div class="section-title" style="font-size:16px; margin-bottom:15px;">🌅 Popularité par vue</div>
                <canvas id="chartVue"></canvas>
            </div>
            <div class="chart-card">
                <div class="section-title" style="font-size:16px; margin-bottom:15px;">💰 Revenus mensuels (MAD)</div>
                <canvas id="chartRevenus"></canvas>
            </div>
        </div>

        <div class="charts-grid">
            <div class="chart-card">
                <div class="section-title" style="font-size:16px; margin-bottom:15px;">🏆 Top 5 clients (dépenses)</div>
                <table class="top5-table">
                    <thead><tr><th>Client</th><th>Séjours</th><th>Total MAD</th></tr></thead>
                    <tbody>
                    <?php if($top_clients->num_rows>0): ?>
                        <?php while($c = $top_clients->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['nom']) ?></td>
                            <td><?= $c['nb_sejours'] ?></td>
                            <td style="color:#f0c040;"><?= number_format($c['total'],0) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="3">Aucune donnée</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="chart-card">
                <div class="section-title" style="font-size:16px; margin-bottom:15px;">📊 Indicateurs clés</div>
                <table class="top5-table">
                    <tr><th>Taux d'occupation actuel</th><td style="color:#f0c040;"><?= round(($total_chambres>0 ? ($total_chambres - $total_maintenance)/$total_chambres*100 : 0),1) ?>%</td></tr>
                    <tr><th>Revenu total encaissé</th><td style="color:#f0c040;"><?= number_format($total_paiements,0) ?> MAD</td></tr>
                    <tr><th>Réservations confirmées</th><td style="color:#f0c040;"><?= $total_reservations ?></td></tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Actions Rapides -->
    <div class="section">
        <div class="section-header">
            <div class="section-title">⚡ Actions Rapides</div>
        </div>
        <div class="actions-grid">
            <a href="modules/recherche/index.php" class="action-btn">
                <div class="action-icon">🔍</div>
                <div class="action-label">Recherche Rapide</div>
                <div class="action-desc">Clients, chambres, réservations</div>
            </a>
            <a href="modules/reservations/ajouter.php" class="action-btn">
                <div class="action-icon">➕</div>
                <div class="action-label">Nouvelle Réservation</div>
                <div class="action-desc">Ajouter un client et réserver</div>
            </a>
            <a href="modules/paiements/index.php" class="action-btn">
                <div class="action-icon">💳</div>
                <div class="action-label">Encaisser</div>
                <div class="action-desc">Paiements en attente</div>
            </a>
            <a href="modules/maintenance/ajouter.php" class="action-btn">
                <div class="action-icon">🔧</div>
                <div class="action-label">Maintenance</div>
                <div class="action-desc">Signaler un problème</div>
            </a>
        </div>
    </div>

</div>

<script>
// Graphique réservations par mois
const ctxMois = document.getElementById('chartMois').getContext('2d');
new Chart(ctxMois, {
    type: 'bar',
    data: {
        labels: <?= json_encode($mois_labels) ?>,
        datasets: [{
            label: 'Réservations',
            data: <?= json_encode($mois_data) ?>,
            backgroundColor: 'rgba(240,192,64,0.8)',
            borderRadius: 6
        }]
    },
    options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { labels: { color: '#fff' } } } }
});

// Graphique type de chambre
const ctxType = document.getElementById('chartType').getContext('2d');
new Chart(ctxType, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($type_labels) ?>,
        datasets: [{ data: <?= json_encode($type_data) ?>, backgroundColor: ['#f0c040','#17a2b8','#28a745'] }]
    },
    options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { labels: { color: '#fff' } } } }
});

// Graphique vue
const ctxVue = document.getElementById('chartVue').getContext('2d');
new Chart(ctxVue, {
    type: 'polarArea',
    data: {
        labels: <?= json_encode($vue_labels) ?>,
        datasets: [{ data: <?= json_encode($vue_data) ?>, backgroundColor: ['#f0c040','#17a2b8','#28a745','#dc3545'] }]
    },
    options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { labels: { color: '#fff' } } } }
});

// Graphique revenus mensuels
const ctxRev = document.getElementById('chartRevenus').getContext('2d');
new Chart(ctxRev, {
    type: 'line',
    data: {
        labels: <?= json_encode($mois_labels) ?>,
        datasets: [{
            label: 'Revenus (MAD)',
            data: <?= json_encode($revenus_data) ?>,
            borderColor: '#f0c040',
            backgroundColor: 'rgba(240,192,64,0.15)',
            fill: true,
            tension: 0.3
        }]
    },
    options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { labels: { color: '#fff' } } } }
});
</script>

</body>
</html>