<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../modules/auth/login.php");
    exit;
}
require_once '../../config/database.php';

$res_id = (int)$_GET['id'];
$data = $conn->query("
    SELECT r.*, 
           c.nom as client_nom, c.cin, c.telephone, c.email,
           ch.numero as chambre_num, ch.type, ch.vue, ch.prix_base,
           p.montant, p.methode, p.statut as paiement_statut, p.date_paiement,
           u.nom as receptionniste
    FROM reservations r
    JOIN clients c ON r.client_id = c.id
    JOIN chambres ch ON r.chambre_id = ch.id
    LEFT JOIN paiements p ON p.reservation_id = r.id
    LEFT JOIN utilisateurs u ON r.receptionniste_id = u.id
    WHERE r.id = $res_id
")->fetch_assoc();

if (!$data) { die("Réservation introuvable"); }

$nuits = (strtotime($data['date_fin']) - strtotime($data['date_debut'])) / 86400;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture #<?= $res_id ?> — IntelliHotel</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: Arial; background:#f0f2f5; padding:30px; }

        .facture {
            max-width: 750px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
        }

        /* Header */
        .facture-header {
            background: linear-gradient(135deg, #1a1a2e, #0f3460);
            color: white;
            padding: 35px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .hotel-info h1 { font-size: 26px; color: #f0c040; letter-spacing: 2px; }
        .hotel-info p  { color: rgba(255,255,255,0.7); font-size: 13px; margin-top: 5px; }
        .stars { color: #f0c040; font-size: 16px; letter-spacing: 3px; }

        .facture-num { text-align: right; }
        .facture-num .num { font-size: 28px; font-weight: bold; color: #f0c040; }
        .facture-num .date { color: rgba(255,255,255,0.6); font-size: 13px; margin-top: 5px; }

        /* Body */
        .facture-body { padding: 35px 40px; }

        /* Parties */
        .parties { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px; }
        .partie h3 { font-size: 12px; text-transform: uppercase; color: #888; letter-spacing: 1px; margin-bottom: 12px; }
        .partie p  { font-size: 14px; color: #333; line-height: 1.7; }
        .partie strong { color: #1a1a2e; }

        /* Détails séjour */
        .sejour-box {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 12px;
            padding: 20px 25px;
            margin-bottom: 25px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            text-align: center;
        }
        .sejour-item .label { font-size: 11px; color: #888; text-transform: uppercase; margin-bottom: 5px; }
        .sejour-item .value { font-size: 15px; font-weight: bold; color: #1a1a2e; }

        /* Table */
        .facture-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .facture-table th {
            background: #1a1a2e; color: #f0c040;
            padding: 12px 15px; text-align: left; font-size: 13px;
        }
        .facture-table td {
            padding: 13px 15px; font-size: 13px; color: #333;
            border-bottom: 1px solid #f0f0f0;
        }
        .facture-table tr:last-child td { border-bottom: none; }

        /* Total */
        .total-box {
            background: linear-gradient(135deg, #f0c040, #e0a020);
            border-radius: 12px;
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        .total-box .label { font-size: 16px; font-weight: bold; color: #1a1a2e; }
        .total-box .montant { font-size: 30px; font-weight: bold; color: #1a1a2e; }

        /* Statut Paiement */
        .statut-paiement {
            text-align: center;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-weight: bold;
            font-size: 16px;
        }
        .statut-paye   { background: #d4edda; color: #155724; border: 2px solid #c3e6cb; }
        .statut-attente{ background: #fff3cd; color: #856404; border: 2px solid #ffeeba; }

        /* Footer */
        .facture-footer {
            background: #f8f9fa;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 2px solid #f0c040;
        }
        .facture-footer p { font-size: 12px; color: #888; }
        .facture-footer strong { color: #1a1a2e; }

        /* Print button */
        .print-bar {
            max-width: 750px; margin: 0 auto 20px;
            display: flex; gap: 10px;
        }
        .btn-print {
            background: linear-gradient(135deg, #1a1a2e, #0f3460);
            color: white; padding: 12px 25px;
            border-radius: 8px; border: none;
            font-size: 14px; cursor: pointer;
            font-weight: bold;
        }
        .btn-back {
            background: #dee2e6; color: #495057;
            padding: 12px 25px; border-radius: 8px;
            text-decoration: none; font-size: 14px;
            font-weight: bold;
        }

        @media print {
            body { background: white; padding: 0; }
            .print-bar { display: none; }
            .facture { box-shadow: none; border-radius: 0; }
        }
    </style>
</head>
<body>

<div class="print-bar">
    <button class="btn-print" onclick="window.print()">🖨️ Imprimer / PDF</button>
    <a href="index.php" class="btn-back">← Retour</a>
</div>

<div class="facture">

    <!-- Header -->
    <div class="facture-header">
        <div class="hotel-info">
            <div class="stars">★ ★ ★ ★ ★</div>
            <h1>🏨 INTELLI HOTEL</h1>
            <p>Système Intelligent de Gestion Hôtelière</p>
            <p>Marrakech, Maroc — contact@intellihotel.ma</p>
        </div>
        <div class="facture-num">
            <div style="color:rgba(255,255,255,0.6);font-size:13px;">FACTURE</div>
            <div class="num">#<?= str_pad($res_id, 5, '0', STR_PAD_LEFT) ?></div>
            <div class="date">Émise le : <?= date('d/m/Y') ?></div>
        </div>
    </div>

    <!-- Body -->
    <div class="facture-body">

        <!-- Parties -->
        <div class="parties">
            <div class="partie">
                <h3>👤 Client</h3>
                <p>
                    <strong><?= $data['client_nom'] ?></strong><br>
                    CIN : <?= $data['cin'] ?><br>
                    Tél : <?= $data['telephone'] ?><br>
                    Email : <?= $data['email'] ?: '—' ?>
                </p>
            </div>
            <div class="partie">
                <h3>🏨 Hôtel</h3>
                <p>
                    <strong>IntelliHotel ★★★★★</strong><br>
                    Réceptionniste : <?= $data['receptionniste'] ?><br>
                    Chambre N° : <?= $data['chambre_num'] ?><br>
                    Type : <?= $data['type'] ?> / Vue <?= $data['vue'] ?>
                </p>
            </div>
        </div>

        <!-- Détails Séjour -->
        <div class="sejour-box">
            <div class="sejour-item">
                <div class="label">📅 Arrivée</div>
                <div class="value"><?= date('d/m/Y', strtotime($data['date_debut'])) ?></div>
            </div>
            <div class="sejour-item">
                <div class="label">📅 Départ</div>
                <div class="value"><?= date('d/m/Y', strtotime($data['date_fin'])) ?></div>
            </div>
            <div class="sejour-item">
                <div class="label">🌙 Nuits</div>
                <div class="value"><?= $nuits ?> nuit(s)</div>
            </div>
            <div class="sejour-item">
                <div class="label">💰 Prix/Nuit</div>
                <div class="value"><?= $data['prix_base'] ?> MAD</div>
            </div>
        </div>

        <!-- Table Détail -->
        <table class="facture-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Quantité</th>
                    <th>Prix Unitaire</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        Chambre N°<?= $data['chambre_num'] ?> — <?= $data['type'] ?> (Vue <?= $data['vue'] ?>)<br>
                        <small style="color:#888">Du <?= date('d/m/Y', strtotime($data['date_debut'])) ?> au <?= date('d/m/Y', strtotime($data['date_fin'])) ?></small>
                    </td>
                    <td><?= $nuits ?> nuit(s)</td>
                    <td><?= $data['prix_base'] ?> MAD</td>
                    <td><strong><?= number_format($data['montant'], 2) ?> MAD</strong></td>
                </tr>
            </tbody>
        </table>

        <!-- Total -->
        <div class="total-box">
            <div class="label">💰 TOTAL À PAYER</div>
            <div class="montant"><?= number_format($data['montant'], 2) ?> MAD</div>
        </div>

        <!-- Statut Paiement -->
        <div class="statut-paiement <?= $data['paiement_statut'] === 'paye' ? 'statut-paye' : 'statut-attente' ?>">
            <?php if($data['paiement_statut'] === 'paye'): ?>
                ✅ PAYÉ le <?= date('d/m/Y', strtotime($data['date_paiement'])) ?> — <?= $data['methode'] ?>
            <?php else: ?>
                ⏳ EN ATTENTE DE PAIEMENT
            <?php endif; ?>
        </div>

    </div>

    <!-- Footer -->
    <div class="facture-footer">
        <p>Merci pour votre confiance — <strong>IntelliHotel ★★★★★</strong></p>
        <p>Document généré le <?= date('d/m/Y à H:i') ?></p>
    </div>

</div>

</body>
</html>