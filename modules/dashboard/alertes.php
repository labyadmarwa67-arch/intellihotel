<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../modules/auth/login.php");
    exit;
}
require_once '../../config/database.php';

// ========== جمع جميع الalertes ==========

$alertes = [];

// 1️⃣ Checkout اليوم
$checkout_today = $conn->query("
    SELECT r.id, c.nom as client_nom, c.telephone,
           ch.numero as chambre_num, r.date_fin, r.prix_total
    FROM reservations r
    JOIN clients c ON r.client_id = c.id
    JOIN chambres ch ON r.chambre_id = ch.id
    WHERE r.date_fin = CURDATE() AND r.statut = 'checkin'
");
while($r = $checkout_today->fetch_assoc()) {
    $alertes[] = [
        'type'    => 'checkout',
        'niveau'  => 'danger',
        'icon'    => '🚪',
        'titre'   => 'Checkout Aujourd\'hui',
        'message' => "Client {$r['client_nom']} — Chambre N°{$r['chambre_num']} — {$r['prix_total']} MAD",
        'action'  => "../../modules/reservations/index.php",
        'action_label' => 'Voir réservation'
    ];
}

// 2️⃣ Paiements en retard
$paiements_retard = $conn->query("
    SELECT p.id, p.montant, c.nom as client_nom, c.telephone,
           ch.numero as chambre_num, r.date_fin,
           DATEDIFF(CURDATE(), r.date_fin) as jours_retard
    FROM paiements p
    JOIN reservations r ON p.reservation_id = r.id
    JOIN clients c ON r.client_id = c.id
    JOIN chambres ch ON r.chambre_id = ch.id
    WHERE p.statut = 'en_attente' AND r.statut = 'checkout'
    ORDER BY jours_retard DESC
");
while($p = $paiements_retard->fetch_assoc()) {
    $alertes[] = [
        'type'    => 'paiement',
        'niveau'  => 'warning',
        'icon'    => '💰',
        'titre'   => "Paiement en Retard — {$p['jours_retard']} jour(s)",
        'message' => "Client {$p['client_nom']} — Chambre N°{$p['chambre_num']} — {$p['montant']} MAD",
        'action'  => "../../modules/paiements/index.php",
        'action_label' => 'Encaisser'
    ];
}

// 3️⃣ Maintenance prolongée > 2 jours
$maintenance_prolongee = $conn->query("
    SELECT m.id, m.description, m.date_debut,
           ch.numero as chambre_num, ch.type,
           u.nom as technicien,
           DATEDIFF(CURDATE(), m.date_debut) as nb_jours
    FROM maintenance m
    JOIN chambres ch ON m.chambre_id = ch.id
    JOIN utilisateurs u ON m.technicien_id = u.id
    WHERE m.statut IN ('en_cours', 'prolongee')
    AND DATEDIFF(CURDATE(), m.date_debut) > 2
    ORDER BY nb_jours DESC
");
while($m = $maintenance_prolongee->fetch_assoc()) {
    $alertes[] = [
        'type'    => 'maintenance',
        'niveau'  => 'warning',
        'icon'    => '🔧',
        'titre'   => "Maintenance Prolongée — {$m['nb_jours']} jour(s)",
        'message' => "Chambre N°{$m['chambre_num']} — {$m['description']} — Technicien: {$m['technicien']}",
        'action'  => "../../modules/maintenance/index.php",
        'action_label' => 'Voir maintenance'
    ];
}

// 4️⃣ Chambres occupées > 7 jours
$long_sejour = $conn->query("
    SELECT r.id, c.nom as client_nom, c.telephone,
           ch.numero as chambre_num,
           r.date_debut, r.prix_total,
           DATEDIFF(CURDATE(), r.date_debut) as nb_jours
    FROM reservations r
    JOIN clients c ON r.client_id = c.id
    JOIN chambres ch ON r.chambre_id = ch.id
    WHERE r.statut = 'checkin'
    AND DATEDIFF(CURDATE(), r.date_debut) > 7
");
while($r = $long_sejour->fetch_assoc()) {
    $alertes[] = [
        'type'    => 'sejour',
        'niveau'  => 'info',
        'icon'    => '🏨',
        'titre'   => "Long Séjour — {$r['nb_jours']} jours",
        'message' => "Client {$r['client_nom']} — Chambre N°{$r['chambre_num']} depuis {$r['date_debut']}",
        'action'  => "../../modules/reservations/index.php",
        'action_label' => 'Voir réservation'
    ];
}

// 5️⃣ Clients VIP (>= 3 séjours)
$clients_vip = $conn->query("
    SELECT c.nom, c.telephone, COUNT(r.id) as nb_sejours,
           SUM(r.prix_total) as total_depense
    FROM clients c
    JOIN reservations r ON c.id = r.client_id
    GROUP BY c.id
    HAVING nb_sejours >= 3
    ORDER BY nb_sejours DESC
");
while($c = $clients_vip->fetch_assoc()) {
    $alertes[] = [
        'type'    => 'vip',
        'niveau'  => 'success',
        'icon'    => '⭐',
        'titre'   => "Client VIP — {$c['nb_sejours']} séjours",
        'message' => "{$c['nom']} — Total dépensé: " . number_format($c['total_depense'], 0) . " MAD",
        'action'  => "../../modules/clients/index.php",
        'action_label' => 'Voir client'
    ];
}

// 6️⃣ Checkin prévu aujourd'hui
$checkin_today = $conn->query("
    SELECT r.id, c.nom as client_nom, c.telephone,
           ch.numero as chambre_num, r.prix_total
    FROM reservations r
    JOIN clients c ON r.client_id = c.id
    JOIN chambres ch ON r.chambre_id = ch.id
    WHERE r.date_debut = CURDATE() AND r.statut = 'confirmee'
");
while($r = $checkin_today->fetch_assoc()) {
    $alertes[] = [
        'type'    => 'checkin',
        'niveau'  => 'success',
        'icon'    => '✅',
        'titre'   => 'Checkin Prévu Aujourd\'hui',
        'message' => "Client {$r['client_nom']} — Chambre N°{$r['chambre_num']} — {$r['prix_total']} MAD",
        'action'  => "../../modules/reservations/index.php",
        'action_label' => 'Faire le checkin'
    ];
}

// إحصاءات الـ alertes
$nb_danger  = count(array_filter($alertes, fn($a) => $a['niveau'] === 'danger'));
$nb_warning = count(array_filter($alertes, fn($a) => $a['niveau'] === 'warning'));
$nb_success = count(array_filter($alertes, fn($a) => $a['niveau'] === 'success'));
$nb_info    = count(array_filter($alertes, fn($a) => $a['niveau'] === 'info'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Alertes — IntelliHotel</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            /* Image de fond large, luxueuse et différente (vue toit-terrasse hôtel) */
            background: url('https://images.pexels.com/photos/261169/pexels-photo-261169.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&dpr=2') no-repeat center center fixed;
            background-size: cover;
            position: relative;
        }
        /* Overlay sophistiqué */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(2px);
            z-index: -1;
        }
        .navbar {
            background: rgba(10, 12, 21, 0.88);
            backdrop-filter: blur(12px);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #d4af37;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .navbar h1 { font-size: 24px; letter-spacing: 1px; color: white; }
        .navbar h1 span { color: #d4af37; }
        .navbar a {
            color: #d4af37;
            text-decoration: none;
            font-size: 14px;
            margin-left: 15px;
            padding: 8px 12px;
            border-radius: 30px;
            transition: 0.2s;
        }
        .navbar a:hover { background: rgba(212, 175, 55, 0.2); }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
        }
        .page-title {
            color: #d4af37;
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 8px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .page-subtitle {
            color: rgba(255,255,255,0.7);
            font-size: 14px;
            margin-bottom: 30px;
        }

        /* KPIs */
        .kpi-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 35px;
        }
        .kpi {
            border-radius: 24px;
            padding: 22px;
            text-align: center;
            backdrop-filter: blur(8px);
            transition: 0.3s;
        }
        .kpi:hover { transform: translateY(-5px); }
        .kpi-danger  { background: rgba(220,53,69,0.2); border: 1px solid rgba(220,53,69,0.5); }
        .kpi-warning { background: rgba(255,193,7,0.2);  border: 1px solid rgba(255,193,7,0.5); }
        .kpi-success { background: rgba(40,167,69,0.2);  border: 1px solid rgba(40,167,69,0.5); }
        .kpi-info    { background: rgba(23,162,184,0.2); border: 1px solid rgba(23,162,184,0.5); }
        .kpi .icon  { font-size: 36px; margin-bottom: 10px; }
        .kpi .value { font-size: 32px; font-weight: bold; color: white; }
        .kpi .label { font-size: 12px; color: rgba(255,255,255,0.8); margin-top: 6px; }

        /* Filtres */
        .filtres {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }
        .filtre-btn {
            padding: 8px 20px;
            border-radius: 40px;
            border: none;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
            transition: 0.2s;
            background: rgba(255,255,255,0.15);
            color: white;
        }
        .filtre-btn.active, .filtre-btn:hover {
            transform: translateY(-2px);
            background: #d4af37;
            color: #1a1a2e;
        }

        /* Alertes */
        .alertes-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .alerte-card {
            border-radius: 20px;
            padding: 20px 25px;
            display: flex;
            align-items: center;
            gap: 20px;
            backdrop-filter: blur(10px);
            transition: 0.3s;
            cursor: pointer;
        }
        .alerte-card:hover {
            transform: translateX(8px);
        }
        .alerte-danger  { background: rgba(220,53,69,0.2); border-left: 5px solid #dc3545; border-right: 1px solid rgba(220,53,69,0.4); }
        .alerte-warning { background: rgba(255,193,7,0.2);  border-left: 5px solid #ffc107; border-right: 1px solid rgba(255,193,7,0.4); }
        .alerte-success { background: rgba(40,167,69,0.2);  border-left: 5px solid #28a745; border-right: 1px solid rgba(40,167,69,0.4); }
        .alerte-info    { background: rgba(23,162,184,0.2); border-left: 5px solid #17a2b8; border-right: 1px solid rgba(23,162,184,0.4); }

        .alerte-icon { font-size: 40px; min-width: 60px; text-align: center; }
        .alerte-content { flex: 1; }
        .alerte-titre   { color: white; font-size: 16px; font-weight: bold; margin-bottom: 6px; }
        .alerte-message { color: rgba(255,255,255,0.8); font-size: 13px; }
        .alerte-action  {
            background: rgba(212, 175, 55, 0.2);
            color: #d4af37;
            padding: 8px 20px;
            border-radius: 40px;
            text-decoration: none;
            font-size: 12px;
            font-weight: bold;
            white-space: nowrap;
            transition: 0.2s;
            border: 1px solid rgba(212, 175, 55, 0.4);
        }
        .alerte-action:hover {
            background: #d4af37;
            color: #1a1a2e;
        }

        /* Refresh bar */
        .refresh-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .last-update {
            color: rgba(255,255,255,0.6);
            font-size: 12px;
            background: rgba(0,0,0,0.3);
            padding: 6px 14px;
            border-radius: 30px;
        }
        .refresh-btn {
            background: rgba(212, 175, 55, 0.2);
            color: #d4af37;
            padding: 8px 20px;
            border-radius: 40px;
            border: 1px solid rgba(212, 175, 55, 0.4);
            cursor: pointer;
            font-size: 13px;
            text-decoration: none;
            transition: 0.2s;
        }
        .refresh-btn:hover {
            background: #d4af37;
            color: #1a1a2e;
        }

        /* Message vide */
        .no-alertes {
            text-align: center;
            padding: 60px;
            background: rgba(40,167,69,0.15);
            border: 1px solid rgba(40,167,69,0.4);
            border-radius: 28px;
            backdrop-filter: blur(8px);
        }
        .no-alertes .icon { font-size: 70px; margin-bottom: 20px; }
        .no-alertes h3 { color: white; font-size: 24px; margin-bottom: 10px; }
        .no-alertes p { color: rgba(255,255,255,0.7); }

        @media (max-width: 800px) {
            .container { padding: 20px; }
            .kpi-row { grid-template-columns: repeat(2, 1fr); gap: 15px; }
            .alerte-card { flex-wrap: wrap; }
            .alerte-action { margin-top: 10px; }
        }
    </style>
</head>
<body>

<div class="navbar">
    <h1>🏨 <span>IntelliHotel</span></h1>
    <div>
        <a href="../../index.php">🏠 Dashboard</a>
        <a href="../../modules/reservations/index.php">📅 Réservations</a>
        <a href="../../modules/paiements/index.php">💰 Paiements</a>
        <a href="../../modules/maintenance/index.php">🔧 Maintenance</a>
    </div>
</div>

<div class="container">

    <div class="page-title">🔔 Alertes Intelligentes</div>
    <div class="page-subtitle">Surveillance automatique en temps réel — IntelliHotel</div>

    <!-- KPIs -->
    <div class="kpi-row">
        <div class="kpi kpi-danger">
            <div class="icon">🚨</div>
            <div class="value"><?= $nb_danger ?></div>
            <div class="label">Urgentes</div>
        </div>
        <div class="kpi kpi-warning">
            <div class="icon">⚠️</div>
            <div class="value"><?= $nb_warning ?></div>
            <div class="label">Avertissements</div>
        </div>
        <div class="kpi kpi-success">
            <div class="icon">✅</div>
            <div class="value"><?= $nb_success ?></div>
            <div class="label">Informations</div>
        </div>
        <div class="kpi kpi-info">
            <div class="icon">ℹ️</div>
            <div class="value"><?= $nb_info ?></div>
            <div class="label">À surveiller</div>
        </div>
    </div>

    <!-- Filtres + Refresh -->
    <div class="refresh-bar">
        <div class="filtres">
            <button class="filtre-btn active" onclick="filtrer('all')">🔔 Toutes (<?= count($alertes) ?>)</button>
            <button class="filtre-btn" onclick="filtrer('danger')">🚨 Urgentes (<?= $nb_danger ?>)</button>
            <button class="filtre-btn" onclick="filtrer('warning')">⚠️ Alertes (<?= $nb_warning ?>)</button>
            <button class="filtre-btn" onclick="filtrer('success')">✅ Info (<?= $nb_success ?>)</button>
        </div>
        <div>
            <span class="last-update">🕐 Mis à jour : <?= date('H:i:s') ?></span>
            <a href="alertes.php" class="refresh-btn" style="margin-left:10px">🔄 Actualiser</a>
        </div>
    </div>

    <!-- Liste des Alertes -->
    <div class="alertes-list" id="alertesList">
        <?php if(empty($alertes)): ?>
            <div class="no-alertes">
                <div class="icon">✅</div>
                <h3>Aucune alerte — Tout va bien !</h3>
                <p>Le système fonctionne normalement. Aucune intervention requise.</p>
            </div>
        <?php else: ?>
            <?php foreach($alertes as $a): ?>
            <div class="alerte-card alerte-<?= $a['niveau'] ?>" data-niveau="<?= $a['niveau'] ?>">
                <div class="alerte-icon"><?= $a['icon'] ?></div>
                <div class="alerte-content">
                    <div class="alerte-titre"><?= htmlspecialchars($a['titre']) ?></div>
                    <div class="alerte-message"><?= htmlspecialchars($a['message']) ?></div>
                </div>
                <a href="<?= $a['action'] ?>" class="alerte-action">
                    <?= $a['action_label'] ?> →
                </a>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<script>
// Filtre des alertes
function filtrer(niveau) {
    const cards = document.querySelectorAll('.alerte-card');
    cards.forEach(card => {
        if (niveau === 'all' || card.dataset.niveau === niveau) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
    // Mise à jour des boutons actifs
    document.querySelectorAll('.filtre-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
}

// Auto-refresh toutes les 60 secondes
setTimeout(() => location.reload(), 60000);
</script>

</body>
</html>