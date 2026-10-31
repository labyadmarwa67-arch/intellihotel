<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../modules/auth/login.php");
    exit;
}
require_once '../../config/database.php';

// Check-in
if (isset($_GET['checkin'])) {
    $id = (int)$_GET['checkin'];
    $res = $conn->query("SELECT chambre_id FROM reservations WHERE id=$id")->fetch_assoc();
    $conn->query("UPDATE reservations SET statut='checkin' WHERE id=$id");
    $conn->query("UPDATE chambres SET etat='occupee' WHERE id={$res['chambre_id']}");
    header("Location: index.php");
    exit;
}

// Check-out
if (isset($_GET['checkout'])) {
    $id = (int)$_GET['checkout'];
    $res = $conn->query("SELECT chambre_id FROM reservations WHERE id=$id")->fetch_assoc();
    $conn->query("UPDATE reservations SET statut='checkout' WHERE id=$id");
    $conn->query("UPDATE chambres SET etat='vide' WHERE id={$res['chambre_id']}");
    header("Location: index.php");
    exit;
}

// Annuler
if (isset($_GET['annuler'])) {
    $id = (int)$_GET['annuler'];
    $res = $conn->query("SELECT chambre_id FROM reservations WHERE id=$id")->fetch_assoc();
    $conn->query("UPDATE reservations SET statut='annulee' WHERE id=$id");
    $conn->query("UPDATE chambres SET etat='vide' WHERE id={$res['chambre_id']}");
    header("Location: index.php");
    exit;
}

$reservations = $conn->query("
    SELECT r.*, c.nom as client_nom, c.cin,
           ch.numero as chambre_num, ch.type, ch.vue,
           u.nom as receptionniste_nom
    FROM reservations r
    JOIN clients c ON r.client_id = c.id
    JOIN chambres ch ON r.chambre_id = ch.id
    LEFT JOIN utilisateurs u ON r.receptionniste_id = u.id
    ORDER BY r.date_debut DESC
");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réservations — IntelliHotel</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            /* Image de fond large, luxueuse et différente (vue piscine rooftop) */
            background: url('https://images.pexels.com/photos/261169/pexels-photo-261169.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&dpr=2') no-repeat center center fixed;
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
            background: rgba(0, 0, 0, 0.5);
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
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            background: rgba(15, 15, 30, 0.65);
            backdrop-filter: blur(8px);
            padding: 15px 25px;
            border-radius: 24px;
            border: 1px solid rgba(240,192,64,0.3);
        }
        .page-header h2 {
            color: #f0c040;
            font-size: 26px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 10px 22px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
            background: linear-gradient(135deg, #2a5298 0%, #1e3c72 100%);
        }
        table {
            width: 100%;
            background: rgba(20, 20, 35, 0.75);
            backdrop-filter: blur(8px);
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.25);
            border-collapse: collapse;
        }
        th {
            background: rgba(0, 0, 0, 0.6);
            color: #f0c040;
            padding: 16px 15px;
            text-align: left;
            font-size: 14px;
            font-weight: 600;
            border-bottom: 1px solid rgba(240,192,64,0.3);
        }
        td {
            padding: 14px 15px;
            font-size: 13px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.9);
        }
        tr:hover td {
            background: rgba(240,192,64,0.1);
        }
        .statut {
            padding: 5px 12px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: bold;
            display: inline-block;
        }
        .statut-confirmee  { background: #00408580; color: #9cd0ff; }
        .statut-checkin    { background: #15572480; color: #90ee90; }
        .statut-checkout   { background: #383d4180; color: #ccc; }
        .statut-annulee    { background: #721c2480; color: #ffaaaa; }
        .statut-en_attente { background: #85640480; color: #ffe69e; }
        .btn-sm {
            padding: 6px 14px;
            border-radius: 30px;
            text-decoration: none;
            font-size: 11px;
            font-weight: 500;
            display: inline-block;
            transition: 0.2s;
            margin-right: 5px;
        }
        .btn-checkin  { background: #28a745; color: white; }
        .btn-checkout { background: #6c757d; color: white; }
        .btn-facture  { background: #17a2b8; color: white; }
        .btn-annuler  { background: #dc3545; color: white; }
        .btn-sm:hover {
            transform: translateY(-1px);
            filter: brightness(1.05);
        }
        small {
            color: rgba(255,255,255,0.6);
            font-size: 11px;
        }
        @media (max-width: 900px) {
            .container { padding: 15px; }
            th, td { padding: 10px 8px; }
            .page-header { flex-direction: column; gap: 12px; text-align: center; }
            .btn-sm { margin-bottom: 5px; }
        }
    </style>
</head>
<body>
<div class="navbar">
    <h1>🏨 <span>IntelliHotel</span></h1>
    <a href="../../index.php">🏠 Tableau de bord</a>
</div>

<div class="container">
    <div class="page-header">
        <h2>📅 Gestion des Réservations</h2>
        <a href="ajouter.php" class="btn-primary">+ Nouvelle réservation</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Client</th>
                <th>Chambre</th>
                <th>Réceptionniste</th> 
                <th>Date Début</th>
                <th>Date Fin</th>
                <th>Prix Total</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
<tbody>
<?php if($reservations->num_rows === 0): ?>
    <tr>
        <td colspan="9" style="text-align:center; color:#888; padding:30px;">
            Aucune réservation enregistrée
        </td>
    </tr>
<?php else: ?>
    <?php while($r = $reservations->fetch_assoc()): ?>
    <tr>
        <td><strong>#<?= $r['id'] ?></strong></td>
        <td>
            👤 <?= htmlspecialchars($r['client_nom']) ?><br>
            <small style="color:#888"><?= htmlspecialchars($r['cin']) ?></small>
        </td>
        <td>
            🚪 N°<?= $r['chambre_num'] ?><br>
            <small style="color:#888"><?= htmlspecialchars($r['type']) ?> / <?= htmlspecialchars($r['vue']) ?></small>
        </td>
        
        <!-- ✅ العمود الجديد: اسم الموظف -->
        <td>
            <i class="fa fa-user-tie"></i> <?= htmlspecialchars($r['receptionniste_nom'] ?? 'Non assigné') ?>
        </td>
        
        <td><?= $r['date_debut'] ?></td>
        <td><?= $r['date_fin'] ?></td>
        <td><strong><?= number_format($r['prix_total'], 0) ?> MAD</strong></td>
        <td>
            <span class="statut statut-<?= $r['statut'] ?>">
                <?php
                $labels = [
                    'confirmee'  => '✅ Confirmée',
                    'checkin'    => '🟢 Check-in',
                    'checkout'   => '⬜ Check-out',
                    'annulee'    => '❌ Annulée',
                    'en_attente' => '⏳ En attente'
                ];
                echo $labels[$r['statut']] ?? $r['statut'];
                ?>
            </span>
        </td>
        <td>
            <?php if($r['statut'] === 'confirmee'): ?>
                <a href="?checkin=<?= $r['id'] ?>" class="btn-sm btn-checkin">▶ Check-in</a>
                <a href="?annuler=<?= $r['id'] ?>"
                   class="btn-sm btn-annuler"
                   onclick="return confirm('Annuler cette réservation ?')">✖ Annuler</a>
            <?php elseif($r['statut'] === 'checkin'): ?>
                <a href="?checkout=<?= $r['id'] ?>" class="btn-sm btn-checkout">⬛ Check-out</a>
            <?php elseif($r['statut'] === 'checkout'): ?>
                <a href="../paiements/facture.php?id=<?= $r['id'] ?>" 
                   class="btn-sm btn-facture">🖨️ Facture</a>
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
<?php endif; ?>
</tbody>
    </table>
</div>
</body>
</html>