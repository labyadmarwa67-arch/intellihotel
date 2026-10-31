<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../modules/auth/login.php");
    exit;
}
require_once '../../config/database.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $chambre_id    = (int)$_POST['chambre_id'];
    $technicien_id = (int)$_POST['technicien_id'];
    $description   = $conn->real_escape_string($_POST['description']);
    $date_debut    = $_POST['date_debut'];

    $conn->query("INSERT INTO maintenance (chambre_id, technicien_id, description, statut, date_debut)
                  VALUES ($chambre_id, $technicien_id, '$description', 'en_cours', '$date_debut')");

    // Mettre à jour l'état de la chambre
    $conn->query("UPDATE chambres SET etat='reservee' WHERE id=$chambre_id");

    $message = "success|✅ Intervention créée avec succès !";
}

$chambres     = $conn->query("SELECT * FROM chambres ORDER BY numero");
$techniciens  = $conn->query("SELECT * FROM utilisateurs WHERE role='technicien' ORDER BY nom");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouvelle Intervention — IntelliHotel</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            /* Image de fond large, moderne et élégante (coin détente/couloir hôtel luxueux) */
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
            max-width: 750px;
            margin: 0 auto;
            padding: 30px;
        }
        .btn-back {
            display: inline-block;
            margin-bottom: 20px;
            color: #d4af37;
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(4px);
            padding: 8px 20px;
            border-radius: 40px;
            text-decoration: none;
            font-size: 14px;
            transition: 0.2s;
        }
        .btn-back:hover {
            background: rgba(212, 175, 55, 0.3);
            color: white;
        }
        h2 {
            color: #d4af37;
            font-size: 28px;
            margin-bottom: 25px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-card {
            background: rgba(20, 20, 35, 0.75);
            backdrop-filter: blur(10px);
            padding: 35px;
            border-radius: 28px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            border: 1px solid rgba(212, 175, 55, 0.3);
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #d4af37;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid rgba(212, 175, 55, 0.4);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        label {
            display: block;
            font-size: 13px;
            font-weight: bold;
            color: rgba(255,255,255,0.9);
            margin-bottom: 6px;
        }
        select, input, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 20px;
            margin-bottom: 20px;
            font-size: 14px;
            background: rgba(255,255,255,0.9);
            transition: 0.2s;
        }
        select:focus, input:focus, textarea:focus {
            outline: none;
            border-color: #d4af37;
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.3);
        }
        textarea {
            height: 100px;
            resize: vertical;
        }
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #d4af37, #b8960c);
            color: #1a1a2e;
            border: none;
            border-radius: 40px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(212, 175, 55, 0.4);
            filter: brightness(1.05);
        }
        .success {
            background: #1e7e34cc;
            border-left: 5px solid #28a745;
            color: white;
            padding: 14px;
            border-radius: 16px;
            margin-bottom: 20px;
            font-weight: bold;
            backdrop-filter: blur(4px);
        }
        .erreur {
            background: #721c24cc;
            border-left: 5px solid #dc3545;
            color: white;
            padding: 14px;
            border-radius: 16px;
            margin-bottom: 20px;
            font-weight: bold;
            backdrop-filter: blur(4px);
        }
        @media (max-width: 650px) {
            .container { padding: 20px; }
            .form-card { padding: 20px; }
            h2 { font-size: 24px; }
        }
    </style>
</head>
<body>
<div class="navbar">
    <h1>🏨 <span>IntelliHotel</span></h1>
    <a href="../../index.php">🏠 Tableau de bord</a>
</div>

<div class="container">
    <a href="index.php" class="btn-back">← Retour</a>
    <h2>🔧 Nouvelle Intervention</h2>

    <?php if($message):
        [$type, $texte] = explode('|', $message);
    ?>
        <div class="<?= $type ?>"><?= $texte ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST">
            <div class="section-title">🚪 Chambre concernée</div>
            <label>Sélectionner la chambre</label>
            <select name="chambre_id" required>
                <option value="">-- Choisir une chambre --</option>
                <?php while($ch = $chambres->fetch_assoc()): ?>
                    <option value="<?= $ch['id'] ?>">
                        🚪 N°<?= $ch['numero'] ?> — <?= htmlspecialchars($ch['type']) ?> / <?= htmlspecialchars($ch['vue']) ?>
                        (<?= $ch['etat'] ?>)
                    </option>
                <?php endwhile; ?>
            </select>

            <div class="section-title">👷 Technicien assigné</div>
            <label>Choisir le technicien</label>
            <select name="technicien_id" required>
                <option value="">-- Choisir un technicien --</option>
                <?php while($t = $techniciens->fetch_assoc()): ?>
                    <option value="<?= $t['id'] ?>">🔧 <?= htmlspecialchars($t['nom']) ?></option>
                <?php endwhile; ?>
            </select>

            <div class="section-title">📝 Description du problème</div>
            <label>Détails de l'intervention</label>
            <textarea name="description" placeholder="Ex: Réparation climatisation, fuite d'eau, électricité..." required></textarea>

            <label>📅 Date de début</label>
            <input type="date" name="date_debut" value="<?= date('Y-m-d') ?>" required>

            <button type="submit" class="btn-submit">
                🔧 CRÉER L'INTERVENTION
            </button>
        </form>
    </div>
</div>
</body>
</html>