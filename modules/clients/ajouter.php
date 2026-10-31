<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../modules/auth/login.php");
    exit;
}
require_once '../../config/database.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom       = $conn->real_escape_string($_POST['nom']);
    $cin       = $conn->real_escape_string($_POST['cin']);
    $telephone = $conn->real_escape_string($_POST['telephone']);
    $email     = $conn->real_escape_string($_POST['email']);

    // Vérifier CIN unique
    $check = $conn->query("SELECT id FROM clients WHERE cin='$cin'");
    if ($check->num_rows > 0) {
        $message = "❌ Ce CIN existe déjà !";
    } else {
        $conn->query("INSERT INTO clients (nom, cin, telephone, email)
                      VALUES ('$nom', '$cin', '$telephone', '$email')");
        $message = "✅ Client ajouté avec succès !";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter Client — IntelliHotel</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            /* Image de fond large et nette (vue luxueuse d'hôtel) */
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
        .navbar a {
            color: #ffb347;
            text-decoration: none;
            font-size: 14px;
            transition: 0.2s;
        }
        .navbar a:hover { color: #ff9f1c; text-decoration: underline; }
        .container {
            padding: 30px;
            max-width: 700px;
            margin: 0 auto;
        }
        .btn-back {
            display: inline-block;
            margin-bottom: 20px;
            color: white;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
            padding: 8px 18px;
            border-radius: 40px;
            text-decoration: none;
            font-size: 14px;
            transition: 0.2s;
        }
        .btn-back:hover { background: rgba(0,0,0,0.7); }
        h2 {
            color: white;
            font-size: 28px;
            margin-bottom: 25px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .form-card {
            background: rgba(255, 255, 255, 0.93);
            backdrop-filter: blur(4px);
            padding: 35px;
            border-radius: 28px;
            box-shadow: 0 20px 35px rgba(0,0,0,0.2);
        }
        label {
            display: block;
            font-size: 13px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 6px;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 16px;
            margin-bottom: 22px;
            font-size: 14px;
            background: white;
            transition: 0.2s;
        }
        input:focus {
            outline: none;
            border-color: #1e3c72;
            box-shadow: 0 0 0 3px rgba(30,60,114,0.2);
        }
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
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
            box-shadow: 0 8px 22px rgba(0,0,0,0.25);
            background: linear-gradient(135deg, #2a5298 0%, #1e3c72 100%);
        }
        .success {
            background: #d4edda;
            border-left: 5px solid #28a745;
            color: #155724;
            padding: 14px;
            border-radius: 16px;
            margin-bottom: 25px;
            font-weight: bold;
        }
        .erreur {
            background: #f8d7da;
            border-left: 5px solid #dc3545;
            color: #721c24;
            padding: 14px;
            border-radius: 16px;
            margin-bottom: 25px;
            font-weight: bold;
        }
        @media (max-width: 600px) {
            .container { padding: 20px; }
            .form-card { padding: 20px; }
            h2 { font-size: 24px; }
        }
    </style>
</head>
<body>
<div class="navbar">
    <h1>🏨 IntelliHotel</h1>
    <a href="../../index.php">🏠 Tableau de bord</a>
</div>

<div class="container">
    <a href="index.php" class="btn-back">← Retour aux clients</a>
    <h2>👤 Ajouter un client</h2>

    <?php if($message): ?>
        <div class="<?= str_starts_with($message,'✅') ? 'success' : 'erreur' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST">
            <label>👤 Nom complet</label>
            <input type="text" name="nom"
                   placeholder="Ex: Ahmed Benali" required>

            <label>🪪 Numéro CIN</label>
            <input type="text" name="cin"
                   placeholder="Ex: AB123456" required>

            <label>📞 Téléphone</label>
            <input type="text" name="telephone"
                   placeholder="Ex: 0612345678" required>

            <label>📧 Email</label>
            <input type="email" name="email"
                   placeholder="Ex: ahmed@email.com">

            <button type="submit" class="btn-submit">
                ✅ Ajouter le client
            </button>
        </form>
    </div>
</div>
</body>
</html>