<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../modules/auth/login.php");
    exit;
}
require_once '../../config/database.php';

// Encaisser un paiement
if (isset($_GET['payer'])) {
    $id     = (int)$_GET['payer'];
    $methode = $conn->real_escape_string($_GET['methode'] ?? 'Especes');
    $conn->query("UPDATE paiements SET statut='paye', methode='$methode', date_paiement=CURDATE() WHERE id=$id");
    header("Location: index.php");
    exit;
}

$paiements = $conn->query("
    SELECT p.*, r.date_debut, r.date_fin, r.statut as res_statut,
           c.nom as client_nom, ch.numero as chambre_num
    FROM paiements p
    JOIN reservations r ON p.reservation_id = r.id
    JOIN clients c ON r.client_id = c.id
    JOIN chambres ch ON r.chambre_id = ch.id
    ORDER BY p.id DESC
");

$total_paye     = $conn->query("SELECT SUM(montant) as t FROM paiements WHERE statut='paye'")->fetch_assoc()['t'] ?? 0;
$total_attente  = $conn->query("SELECT SUM(montant) as t FROM paiements WHERE statut='en_attente'")->fetch_assoc()['t'] ?? 0;
$nb_retard      = $conn->query("SELECT COUNT(*) as n FROM paiements p JOIN reservations r ON p.reservation_id=r.id WHERE p.statut='en_attente' AND r.statut='checkout'")->fetch_assoc()['n'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiements — IntelliHotel</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            /* Image de fond large, luxueuse et différente (hall d'hôtel avec touches dorées) */
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
            padding: 8px 16px;
            border-radius: 30px;
            transition: 0.2s;
        }
        .navbar a:hover { background: rgba(212, 175, 55, 0.2); }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px;
        }
        .page-header {
            margin-bottom: 30px;
        }
        .page-header h2 {
            color: #d4af37;
            font-size: 28px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        /* KPI Cards - style luxe */
        .kpi-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 40px;
        }
        .kpi {
            background: rgba(20, 20, 35, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(212, 175, 55, 0.4);
            border-radius: 28px;
            padding: 25px 20px;
            text-align: center;
            transition: 0.3s;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        .kpi:hover {
            transform: translateY(-5px);
            border-color: #d4af37;
            background: rgba(212, 175, 55, 0.1);
        }
        .kpi .icon { font-size: 40px; margin-bottom: 12px; }
        .kpi .value {
            font-size: 34px;
            font-weight: bold;
            color: #d4af37;
            text-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
        .kpi .label {
            font-size: 13px;
            color: rgba(255,255,255,0.8);
            margin-top: 8px;
            letter-spacing: 1px;
        }
        /* Tableau */
        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(15, 15, 25, 0.75);
            backdrop-filter: blur(8px);
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            border: 1px solid rgba(212, 175, 55, 0.2);
        }
        th {
            background: rgba(0, 0, 0, 0.6);
            color: #d4af37;
            padding: 16px 15px;
            text-align: left;
            font-size: 14px;
            font-weight: 600;
            border-bottom: 1px solid rgba(212, 175, 55, 0.4);
        }
        td {
            padding: 14px 15px;
            font-size: 13px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.9);
        }
        tr:hover td {
            background: rgba(212, 175, 55, 0.1);
        }
        /* Badges */
        .badge {
            padding: 5px 14px;
            border-radius: 40px;
            font-size: 11px;
            font-weight: bold;
            display: inline-block;
        }
        .badge-paye     { background: #1e7e34cc; color: #d4ffd4; border-left: 3px solid #28a745; }
        .badge-attente  { background: #856404cc; color: #fff3cd; border-left: 3px solid #ffc107; }
        .badge-retard   { background: #721c24cc; color: #ffc0c0; border-left: 3px solid #dc3545; }
        /* Boutons */
        .btn-payer {
            background: linear-gradient(135deg, #d4af37, #b8960c);
            color: #1a1a2e;
            padding: 7px 16px;
            border-radius: 40px;
            text-decoration: none;
            font-size: 12px;
            font-weight: bold;
            transition: 0.2s;
            display: inline-block;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .btn-payer:hover {
            transform: translateY(-2px);
            filter: brightness(1.05);
        }
        .btn-facture {
            background: linear-gradient(135deg, #17a2b8, #0f6c7a);
            color: white;
            padding: 7px 16px;
            border-radius: 40px;
            text-decoration: none;
            font-size: 12px;
            font-weight: bold;
            margin-left: 8px;
            transition: 0.2s;
        }
        .btn-facture:hover {
            transform: translateY(-2px);
        }
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 999;
            align-items: center;
            justify-content: center;
        }
        .modal.open { display: flex; }
        .modal-box {
            background: rgba(20, 20, 35, 0.95);
            backdrop-filter: blur(12px);
            padding: 35px;
            border-radius: 28px;
            width: 420px;
            text-align: center;
            border: 1px solid #d4af37;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }
        .modal-box h3 {
            color: #d4af37;
            margin-bottom: 25px;
            font-size: 22px;
        }
        .modal-box select {
            width: 100%;
            padding: 14px;
            border-radius: 40px;
            border: 1px solid rgba(212,175,55,0.5);
            background: rgba(255,255,255,0.9);
            margin-bottom: 25px;
            font-size: 14px;
            font-weight: bold;
        }
        .modal-btns {
            display: flex;
            gap: 15px;
        }
        .modal-btns button {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 40px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: 0.2s;
        }
        .btn-confirm {
            background: #d4af37;
            color: #1a1a2e;
        }
        .btn-confirm:hover { filter: brightness(1.05); transform: translateY(-2px); }
        .btn-cancel {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .btn-cancel:hover { background: rgba(255,255,255,0.3); }

        @media (max-width: 800px) {
            .container { padding: 15px; }
            .kpi-row { grid-template-columns: 1fr; gap: 15px; }
            th, td { padding: 10px 8px; font-size: 11px; }
            .btn-payer, .btn-facture { padding: 5px 10px; font-size: 10px; }
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
        <h2>💰 Gestion des Paiements</h2>
    </div>

    <!-- KPIs -->
    <div class="kpi-row">
        <div class="kpi">
            <div class="icon">✅</div>
            <div class="value"><?= number_format($total_paye, 0, ',', ' ') ?> MAD</div>
            <div class="label">Total Encaissé</div>
        </div>
        <div class="kpi">
            <div class="icon">⏳</div>
            <div class="value"><?= number_format($total_attente, 0, ',', ' ') ?> MAD</div>
            <div class="label">En Attente</div>
        </div>
        <div class="kpi">
            <div class="icon">🔴</div>
            <div class="value"><?= $nb_retard ?></div>
            <div class="label">Paiements en Retard</div>
        </div>
    </div>

    <!-- Tableau des paiements -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Client</th>
                <th>Chambre</th>
                <th>Dates Séjour</th>
                <th>Montant</th>
                <th>Méthode</th>
                <th>Statut</th>
                <th>Date Paiement</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if($paiements->num_rows === 0): ?>
            <tr><td colspan="9" style="text-align:center;padding:40px;color:rgba(255,255,255,0.6);">🏝️ Aucun paiement enregistré</td></tr>
        <?php else: ?>
            <?php while($p = $paiements->fetch_assoc()):
                $en_retard = ($p['statut'] === 'en_attente' && $p['res_statut'] === 'checkout');
            ?>
            <tr>
                <td><strong style="color:#d4af37;">#<?= $p['id'] ?></strong></td>
                <td>👤 <?= htmlspecialchars($p['client_nom']) ?></td>
                <td>🚪 N°<?= $p['chambre_num'] ?></td>
                <td><small><?= $p['date_debut'] ?> → <?= $p['date_fin'] ?></small></td>
                <td><strong style="color:#d4af37;"><?= number_format($p['montant'], 0) ?> MAD</strong></td>
                <td><?= $p['methode'] ?? '—' ?></td>
                <td>
                    <?php if($en_retard): ?>
                        <span class="badge badge-retard">🔴 En Retard</span>
                    <?php elseif($p['statut'] === 'paye'): ?>
                        <span class="badge badge-paye">✅ Payé</span>
                    <?php else: ?>
                        <span class="badge badge-attente">⏳ En Attente</span>
                    <?php endif; ?>
                </td>
                <td><?= $p['date_paiement'] ?? '—' ?></td>
                <td>
                    <?php if($p['statut'] !== 'paye'): ?>
                        <a href="#" class="btn-payer" onclick="ouvrirModal(<?= $p['id'] ?>); return false;">💳 Encaisser</a>
                    <?php endif; ?>
                    <a href="facture.php?id=<?= $p['reservation_id'] ?>" class="btn-facture">🖨️ Facture</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal choix méthode de paiement -->
<div class="modal" id="modal">
    <div class="modal-box">
        <h3>💎 Choisir la méthode de paiement</h3>
        <select id="methodeSelect">
            <option value="Especes">💵 Espèces</option>
            <option value="Carte bancaire">💳 Carte Bancaire</option>
            <option value="Virement">🏦 Virement</option>
        </select>
        <div class="modal-btns">
            <button class="btn-cancel" onclick="fermerModal()">Annuler</button>
            <button class="btn-confirm" onclick="confirmerPaiement()">✅ Confirmer</button>
        </div>
    </div>
</div>

<script>
let paiementId = null;
function ouvrirModal(id) {
    paiementId = id;
    document.getElementById('modal').classList.add('open');
}
function fermerModal() {
    document.getElementById('modal').classList.remove('open');
}
function confirmerPaiement() {
    const methode = encodeURIComponent(document.getElementById('methodeSelect').value);
    window.location.href = `index.php?payer=${paiementId}&methode=${methode}`;
}
</script>
</body>
</html>