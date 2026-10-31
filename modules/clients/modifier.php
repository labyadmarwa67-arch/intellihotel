<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../modules/auth/login.php");
    exit;
}
require_once '../../config/database.php';

$id = (int)$_GET['id'];
$client = $conn->query("SELECT * FROM clients WHERE id=$id")->fetch_assoc();

if (!$client) {
    header("Location: index.php");
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom       = $conn->real_escape_string($_POST['nom']);
    $cin       = $conn->real_escape_string($_POST['cin']);
    $telephone = $conn->real_escape_string($_POST['telephone']);
    $email     = $conn->real_escape_string($_POST['email']);

    $conn->query("UPDATE clients SET 
                  nom='$nom', cin='$cin',
                  telephone='$telephone', email='$email'
                  WHERE id=$id");
    $message = "✅ Client modifié avec succès !";
    $client = $conn->query("SELECT * FROM clients WHERE id=$id")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Client — IntelliHotel</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: Arial; background: #f0f2f5; }
        .navbar {
            background: #1a1a2e; color: white;
            padding: 15px 30px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .navbar h1 { font-size: 20px; }
        .navbar a { color: #ff6b6b; text-decoration: none; font-size: 14px; }
        .container { padding: 30px; max-width: 600px; margin: 0 auto; }
        h2 { color: #1a1a2e; margin-bottom: 25px; font-size: 22px; }
        .form-card {
            background: white; padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        label {
            display: block; font-size: 13px;
            color: #555; margin-bottom: 6px; font-weight: bold;
        }
        input {
            width: 100%; padding: 11px;
            border: 1px solid #ddd; border-radius: 8px;
            margin-bottom: 20px; font-size: 14px;
        }
        input:focus { outline: none; border-color: #1a1a2e; }
        .btn-submit {
            width: 100%; padding: 13px;
            background: #1a1a2e; color: white;
            border: none; border-radius: 8px;
            font-size: 15px; cursor: pointer;
        }
        .btn-submit:hover { background: #16213e; }
        .btn-back {
            display: inline-block; margin-bottom: 20px;
            color: #1a1a2e; text-decoration: none; font-size: 14px;
        }
        .success {
            background: #d4edda; border: 1px solid #c3e6cb;
            color: #155724; padding: 12px; border-radius: 8px;
            margin-bottom: 20px; text-align: center;
        }
    </style>
</head>
<body>
<div class="navbar">
    <h1>🏨 IntelliHotel</h1>
    <a href="../../index.php">🏠 Dashboard</a>
</div>

<div class="container">
    <a href="index.php" class="btn-back">← Retour aux clients</a>
    <h2>✏️ Modifier Client — <?= $client['nom'] ?></h2>

    <?php if($message): ?>
        <div class="success"><?= $message ?></div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST">
            <label>👤 Nom complet</label>
            <input type="text" name="nom"
                   value="<?= $client['nom'] ?>" required>

            <label>🪪 Numéro CIN</label>
            <input type="text" name="cin"
                   value="<?= $client['cin'] ?>" required>

            <label>📞 Téléphone</label>
            <input type="text" name="telephone"
                   value="<?= $client['telephone'] ?>">

            <label>📧 Email</label>
            <input type="email" name="email"
                   value="<?= $client['email'] ?>">

            <button type="submit" class="btn-submit">
                💾 Enregistrer les modifications
            </button>
        </form>
    </div>
</div>
</body>
</html>