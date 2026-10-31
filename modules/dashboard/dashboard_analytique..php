<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../modules/auth/login.php");
    exit;
}
require_once '../../config/database.php';

// ==========================================================
// 1. KPIs globaux
// ==========================================================
$total_chambres = $conn->query("SELECT COUNT(*) as n FROM chambres")->fetch_assoc()['n'];
$total_reservations = $conn->query("SELECT COUNT(*) as n FROM reservations")->fetch_assoc()['n'];

$chambres_occupees = $conn->query("SELECT COUNT(*) as n FROM chambres WHERE etat='occupee'")->fetch_assoc()['n'];
$taux_occupation = $total_chambres > 0 ? round(($chambres_occupees / $total_chambres) * 100, 1) : 0;

$revenus_total = $conn->query("SELECT SUM(montant) as t FROM paiements WHERE statut='paye'")->fetch_assoc()['t'] ?? 0;
$revpar = $total_chambres > 0 ? round($revenus_total / $total_chambres, 0) : 0;

$nuits_vendues = $conn->query("SELECT SUM(DATEDIFF(date_fin, date_debut)) as nuits FROM reservations WHERE statut NOT IN ('annulee')")->fetch_assoc()['nuits'] ?? 0;
$adr = $nuits_vendues > 0 ? round($revenus_total / $nuits_vendues, 0) : 0;
$duree_moyenne = ($total_reservations > 0 && $nuits_vendues > 0) ? round($nuits_vendues / $total_reservations, 1) : 0;

// ==========================================================
// 2. Graphiques
// ==========================================================

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

// Répartition par type
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

// Taux d'occupation par type de chambre (actuel)
$occup_type = $conn->query("
    SELECT ch.type,
           COUNT(CASE WHEN ch.etat='occupee' THEN 1 END) as occupees,
           COUNT(*) as total
    FROM chambres ch
    GROUP BY ch.type
");
$type_occup_labels = [];
$type_occup_data = [];
while($ot = $occup_type->fetch_assoc()) {
    $type_occup_labels[] = $ot['type'];
    $taux = $ot['total'] > 0 ? round(($ot['occupees'] / $ot['total']) * 100, 1) : 0;
    $type_occup_data[] = $taux;
}

// Top clients
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
    <title>Dashboard Analytique — IntelliHotel</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460);
            color: #eee;
            padding: 20px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        .navbar {
            background: rgba(10,12,21,0.95);
            padding: 15px 25px;
            border-radius: 16px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar h1 { font-size: 22px; }
        .navbar h1 span { color: #f0c040; }
        .navbar a {
            color: #f0c040;
            text-decoration: none;
            background: rgba(240,192,64,0.1);
            padding: 8px 18px;
            border-radius: 30px;
        }
        .page-title {
            font-size: 28px;
            font-weight: bold;
            background: linear-gradient(135deg,#fff,#f0c040);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }
        .page-subtitle {
            color: rgba(255,255,255,0.5);
            margin-bottom: 25px;
        }
        /* KPIs */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px,1fr));
            gap: 20px;
            margin-bottom: 35px;
        }
        .kpi-card {
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 18px;
            text-align: center;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .kpi-value {
            font-size: 30px;
            font-weight: bold;
            color: #f0c040;
        }
        .kpi-label {
            font-size: 12px;
            color: rgba(255,255,255,0.6);
            margin-top: 5px;
        }
        /* Charts */
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px,1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        .chart-card {
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 20px;
            border: 1px solid rgba(255,255,255,0.08);
        }
        .chart-title {
            color: #f0c040;
            font-weight: bold;
            margin-bottom: 15px;
            font-size: 16px;
        }
        canvas { max-height: 250px; width: 100% !important; }
        .top-table {
            width: 100%;
            border-collapse: collapse;
        }
        .top-table th {
            text-align: left;
            padding: 8px 5px;
            color: #f0c040;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        .top-table td {
            padding: 8px 5px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        @media (max-width: 800px) {
            .charts-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="navbar">
        <h1>🏨 <span>IntelliHotel</span> — Analytics</h1>
        <a href="../../index.php">🏠 Dashboard principal</a>
    </div>

    <div class="page-title">📊 Tableau de Bord Analytique</div>
    <div class="page-subtitle">Statistiques détaillées et indicateurs clés <?= $annee ?></div>

    <!-- KPIs -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-value"><?= $taux_occupation ?>%</div>
            <div class="kpi-label">Taux d'occupation</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value"><?= number_format($revenus_total,0) ?> MAD</div>
            <div class="kpi-label">Revenus totaux (payés)</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value"><?= number_format($revpar,0) ?> MAD</div>
            <div class="kpi-label">RevPAR</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value"><?= number_format($adr,0) ?> MAD</div>
            <div class="kpi-label">ADR (prix moyen/nuit)</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-value"><?= $duree_moyenne ?></div>
            <div class="kpi-label">Durée moyenne séjour (nuits)</div>
        </div>
    </div>

    <!-- Graphiques ligne 1 -->
    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-title">📈 Réservations par mois (<?= $annee ?>)</div>
            <canvas id="chartMois"></canvas>
        </div>
        <div class="chart-card">
            <div class="chart-title">🥧 Répartition par type de chambre</div>
            <canvas id="chartType"></canvas>
        </div>
    </div>

    <!-- Graphiques ligne 2 -->
    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-title">🌅 Popularité par vue</div>
            <canvas id="chartVue"></canvas>
        </div>
        <div class="chart-card">
            <div class="chart-title">📊 Taux d'occupation par type</div>
            <canvas id="chartOccupType"></canvas>
        </div>
    </div>

    <!-- Top clients + tendances -->
    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-title">🏆 Top 5 clients (dépenses)</div>
            <table class="top-table">
                <thead><tr><th>Client</th><th>Séjours</th><th>Total MAD</th></tr></thead>
                <tbody>
                <?php while($c = $top_clients->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['nom']) ?></td>
                        <td><?= $c['nb_sejours'] ?></td>
                        <td style="color:#f0c040"><?= number_format($c['total'],0) ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div class="chart-card">
            <div class="chart-title">💰 Revenus mensuels (MAD)</div>
            <canvas id="chartRevenus"></canvas>
        </div>
    </div>

</div>

<script>
// Barres réservations par mois
const ctxMois = document.getElementById('chartMois').getContext('2d');
new Chart(ctxMois, {
    type: 'bar',
    data: {
        labels: <?= json_encode($mois_labels) ?>,
        datasets: [{
            label: 'Nombre de réservations',
            data: <?= json_encode($mois_data) ?>,
            backgroundColor: 'rgba(240,192,64,0.7)',
            borderRadius: 6
        }]
    },
    options: { responsive: true, plugins: { legend: { labels: { color: '#fff' } } } }
});

// Type (doughnut)
const ctxType = document.getElementById('chartType').getContext('2d');
new Chart(ctxType, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($type_labels) ?>,
        datasets: [{ data: <?= json_encode($type_data) ?>, backgroundColor: ['#f0c040','#17a2b8','#28a745'] }]
    },
    options: { responsive: true, plugins: { legend: { labels: { color: '#fff' } } } }
});

// Vue (polarArea)
const ctxVue = document.getElementById('chartVue').getContext('2d');
new Chart(ctxVue, {
    type: 'polarArea',
    data: {
        labels: <?= json_encode($vue_labels) ?>,
        datasets: [{ data: <?= json_encode($vue_data) ?>, backgroundColor: ['#f0c040','#17a2b8','#28a745','#dc3545'] }]
    },
    options: { responsive: true, plugins: { legend: { labels: { color: '#fff' } } } }
});

// Taux occupation par type
const ctxOccup = document.getElementById('chartOccupType').getContext('2d');
new Chart(ctxOccup, {
    type: 'bar',
    data: {
        labels: <?= json_encode($type_occup_labels) ?>,
        datasets: [{ label: 'Taux occupation (%)', data: <?= json_encode($type_occup_data) ?>, backgroundColor: '#f0c040' }]
    },
    options: {
        responsive: true,
        scales: { y: { max: 100, ticks: { color: '#fff' } } },
        plugins: { legend: { labels: { color: '#fff' } } }
    }
});

// Revenus mensuels (ligne)
const ctxRev = document.getElementById('chartRevenus').getContext('2d');
new Chart(ctxRev, {
    type: 'line',
    data: {
        labels: <?= json_encode($mois_labels) ?>,
        datasets: [{ label: 'Revenus (MAD)', data: <?= json_encode($revenus_data) ?>, borderColor: '#f0c040', backgroundColor: 'rgba(240,192,64,0.1)', fill: true, tension: 0.3 }]
    },
    options: { responsive: true, plugins: { legend: { labels: { color: '#fff' } } } }
});
</script>
</body>
</html>