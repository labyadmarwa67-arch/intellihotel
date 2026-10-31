<?php
session_start();
require_once '../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $mdp   = MD5($_POST['mot_de_passe']);
    $result = $conn->query("SELECT * FROM utilisateurs 
                            WHERE email='$email' AND mot_de_passe='$mdp'");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_nom']  = $user['nom'];
        $_SESSION['user_role'] = $user['role'];
        header("Location: ../../index.php");
        exit;
    } else {
        $erreur = "Email ou mot de passe incorrect !";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>IntelliHotel — Connexion</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: Arial;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            /* خلفية فندق فاخر */
            background-image: url('https://images.unsplash.com/photo-1566073771259-6a8506099945?w=1600');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        /* طبقة داكنة فوق الصورة */
        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.55);
            z-index: 0;
        }

        .login-wrapper {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* عنوان فوق الكارد */
        .hotel-title {
            text-align: center;
            color: white;
            margin-bottom: 25px;
        }
        .hotel-title h1 {
            font-size: 42px;
            font-weight: bold;
            letter-spacing: 3px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.5);
        }
        .hotel-title p {
            font-size: 15px;
            color: rgba(255,255,255,0.8);
            margin-top: 5px;
            letter-spacing: 1px;
        }

        /* الكارد */
        .card {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255,255,255,0.25);
            padding: 40px;
            border-radius: 20px;
            width: 400px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.4);
        }

        .card h2 {
            text-align: center;
            color: white;
            font-size: 22px;
            margin-bottom: 8px;
        }
        .card .sub {
            text-align: center;
            color: rgba(255,255,255,0.7);
            font-size: 13px;
            margin-bottom: 30px;
        }

        /* الخط الذهبي */
        .divider {
            width: 50px; height: 2px;
            background: linear-gradient(90deg, #f0c040, #e0a020);
            margin: 0 auto 25px;
            border-radius: 2px;
        }

        label {
            display: block;
            color: rgba(255,255,255,0.85);
            font-size: 13px;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 13px 15px;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 10px;
            color: white;
            font-size: 14px;
            margin-bottom: 20px;
            transition: 0.3s;
        }
        input::placeholder { color: rgba(255,255,255,0.5); }
        input:focus {
            outline: none;
            border-color: #f0c040;
            background: rgba(255,255,255,0.2);
        }

        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #f0c040, #e0a020);
            color: #1a1a2e;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            letter-spacing: 1px;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(240,192,64,0.4);
        }

        .erreur {
            background: rgba(220,53,69,0.3);
            border: 1px solid rgba(220,53,69,0.5);
            color: white;
            padding: 12px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: center;
        }

        /* معلومات تسجيل الدخول */
        .hint {
            margin-top: 20px;
            background: rgba(255,255,255,0.08);
            border-radius: 10px;
            padding: 12px;
            font-size: 12px;
            color: rgba(255,255,255,0.6);
            text-align: center;
        }
        .hint strong { color: #f0c040; }

        /* النجوم */
        .stars {
            color: #f0c040;
            font-size: 18px;
            text-align: center;
            margin-bottom: 5px;
            letter-spacing: 3px;
        }
    </style>
</head>
<body>

<div class="login-wrapper">

    <!-- عنوان الفندق -->
    <div class="hotel-title">
        <div class="stars">★ ★ ★ ★ ★</div>
        <h1>🏨 INTELLI HOTEL</h1>
        <p>Système Intelligent de Gestion Hôtelière</p>
    </div>

    <!-- كارد تسجيل الدخول -->
    <div class="card">
        <h2>Connexion</h2>
        <p class="sub">Accès réservé au personnel</p>
        <div class="divider"></div>

        <?php if(isset($erreur)): ?>
            <div class="erreur">⚠️ <?= $erreur ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>📧 Email</label>
            <input type="email" name="email"
                   placeholder="votre@email.com" required>

            <label>🔒 Mot de passe</label>
            <input type="password" name="mot_de_passe"
                   placeholder="••••••••" required>

            <button type="submit">SE CONNECTER →</button>
        </form>

        <div class="hint">
            👤 Admin : <strong>admin@intellihotel.ma</strong><br>
            🔑 Mot de passe : <strong>admin123</strong>
        </div>
    </div>

</div>

</body>
</html>