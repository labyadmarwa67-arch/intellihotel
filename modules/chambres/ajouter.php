<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../modules/auth/login.php");
    exit;
}
require_once '../../config/database.php';

// دالة جلب معامل السيزون
function getSaisonCoefficient($conn, $date) {
    $mois = (int)date('m', strtotime($date));
    
    $saison = $conn->query("SELECT coefficient FROM saisons 
        WHERE (mois_debut <= $mois AND mois_fin >= $mois)
        OR (mois_debut > mois_fin AND ($mois >= mois_debut OR $mois <= mois_fin))
        LIMIT 1")->fetch_assoc();
    
    return $saison ? $saison['coefficient'] : 1.0;
}

$message = '';
$prix_suggere = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero    = (int)$_POST['numero'];
    $type      = $conn->real_escape_string($_POST['type']);
    $vue       = $conn->real_escape_string($_POST['vue']);
    $prix_base = (float)$_POST['prix_base'];
    $etat      = $conn->real_escape_string($_POST['etat']);

    // Vérifier si le numéro de chambre existe déjà
    $check = $conn->query("SELECT id FROM chambres WHERE numero=$numero");
    if ($check->num_rows > 0) {
        $message = "❌ Ce numéro de chambre existe déjà !";
    } else {
        $conn->query("INSERT INTO chambres (numero, type, vue, prix_base, etat)
                      VALUES ($numero, '$type', '$vue', $prix_base, '$etat')");
        $message = "✅ Chambre ajoutée avec succès !";
    }
}

// جلب جميع السيزونات لعرضها في القائمة
$saisons = $conn->query("SELECT * FROM saisons ORDER BY id");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter Chambre — IntelliHotel</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: url('https://images.pexels.com/photos/258154/pexels-photo-258154.jpeg?auto=compress&cs=tinysrgb&w=1920&h=1080&dpr=2') no-repeat center center fixed;
            background-size: cover;
            position: relative;
        }
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
        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 16px;
            margin-bottom: 22px;
            font-size: 14px;
            background: white;
            transition: 0.2s;
        }
        input:focus, select:focus {
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
        .prix-info {
            background: rgba(0,86,179,0.12);
            padding: 12px 18px;
            border-radius: 20px;
            font-size: 13px;
            color: #00448d;
            margin-bottom: 25px;
            border: 1px solid rgba(0,86,179,0.2);
            backdrop-filter: blur(2px);
        }
        .prix-suggere {
            background: #e8f4fd;
            border-radius: 16px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #1e3c72;
        }
        .prix-suggere span {
            font-size: 24px;
            font-weight: bold;
            color: #1e3c72;
        }
        .detail-calcul {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
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
    <a href="index.php" class="btn-back">← Retour aux chambres</a>
    <h2>🚪 Ajouter une chambre</h2>

    <?php if($message): ?>
        <div class="<?= str_starts_with($message,'✅') ? 'success' : 'erreur' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <div class="prix-info">
            💡 Prix calculé automatiquement selon : <strong>Type × Vue × Saison</strong>
        </div>

        <!-- ✅ عرض السعر المقترح -->
        <div class="prix-suggere">
            💰 Prix suggéré : <span id="prix_suggere">---</span> MAD/nuit
            <div class="detail-calcul" id="detail_calcul"></div>
        </div>

        <form method="POST" id="chambreForm">
            <label>🔢 Numéro de chambre</label>
            <input type="number" name="numero" id="numero"
                   placeholder="Ex: 101" required min="1">

            <label>🛏️ Type de chambre</label>
            <select name="type" id="type" required>
                <option value="">-- Choisir --</option>
                <option value="Single" data-prix="500">🛏️ Single (1 personne)</option>
                <option value="Double" data-prix="800">🛏️🛏️ Double (1-2 personnes)</option>
                <option value="Suite" data-prix="1500">👑 Suite (1-4 personnes)</option>
            </select>

            <label>🌅 Vue</label>
            <select name="vue" id="vue" required>
                <option value="">-- Choisir --</option>
                <option value="Mer" data-coef="1.30">🌊 Vue sur Mer (+30%)</option>
                <option value="Jardin" data-coef="1.00">🌿 Vue sur Jardin (+0%)</option>
                <option value="Piscine" data-coef="1.15">🏊 Vue sur Piscine (+15%)</option>
                <option value="Ville" data-coef="1.10">🏙️ Vue sur Ville (+10%)</option>
            </select>

            <label>📅 Saison</label>
            <select name="saison" id="saison" required>
                <option value="1.00">-- Choisir une saison --</option>
                <?php while($s = $saisons->fetch_assoc()): ?>
                    <option value="<?= $s['coefficient'] ?>" 
                            data-mois-debut="<?= $s['mois_debut'] ?>" 
                            data-mois-fin="<?= $s['mois_fin'] ?>">
                        🗓️ <?= $s['nom'] ?> (x<?= $s['coefficient'] ?>)
                    </option>
                <?php endwhile; ?>
                <option value="1.20">🎄 Haute Saison (Juin-Août) x1.20</option>
                <option value="1.00">🍂 Moyenne Saison (Avril-Mai, Sept-Oct) x1.00</option>
                <option value="0.80">❄️ Basse Saison (Nov-Mars) x0.80</option>
            </select>

            <label>💰 Prix de base (MAD/nuit)</label>
            <input type="number" name="prix_base" id="prix_base"
                   placeholder="Ex: 500" required min="1" step="0.01">

            <label>📊 État initial</label>
            <select name="etat" required>
                <option value="vide">🟢 Disponible</option>
                <option value="reservee">🔴 Réservée</option>
                <option value="occupee">🟡 Occupée</option>
            </select>

            <button type="submit" class="btn-submit">
                ✅ Ajouter la chambre
            </button>
        </form>
    </div>
</div>

<script>
// الأسعار الأساسية حسب النوع
const prixTypes = {
    'Single': 500,
    'Double': 800,
    'Suite': 1500
};

// معاملات الإطلالة
const coefVues = {
    'Mer': 1.30,
    'Jardin': 1.00,
    'Piscine': 1.15,
    'Ville': 1.10
};

// معاملات السيزون (Saison)
const coefSaisons = {
    '1.20': 1.20,  // Haute saison
    '1.00': 1.00,  // Moyenne saison
    '0.80': 0.80   // Basse saison
};

function calculerPrix() {
    // جلب العناصر
    let typeSelect = document.getElementById('type');
    let vueSelect = document.getElementById('vue');
    let saisonSelect = document.getElementById('saison');
    let prixInput = document.getElementById('prix_base');
    let prixSuggere = document.getElementById('prix_suggere');
    let detailCalcul = document.getElementById('detail_calcul');
    
    let type = typeSelect.value;
    let vue = vueSelect.value;
    let saison = saisonSelect.value;
    
    let prixBase = 0;
    let prixApresVue = 0;
    let prixFinal = 0;
    
    // 1. حساب السعر حسب النوع
    if (type && prixTypes[type]) {
        prixBase = prixTypes[type];
        
        // 2. تطبيق معامل الإطلالة
        if (vue && coefVues[vue]) {
            prixApresVue = prixBase * coefVues[vue];
        } else {
            prixApresVue = prixBase;
        }
        
        // 3. تطبيق معامل السيزون
        let coefSaison = 1.00;
        if (saison && !isNaN(parseFloat(saison))) {
            coefSaison = parseFloat(saison);
        }
        
        prixFinal = Math.round(prixApresVue * coefSaison);
        
        // تحديث حقل السعر
        prixInput.value = prixFinal;
        
        // تحديث النص المعروض
        if (prixSuggere) {
            prixSuggere.innerHTML = prixFinal;
        }
        
        // عرض تفاصيل الحساب
        if (detailCalcul) {
            detailCalcul.innerHTML = `${prixBase} (Type) × ${coefVues[vue] || 1} (Vue) × ${coefSaison} (Saison) = ${prixFinal} MAD`;
        }
        
        // تغيير لون الخلفية
        prixInput.style.backgroundColor = "#d4edda";
        
        console.log(`Prix calculé: ${prixFinal} (${prixBase} × ${coefVues[vue] || 1} × ${coefSaison})`);
    } else {
        prixInput.value = "";
        if (prixSuggere) prixSuggere.innerHTML = "---";
        if (detailCalcul) detailCalcul.innerHTML = "";
        prixInput.style.backgroundColor = "white";
    }
}

// ربط الأحداث عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    let typeSelect = document.getElementById('type');
    let vueSelect = document.getElementById('vue');
    let saisonSelect = document.getElementById('saison');
    
    if (typeSelect) typeSelect.addEventListener('change', calculerPrix);
    if (vueSelect) vueSelect.addEventListener('change', calculerPrix);
    if (saisonSelect) saisonSelect.addEventListener('change', calculerPrix);
    
    console.log("Script chargé - événements prêts (Type × Vue × Saison)");
});
</script>

</body>
</html>