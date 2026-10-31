<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../modules/auth/login.php");
    exit;
}
require_once '../../config/database.php';

$query   = isset($_GET['q']) ? $conn->real_escape_string(trim($_GET['q'])) : '';
$filtre  = isset($_GET['filtre']) ? $_GET['filtre'] : 'all';

$resultats_clients      = [];
$resultats_chambres     = [];
$resultats_reservations = [];
$resultats_paiements    = [];

if (strlen($query) >= 2) {

    // 🔍 Clients
    if ($filtre === 'all' || $filtre === 'clients') {
        $r = $conn->query("
            SELECT c.*, COUNT(res.id) as nb_sejours
            FROM clients c
            LEFT JOIN reservations res ON c.id = res.client_id
            WHERE c.nom LIKE '%$query%'
               OR c.cin LIKE '%$query%'
               OR c.telephone LIKE '%$query%'
               OR c.email LIKE '%$query%'
            GROUP BY c.id
        ");
        while($row = $r->fetch_assoc()) $resultats_clients[] = $row;
    }

    // 🔍 Chambres
    if ($filtre === 'all' || $filtre === 'chambres') {
        $r = $conn->query("
            SELECT * FROM chambres
            WHERE numero LIKE '%$query%'
               OR type LIKE '%$query%'
               OR vue LIKE '%$query%'
               OR etat LIKE '%$query%'
        ");
        while($row = $r->fetch_assoc()) $resultats_chambres[] = $row;
    }

    // 🔍 Réservations
    if ($filtre === 'all' || $filtre === 'reservations') {
        $r = $conn->query("
            SELECT r.*, c.nom as client_nom, c.cin,
                   ch.numero as chambre_num, ch.type
            FROM reservations r
            JOIN clients c ON r.client_id = c.id
            JOIN chambres ch ON r.chambre_id = ch.id
            WHERE c.nom LIKE '%$query%'
               OR c.cin LIKE '%$query%'
               OR ch.numero LIKE '%$query%'
               OR r.statut LIKE '%$query%'
               OR r.date_debut LIKE '%$query%'
               OR r.date_fin LIKE '%$query%'
            ORDER BY r.id DESC
        ");
        while($row = $r->fetch_assoc()) $resultats_reservations[] = $row;
    }

    // 🔍 Paiements
    if ($filtre === 'all' || $filtre === 'paiements') {
        $r = $conn->query("
            SELECT p.*, c.nom as client_nom, ch.numero as chambre_num
            FROM paiements p
            JOIN reservations r ON p.reservation_id = r.id
            JOIN clients c ON r.client_id = c.id
            JOIN chambres ch ON r.chambre_id = ch.id
            WHERE c.nom LIKE '%$query%'
               OR p.statut LIKE '%$query%'
               OR p.methode LIKE '%$query%'
               OR ch.numero LIKE '%$query%'
        ");
        while($row = $r->fetch_assoc()) $resultats_paiements[] = $row;
    }
}

$total = count($resultats_clients) + count($resultats_chambres) +
         count($resultats_reservations) + count($resultats_paiements);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Recherche — IntelliHotel</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:Arial; min-height:100vh; background:linear-gradient(135deg,#1a1a2e,#16213e,#0f3460); }
        .navbar { background:rgba(26,26,46,0.98); color:white; padding:15px 30px; display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #f0c040; }
        .navbar h1 { font-size:20px; }
        .navbar a  { color:#f0c040; text-decoration:none; font-size:14px; margin-left:15px; }
        .container { padding:30px; max-width:1000px; margin:0 auto; }
        .page-title    { color:white; font-size:26px; font-weight:bold; margin-bottom:5px; }
        .page-subtitle { color:rgba(255,255,255,0.6); font-size:14px; margin-bottom:25px; }

        /* Search Bar */
        .search-box {
            background:rgba(255,255,255,0.08);
            backdrop-filter:blur(12px);
            border:1px solid rgba(255,255,255,0.2);
            border-radius:16px;
            padding:25px;
            margin-bottom:25px;
        }
        .search-form { display:flex; gap:10px; margin-bottom:15px; }
        .search-input {
            flex:1; padding:14px 20px;
            background:rgba(255,255,255,0.12);
            border:2px solid rgba(255,255,255,0.2);
            border-radius:10px; color:white;
            font-size:16px; transition:0.3s;
        }
        .search-input::placeholder { color:rgba(255,255,255,0.45); }
        .search-input:focus { outline:none; border-color:#f0c040; background:rgba(255,255,255,0.18); }
        .search-btn {
            padding:14px 28px;
            background:linear-gradient(135deg,#f0c040,#e0a020);
            color:#1a1a2e; border:none; border-radius:10px;
            font-size:15px; font-weight:bold; cursor:pointer;
            transition:0.3s;
        }
        .search-btn:hover { transform:translateY(-2px); box-shadow:0 5px 15px rgba(240,192,64,0.4); }

        /* Filtres */
        .filtres { display:flex; gap:8px; flex-wrap:wrap; }
        .filtre {
            padding:7px 16px; border-radius:20px;
            text-decoration:none; font-size:13px; font-weight:bold;
            border:1px solid rgba(255,255,255,0.2);
            color:rgba(255,255,255,0.7); transition:0.2s;
        }
        .filtre:hover, .filtre.active {
            background:#f0c040; color:#1a1a2e;
            border-color:#f0c040;
        }

        /* Résultats */
        .resultats-count {
            color:rgba(255,255,255,0.7); font-size:14px;
            margin-bottom:20px;
        }
        .resultats-count strong { color:#f0c040; font-size:18px; }

        /* Section */
        .section { margin-bottom:30px; }
        .section-title {
            color:#f0c040; font-size:16px; font-weight:bold;
            margin-bottom:15px; padding-bottom:8px;
            border-bottom:1px solid rgba(240,192,64,0.3);
            display:flex; align-items:center; gap:8px;
        }
        .section-count {
            background:rgba(240,192,64,0.2);
            color:#f0c040; padding:2px 10px;
            border-radius:10px; font-size:12px;
        }

        /* Cards */
        .cards-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:15px; }
        .result-card {
            background:rgba(255,255,255,0.95);
            border-radius:12px; padding:18px;
            transition:0.3s; cursor:pointer;
        }
        .result-card:hover { transform:translateY(-3px); box-shadow:0 8px 25px rgba(0,0,0,0.3); }
        .card-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; }
        .card-title  { font-size:15px; font-weight:bold; color:#1a1a2e; }
        .card-badge  { padding:4px 10px; border-radius:10px; font-size:11px; font-weight:bold; }

        .badge-vide     { background:#d4edda; color:#155724; }
        .badge-reservee { background:#f8d7da; color:#721c24; }
        .badge-occupee  { background:#fff3cd; color:#856404; }
        .badge-confirmee{ background:#cce5ff; color:#004085; }
        .badge-checkin  { background:#d4edda; color:#155724; }
        .badge-checkout { background:#e2e3e5; color:#383d41; }
        .badge-paye     { background:#d4edda; color:#155724; }
        .badge-attente  { background:#fff3cd; color:#856404; }
        .badge-vip      { background:linear-gradient(135deg,#f0c040,#e0a020); color:#1a1a2e; }

        .card-info { font-size:13px; color:#555; line-height:1.8; }
        .card-info span { color:#888; }
        .card-link {
            display:inline-block; margin-top:10px;
            color:#1a1a2e; font-size:12px; font-weight:bold;
            text-decoration:none; border-bottom:1px solid #f0c040;
        }

        /* Highlight */
        mark {
            background:rgba(240,192,64,0.4);
            color:#1a1a2e; border-radius:3px;
            padding:0 2px;
        }

        /* Vide */
        .no-result {
            text-align:center; padding:50px;
            color:rgba(255,255,255,0.5);
        }
        .no-result .icon { font-size:50px; margin-bottom:15px; }

        /* Suggestions */
        .suggestions {
            background:rgba(255,255,255,0.05);
            border-radius:12px; padding:20px;
            text-align:center;
        }
        .suggestions p { color:rgba(255,255,255,0.6); margin-bottom:15px; font-size:14px; }
        .suggestion-tags { display:flex; gap:8px; flex-wrap:wrap; justify-content:center; }
        .tag {
            background:rgba(255,255,255,0.1);
            color:rgba(255,255,255,0.8);
            padding:6px 14px; border-radius:20px;
            text-decoration:none; font-size:13px;
            border:1px solid rgba(255,255,255,0.15);
            transition:0.2s;
        }
        .tag:hover { background:#f0c040; color:#1a1a2e; border-color:#f0c040; }
    </style>
</head>
<body>

<div class="navbar">
    <h1>🏨 IntelliHotel</h1>
    <div>
        <a href="../../index.php">🏠 Dashboard</a>
        <a href="../clients/index.php">👥 Clients</a>
        <a href="../reservations/index.php">📅 Réservations</a>
    </div>
</div>

<div class="container">

    <div class="page-title">🔍 Recherche Rapide</div>
    <div class="page-subtitle">Recherchez dans tous les modules — clients, chambres, réservations, paiements</div>

    <!-- Search Box -->
    <div class="search-box">
        <form method="GET" class="search-form">
            <input type="text" name="q" class="search-input"
                   placeholder="🔍 Rechercher par nom, CIN, N° chambre, date..."
                   value="<?= htmlspecialchars($query) ?>" autofocus>
            <input type="hidden" name="filtre" value="<?= $filtre ?>">
            <button type="submit" class="search-btn">🔍 Chercher</button>
        </form>

        <!-- Filtres -->
        <div class="filtres">
            <a href="?q=<?= urlencode($query) ?>&filtre=all"          class="filtre <?= $filtre==='all'          ? 'active':'' ?>">🔍 Tout</a>
            <a href="?q=<?= urlencode($query) ?>&filtre=clients"      class="filtre <?= $filtre==='clients'      ? 'active':'' ?>">👥 Clients</a>
            <a href="?q=<?= urlencode($query) ?>&filtre=chambres"     class="filtre <?= $filtre==='chambres'     ? 'active':'' ?>">🚪 Chambres</a>
            <a href="?q=<?= urlencode($query) ?>&filtre=reservations" class="filtre <?= $filtre==='reservations' ? 'active':'' ?>">📅 Réservations</a>
            <a href="?q=<?= urlencode($query) ?>&filtre=paiements"    class="filtre <?= $filtre==='paiements'    ? 'active':'' ?>">💰 Paiements</a>
        </div>
    </div>

    <?php if(strlen($query) < 2): ?>
        <!-- Suggestions -->
        <div class="suggestions">
            <p>💡 Essayez de rechercher par :</p>
            <div class="suggestion-tags">
                <a href="?q=Ahmed&filtre=all"      class="tag">👤 Ahmed</a>
                <a href="?q=Suite&filtre=all"       class="tag">🚪 Suite</a>
                <a href="?q=Mer&filtre=all"          class="tag">🌊 Mer</a>
                <a href="?q=checkout&filtre=all"    class="tag">📅 Checkout</a>
                <a href="?q=paye&filtre=all"         class="tag">💰 Payé</a>
                <a href="?q=2026-05&filtre=all"     class="tag">📆 Mai 2026</a>
                <a href="?q=Double&filtre=all"      class="tag">🛏️ Double</a>
                <a href="?q=VIP&filtre=all"          class="tag">⭐ VIP</a>
            </div>
        </div>

    <?php elseif($total === 0): ?>
        <div class="no-result">
            <div class="icon">🔍</div>
            <p>Aucun résultat pour "<strong style="color:white"><?= htmlspecialchars($query) ?></strong>"</p>
        </div>

    <?php else: ?>

        <div class="resultats-count">
            <strong><?= $total ?></strong> résultat(s) trouvé(s) pour "<strong style="color:white"><?= htmlspecialchars($query) ?></strong>"
        </div>

        <?php
        // دالة highlight
        function hl($text, $query) {
            if (!$query) return htmlspecialchars($text);
            return preg_replace('/(' . preg_quote($query, '/') . ')/i', '<mark>$1</mark>', htmlspecialchars($text));
        }
        ?>

        <!-- Clients -->
        <?php if(!empty($resultats_clients)): ?>
        <div class="section">
            <div class="section-title">
                👥 Clients
                <span class="section-count"><?= count($resultats_clients) ?></span>
            </div>
            <div class="cards-grid">
                <?php foreach($resultats_clients as $c): ?>
                <div class="result-card" onclick="window.location='../clients/modifier.php?id=<?= $c['id'] ?>'">
                    <div class="card-header">
                        <div class="card-title"><?= hl($c['nom'], $query) ?></div>
                        <?php if($c['nb_sejours'] >= 3): ?>
                            <span class="card-badge badge-vip">⭐ VIP</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-info">
                        <span>CIN:</span> <?= hl($c['cin'], $query) ?><br>
                        <span>Tél:</span> <?= hl($c['telephone'], $query) ?><br>
                        <span>Email:</span> <?= hl($c['email'] ?: '—', $query) ?><br>
                        <span>Séjours:</span> <?= $c['nb_sejours'] ?>
                    </div>
                    <a href="../clients/modifier.php?id=<?= $c['id'] ?>" class="card-link">Modifier →</a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Chambres -->
        <?php if(!empty($resultats_chambres)): ?>
        <div class="section">
            <div class="section-title">
                🚪 Chambres
                <span class="section-count"><?= count($resultats_chambres) ?></span>
            </div>
            <div class="cards-grid">
                <?php foreach($resultats_chambres as $ch): ?>
                <div class="result-card" onclick="window.location='../chambres/modifier.php?id=<?= $ch['id'] ?>'">
                    <div class="card-header">
                        <div class="card-title">N° <?= hl($ch['numero'], $query) ?></div>
                        <span class="card-badge badge-<?= $ch['etat'] ?>">
                            <?php
                            $icons  = ['vide'=>'🟢 Disponible','reservee'=>'🔴 Réservée','occupee'=>'🟡 Occupée'];
                            echo $icons[$ch['etat']];
                            ?>
                        </span>
                    </div>
                    <div class="card-info">
                        <span>Type:</span> <?= hl($ch['type'], $query) ?><br>
                        <span>Vue:</span> <?= hl($ch['vue'], $query) ?><br>
                        <span>Prix:</span> <?= number_format($ch['prix_base'], 0) ?> MAD/nuit
                    </div>
                    <a href="../chambres/modifier.php?id=<?= $ch['id'] ?>" class="card-link">Modifier →</a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Réservations -->
        <?php if(!empty($resultats_reservations)): ?>
        <div class="section">
            <div class="section-title">
                📅 Réservations
                <span class="section-count"><?= count($resultats_reservations) ?></span>
            </div>
            <div class="cards-grid">
                <?php foreach($resultats_reservations as $r): ?>
                <div class="result-card">
                    <div class="card-header">
                        <div class="card-title">#<?= $r['id'] ?> — <?= hl($r['client_nom'], $query) ?></div>
                        <span class="card-badge badge-<?= $r['statut'] ?>">
                            <?php
                            $labels = ['confirmee'=>'✅ Confirmée','checkin'=>'🟢 Checkin','checkout'=>'⬜ Checkout','annulee'=>'❌ Annulée','en_attente'=>'⏳ Attente'];
                            echo $labels[$r['statut']] ?? $r['statut'];
                            ?>
                        </span>
                    </div>
                    <div class="card-info">
                        <span>Chambre:</span> N°<?= hl($r['chambre_num'], $query) ?> — <?= $r['type'] ?><br>
                        <span>Dates:</span> <?= hl($r['date_debut'], $query) ?> → <?= hl($r['date_fin'], $query) ?><br>
                        <span>Prix:</span> <strong><?= number_format($r['prix_total'], 0) ?> MAD</strong>
                    </div>
                    <a href="../reservations/index.php" class="card-link">Voir réservations →</a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Paiements -->
        <?php if(!empty($resultats_paiements)): ?>
        <div class="section">
            <div class="section-title">
                💰 Paiements
                <span class="section-count"><?= count($resultats_paiements) ?></span>
            </div>
            <div class="cards-grid">
                <?php foreach($resultats_paiements as $p): ?>
                <div class="result-card">
                    <div class="card-header">
                        <div class="card-title"><?= hl($p['client_nom'], $query) ?></div>
                        <span class="card-badge badge-<?= $p['statut']==='en_attente' ? 'attente' : $p['statut'] ?>">
                            <?= $p['statut'] === 'paye' ? '✅ Payé' : '⏳ En attente' ?>
                        </span>
                    </div>
                    <div class="card-info">
                        <span>Chambre:</span> N°<?= hl($p['chambre_num'], $query) ?><br>
                        <span>Montant:</span> <strong><?= number_format($p['montant'], 0) ?> MAD</strong><br>
                        <span>Méthode:</span> <?= hl($p['methode'] ?? '—', $query) ?><br>
                        <span>Date:</span> <?= $p['date_paiement'] ?? '—' ?>
                    </div>
                    <a href="../paiements/facture.php?id=<?= $p['reservation_id'] ?>" class="card-link">Voir facture →</a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    <?php endif; ?>

</div>

<!-- AJAX Live Search -->
<script>
let timer = null;
document.querySelector('.search-input').addEventListener('input', function() {
    clearTimeout(timer);
    const q = this.value.trim();
    if (q.length >= 2) {
        timer = setTimeout(() => {
            const filtre = document.querySelector('input[name="filtre"]').value;
            window.location.href = `index.php?q=${encodeURIComponent(q)}&filtre=${filtre}`;
        }, 600);
    }
});
</script>

</body>
</html>