<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../modules/auth/login.php");
    exit;
}
require_once '../../config/database.php';

// Terminer une intervention
if (isset($_GET['terminer'])) {
    $id = (int)$_GET['terminer'];
    $conn->query("UPDATE maintenance SET statut='terminee', date_fin=CURDATE() WHERE id=$id");
    // Libérer la chambre
    $m = $conn->query("SELECT chambre_id FROM maintenance WHERE id=$id")->fetch_assoc();
    $conn->query("UPDATE chambres SET etat='vide' WHERE id={$m['chambre_id']}");
    header("Location: index.php");
    exit;
}

// Supprimer
if (isset($_GET['supprimer'])) {
    $id = (int)$_GET['supprimer'];
    $conn->query("DELETE FROM maintenance WHERE id=$id");
    header("Location: index.php");
    exit;
}

$maintenances = $conn->query("
    SELECT m.*, ch.numero as chambre_num, ch.type, u.nom as technicien_nom
    FROM maintenance m
    JOIN chambres ch ON m.chambre_id = ch.id
    JOIN utilisateurs u ON m.technicien_id = u.id
    ORDER BY m.id DESC
");

$nb_encours   = $conn->query("SELECT COUNT(*) as n FROM maintenance WHERE statut='en_cours'")->fetch_assoc()['n'];
$nb_terminee  = $conn->query("SELECT COUNT(*) as n FROM maintenance WHERE statut='terminee'")->fetch_assoc()['n'];
$nb_prolongee = $conn->query("SELECT COUNT(*) as n FROM maintenance WHERE statut='prolongee'")->fetch_assoc()['n'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Maintenance — IntelliHotel</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            /* Image de fond large, moderne et élégante (couloir d'hôtel luxueux) */
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
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        .btn-primary {
            background: linear-gradient(135deg, #d4af37, #b8960c);
            color: #1a1a2e;
            padding: 10px 24px;
            border-radius: 40px;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            transition: 0.3s;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            filter: brightness(1.05);
        }

        /* KPIs style luxe */
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
        }

        /* Tableau glassmorphism */
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
        small {
            color: rgba(255,255,255,0.6);
        }
        /* Badges */
        .badge {
            padding: 5px 14px;
            border-radius: 40px;
            font-size: 11px;
            font-weight: bold;
            display: inline-block;
        }
        .badge-encours   { background: #856404cc; color: #fff3cd; border-left: 3px solid #ffc107; }
        .badge-terminee  { background: #1e7e34cc; color: #d4ffd4; border-left: 3px solid #28a745; }
        .badge-prolongee { background: #721c24cc; color: #ffc0c0; border-left: 3px solid #dc3545; }

        /* Durée en alerte */
        .duree-warning { color: #ffaa66; font-weight: bold; }

        /* Boutons d'action */
        .btn-terminer, .btn-modifier, .btn-supprimer {
            padding: 6px 14px;
            border-radius: 30px;
            text-decoration: none;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
            margin-right: 6px;
            transition: 0.2s;
        }
        .btn-terminer {
            background: linear-gradient(135deg, #28a745, #1e7e34);
            color: white;
        }
        .btn-modifier {
            background: linear-gradient(135deg, #17a2b8, #117a8b);
            color: white;
        }
        .btn-supprimer {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }
        .btn-terminer:hover, .btn-modifier:hover, .btn-supprimer:hover {
            transform: translateY(-2px);
            filter: brightness(1.05);
        }

        @media (max-width: 900px) {
            .container { padding: 15px; }
            .kpi-row { grid-template-columns: 1fr; gap: 15px; }
            th, td { padding: 10px 8px; font-size: 11px; }
            .btn-terminer, .btn-modifier, .btn-supprimer { padding: 4px 10px; font-size: 10px; margin-bottom: 4px; }
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
        <h2>🔧 Gestion de la Maintenance</h2>
        <a href="ajouter.php" class="btn-primary">+ Nouvelle intervention</a>
    </div>

    <!-- KPIs -->
    <div class="kpi-row">
        <div class="kpi">
            <div class="icon">⚙️</div>
            <div class="value"><?= $nb_encours ?></div>
            <div class="label">En Cours</div>
        </div>
        <div class="kpi">
            <div class="icon">✅</div>
            <div class="value"><?= $nb_terminee ?></div>
            <div class="label">Terminées</div>
        </div>
        <div class="kpi">
            <div class="icon">⚠️</div>
            <div class="value"><?= $nb_prolongee ?></div>
            <div class="label">Prolongées</div>
        </div>
    </div>

    <!-- Table des interventions -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Chambre</th>
                <th>Technicien</th>
                <th>Description</th>
                <th>Date Début</th>
                <th>Date Fin</th>
                <th>Durée</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if($maintenances->num_rows === 0): ?>
            <tr>
                <td colspan="9" style="text-align:center; padding:40px; color:rgba(255,255,255,0.6);">
                    🏝️ Aucune intervention enregistrée
                 </td>
            </tr>
        <?php else: ?>
            <?php while($m = $maintenances->fetch_assoc()):
                $debut = strtotime($m['date_debut']);
                $fin   = $m['date_fin'] ? strtotime($m['date_fin']) : time();
                $duree = ceil(($fin - $debut) / 86400);
                $warning = ($m['statut'] === 'en_cours' && $duree > 2);
            ?>
            <tr>
                <td><strong style="color:#d4af37;">#<?= $m['id'] ?></strong></td>
                <td>🚪 N°<?= $m['chambre_num'] ?><br><small><?= htmlspecialchars($m['type']) ?></small></td>
                <td>🔧 <?= htmlspecialchars($m['technicien_nom']) ?></td>
                <td><?= htmlspecialchars($m['description']) ?></td>
                <td><?= $m['date_debut'] ?></td>
                <td><?= $m['date_fin'] ?? '<span style="color:rgba(255,255,255,0.6)">En cours</span>' ?></td>
                <td class="<?= $warning ? 'duree-warning' : '' ?>">
                    <?= $duree ?> jour(s) <?= $warning ? '⚠️' : '' ?>
                 </td>
                <td>
                    <?php
                    $badges = ['en_cours'=>'badge-encours','terminee'=>'badge-terminee','prolongee'=>'badge-prolongee'];
                    $labels = ['en_cours'=>'⚙️ En Cours','terminee'=>'✅ Terminée','prolongee'=>'⚠️ Prolongée'];
                    ?>
                    <span class="badge <?= $badges[$m['statut']] ?>">
                        <?= $labels[$m['statut']] ?>
                    </span>
                 </td>
                <td>
                    <?php if($m['statut'] !== 'terminee'): ?>
                        <a href="?terminer=<?= $m['id'] ?>" class="btn-terminer"
                           onclick="return confirm('Marquer cette intervention comme terminée ?')">✅ Terminer</a>
                    <?php endif; ?>
                    <a href="modifier.php?id=<?= $m['id'] ?>" class="btn-modifier">✏️ Modifier</a>
                    <a href="?supprimer=<?= $m['id'] ?>" class="btn-supprimer"
                       onclick="return confirm('Supprimer définitivement cette intervention ?')">🗑️ Supprimer</a>
                 </td>
             </tr>
            <?php endwhile; ?>
        <?php endif; ?>
        </tbody>
     </table>
</div>
</body>
</html>