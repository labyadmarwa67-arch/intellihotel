<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../modules/auth/login.php");
    exit;
}
require_once '../../config/database.php';

// Supprimer une chambre
if (isset($_GET['supprimer'])) {
    $id = (int)$_GET['supprimer'];
    $conn->query("DELETE FROM chambres WHERE id=$id");
    header("Location: index.php");
    exit;
}

$chambres = $conn->query("SELECT * FROM chambres ORDER BY numero");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Chambres — IntelliHotel</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            /* Image de fond large et nette (libre de droits Pexels) */
            background: url('https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&dpr=2') no-repeat center center fixed;
            background-size: cover;
            position: relative;
        }
        /* Overlay semi-transparent pour la lisibilité */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: -1;
        }
        .navbar {
            background: rgba(26, 26, 46, 0.92);
            backdrop-filter: blur(8px);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        .navbar h1 { font-size: 24px; letter-spacing: 1px; }
        .navbar a { color: #ffb347; text-decoration: none; font-size: 14px; transition: 0.2s; }
        .navbar a:hover { color: #ff9f1c; text-decoration: underline; }
        .container {
            padding: 30px;
            max-width: 1300px;
            margin: 0 auto;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(4px);
            padding: 15px 25px;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        .page-header h2 {
            color: #1a1a2e;
            font-size: 26px;
            font-weight: 600;
            text-shadow: 1px 1px 0 rgba(255,255,255,0.5);
        }
        .btn-primary {
            background: #1e3c72;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 10px 22px;
            border-radius: 40px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(0,0,0,0.2);
            background: linear-gradient(135deg, #2a5298 0%, #1e3c72 100%);
        }
        table {
            width: 100%;
            background: rgba(255, 255, 255, 0.93);
            backdrop-filter: blur(2px);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            border-collapse: collapse;
        }
        th {
            background: #0f172a;
            color: white;
            padding: 16px 20px;
            text-align: left;
            font-size: 15px;
            font-weight: 600;
        }
        td {
            padding: 14px 20px;
            font-size: 14px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            color: #1e293b;
        }
        tr:hover td {
            background: rgba(241, 245, 249, 0.9);
        }
        .etat {
            padding: 5px 14px;
            border-radius: 40px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .etat-vide { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .etat-reservee { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .etat-occupee { background: #fff3cd; color: #856404; border-left: 4px solid #ffc107; }
        .btn-edit {
            background: #0d9488;
            color: white;
            padding: 6px 14px;
            border-radius: 30px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
            transition: 0.2s;
            margin-right: 6px;
        }
        .btn-edit:hover { background: #0f766e; transform: scale(1.02); }
        .btn-delete {
            background: #dc2626;
            color: white;
            padding: 6px 14px;
            border-radius: 30px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
            transition: 0.2s;
            display: inline-block;
        }
        .btn-delete:hover { background: #b91c1c; transform: scale(1.02); }
        @media (max-width: 768px) {
            .container { padding: 15px; }
            th, td { padding: 10px 12px; }
            .page-header { flex-direction: column; gap: 12px; text-align: center; }
        }
    </style>
</head>
<body>
<div class="navbar">
    <h1>🏨 IntelliHotel</h1>
    <a href="../../modules/auth/logout.php">🚪 Déconnexion</a>
</div>

<div class="container">
    <div class="page-header">
        <h2>🚪 Gestion des chambres</h2>
        <a href="ajouter.php" class="btn-primary">+ Ajouter une chambre</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>N° Chambre</th>
                <th>Type</th>
                <th>Vue</th>
                <th>Prix de base</th>
                <th>État</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if($chambres->num_rows === 0): ?>
            <tr>
                <td colspan="6" style="text-align:center; color:#555; padding:40px; font-size:16px;">
                    🏝️ Aucune chambre enregistrée — Ajoutez-en une !
                </td>
            </tr>
        <?php else: ?>
            <?php while($ch = $chambres->fetch_assoc()): ?>
            <tr>
                <td><strong style="font-size:16px;"><?= htmlspecialchars($ch['numero']) ?></strong></td>
                <td><?= htmlspecialchars($ch['type']) ?></td>
                <td><?= htmlspecialchars($ch['vue']) ?></td>
                <td><?= number_format($ch['prix_base'], 0) ?> MAD</td>
                <td>
                    <?php
                    $icons  = ['vide'=>'🟢','reservee'=>'🔴','occupee'=>'🟡'];
                    $labels = ['vide'=>'Disponible','reservee'=>'Réservée','occupee'=>'Occupée'];
                    ?>
                    <span class="etat etat-<?= $ch['etat'] ?>">
                        <?= $icons[$ch['etat']] ?> <?= $labels[$ch['etat']] ?>
                    </span>
                </td>
                <td>
                    <a href="modifier.php?id=<?= $ch['id'] ?>" class="btn-edit">✏️ Modifier</a>
                    <a href="index.php?supprimer=<?= $ch['id'] ?>"
                       class="btn-delete"
                       onclick="return confirm('Supprimer cette chambre ?')">🗑️ Supprimer</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>