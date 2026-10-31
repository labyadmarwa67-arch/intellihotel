<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
require_once '../../config/database.php';

$id   = (int)$_GET['id'];
$user = $conn->query("SELECT * FROM utilisateurs WHERE id=$id")->fetch_assoc();
if (!$user) { header("Location: index.php"); exit; }

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom   = $conn->real_escape_string($_POST['nom']);
    $email = $conn->real_escape_string($_POST['email']);
    $role  = $conn->real_escape_string($_POST['role']);

    if (!empty($_POST['mot_de_passe'])) {
        $mdp = MD5($_POST['mot_de_passe']);
        $conn->query("UPDATE utilisateurs SET nom='$nom',email='$email',role='$role',mot_de_passe='$mdp' WHERE id=$id");
    } else {
        $conn->query("UPDATE utilisateurs SET nom='$nom',email='$email',role='$role' WHERE id=$id");
    }
    $message = "success|✅ Utilisateur modifié avec succès !";
    $user = $conn->query("SELECT * FROM utilisateurs WHERE id=$id")->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Modifier Utilisateur — IntelliHotel</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:Arial;background:#07071a;color:white;display:flex;}
.sidebar{width:240px;min-height:100vh;position:fixed;background:linear-gradient(180deg,#0d0d1f,#1a1a2e);border-right:1px solid rgba(240,192,64,0.1);z-index:100;}
.logo{padding:20px;text-align:center;border-bottom:1px solid rgba(255,255,255,0.07);}
.logo .ico{font-size:36px;}
.logo h2{color:#f0c040;font-size:14px;letter-spacing:3px;margin-top:6px;}
.logo .stars{color:#f0c040;font-size:11px;margin-top:4px;}
.ns{padding:12px 18px 4px;color:rgba(255,255,255,0.25);font-size:10px;text-transform:uppercase;letter-spacing:2px;}
.sidebar a{display:flex;align-items:center;gap:10px;color:rgba(255,255,255,0.55);text-decoration:none;padding:11px 18px;font-size:13px;border-left:3px solid transparent;transition:0.2s;}
.sidebar a:hover,.sidebar a.active{background:rgba(240,192,64,0.07);color:#f0c040;border-left-color:#f0c040;}
.sidebar a i{width:16px;text-align:center;}
.sf{position:absolute;bottom:0;width:100%;padding:14px 18px;border-top:1px solid rgba(255,255,255,0.07);}
.sf a{color:rgba(255,255,255,0.35);font-size:12px;display:flex;align-items:center;gap:8px;}
.main{margin-left:240px;flex:1;padding:22px;}
.topbar{display:flex;justify-content:space-between;align-items:center;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:14px;padding:14px 22px;margin-bottom:22px;}
.topbar h1{font-size:17px;}
.btn-back{display:flex;align-items:center;gap:8px;color:#f0c040;text-decoration:none;font-size:13px;background:rgba(240,192,64,0.1);border:1px solid rgba(240,192,64,0.2);padding:9px 16px;border-radius:10px;transition:0.2s;}
.btn-back:hover{background:rgba(240,192,64,0.2);}
.form-wrap{max-width:600px;margin:0 auto;}

/* User Info Box */
.user-info-box{
    display:flex;align-items:center;gap:16px;
    background:rgba(255,255,255,0.03);
    border:1px solid rgba(255,255,255,0.07);
    border-radius:14px;padding:20px;margin-bottom:20px;
}
.uav{
    width:60px;height:60px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    font-weight:bold;font-size:24px;flex-shrink:0;
}
.uav.admin{background:linear-gradient(135deg,#dc3545,#a71d2a);}
.uav.receptionniste{background:linear-gradient(135deg,#17a2b8,#0c5460);}
.uav.technicien{background:linear-gradient(135deg,#28a745,#155724);}
.uinfo-name{color:white;font-size:17px;font-weight:bold;}
.uinfo-role{font-size:11px;padding:3px 10px;border-radius:20px;display:inline-block;margin-top:5px;}
.uinfo-role.admin{background:rgba(220,53,69,0.2);color:#ff6b6b;}
.uinfo-role.receptionniste{background:rgba(23,162,184,0.2);color:#17a2b8;}
.uinfo-role.technicien{background:rgba(40,167,69,0.2);color:#28a745;}

.form-card{background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:16px;padding:30px;}
.sec-title{color:#f0c040;font-size:14px;font-weight:bold;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid rgba(240,192,64,0.2);display:flex;align-items:center;gap:8px;}
label{display:block;font-size:12px;color:rgba(255,255,255,0.5);margin-bottom:6px;font-weight:bold;text-transform:uppercase;letter-spacing:0.5px;}
input,select{width:100%;padding:12px 16px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:10px;color:white;font-size:14px;margin-bottom:18px;transition:0.2s;}
input:focus,select:focus{outline:none;border-color:#f0c040;background:rgba(255,255,255,0.08);}
input::placeholder{color:rgba(255,255,255,0.3);}
select option{background:#1a1a2e;color:white;}

.role-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:20px;}
.role-card{border-radius:12px;padding:16px;text-align:center;cursor:pointer;transition:0.2s;border:2px solid transparent;}
.role-card.admin{background:rgba(220,53,69,0.1);}
.role-card.receptionniste{background:rgba(23,162,184,0.1);}
.role-card.technicien{background:rgba(40,167,69,0.1);}
.role-card input[type=radio]{display:none;}
.role-card.selected.admin{border-color:#dc3545;background:rgba(220,53,69,0.2);}
.role-card.selected.receptionniste{border-color:#17a2b8;background:rgba(23,162,184,0.2);}
.role-card.selected.technicien{border-color:#28a745;background:rgba(40,167,69,0.2);}
.role-ico{font-size:28px;margin-bottom:8px;}
.role-name{font-size:12px;font-weight:bold;color:white;}
.role-desc{font-size:10px;color:rgba(255,255,255,0.4);margin-top:3px;}

.mdp-note{background:rgba(240,192,64,0.08);border:1px solid rgba(240,192,64,0.2);border-radius:10px;padding:12px;margin-bottom:18px;font-size:12px;color:rgba(255,255,255,0.5);}
.row2{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.btn-submit{width:100%;padding:14px;background:linear-gradient(135deg,#f0c040,#e0a020);color:#1a1a2e;border:none;border-radius:12px;font-size:15px;font-weight:bold;cursor:pointer;transition:0.3s;}
.btn-submit:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(240,192,64,0.3);}
.success{background:rgba(40,167,69,0.15);border:1px solid rgba(40,167,69,0.3);color:#a8f0b8;padding:14px;border-radius:12px;margin-bottom:20px;text-align:center;}
.erreur{background:rgba(220,53,69,0.15);border:1px solid rgba(220,53,69,0.3);color:#f8b4bc;padding:14px;border-radius:12px;margin-bottom:20px;text-align:center;}
</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="logo">
        <div class="ico">🏨</div>
        <h2>INTELLI HOTEL</h2>
        <div class="stars">★ ★ ★ ★ ★</div>
    </div>
    <div class="ns">Principal</div>
    <a href="../../index.php"><i class="fa fa-home"></i> Dashboard</a>
    <a href="../../admin/dashboard.php"><i class="fa fa-chart-line"></i> Admin Analytics</a>
    <div class="ns">Gestion</div>
    <a href="../chambres/index.php"><i class="fa fa-bed"></i> Chambres</a>
    <a href="../clients/index.php"><i class="fa fa-users"></i> Clients</a>
    <a href="../reservations/index.php"><i class="fa fa-calendar"></i> Réservations</a>
    <a href="../paiements/index.php"><i class="fa fa-credit-card"></i> Paiements</a>
    <a href="../maintenance/index.php"><i class="fa fa-wrench"></i> Maintenance</a>
    <div class="ns">Équipe</div>
    <a href="index.php" class="active"><i class="fa fa-user-tie"></i> Utilisateurs</a>
    <div class="sf">
        <a href="../auth/logout.php"><i class="fa fa-sign-out-alt"></i> Déconnexion</a>
    </div>
</div>

<!-- MAIN -->
<div class="main">
    <div class="topbar">
        <h1>✏️ Modifier Utilisateur</h1>
        <a href="index.php" class="btn-back"><i class="fa fa-arrow-left"></i> Retour</a>
    </div>

    <div class="form-wrap">

        <?php if($message):
            [$type,$texte]=explode('|',$message);
        ?>
        <div class="<?= $type ?>"><?= $texte ?></div>
        <?php endif; ?>

        <!-- User Info -->
        <div class="user-info-box">
            <div class="uav <?= $user['role'] ?>"><?= strtoupper(substr($user['nom'],0,1)) ?></div>
            <div>
                <div class="uinfo-name"><?= $user['nom'] ?></div>
                <div><?= $user['email'] ?></div>
                <?php
                $icons=['admin'=>'🛡️ Admin','receptionniste'=>'🎫 Réceptionniste','technicien'=>'🔧 Technicien'];
                ?>
                <div class="uinfo-role <?= $user['role'] ?>"><?= $icons[$user['role']] ?></div>
            </div>
        </div>

        <div class="form-card">
            <form method="POST">

                <div class="sec-title"><i class="fa fa-user"></i> Informations</div>
                <div class="row2">
                    <div>
                        <label>👤 Nom Complet</label>
                        <input type="text" name="nom" value="<?= $user['nom'] ?>" required>
                    </div>
                    <div>
                        <label>📧 Email</label>
                        <input type="email" name="email" value="<?= $user['email'] ?>" required>
                    </div>
                </div>

                <div class="sec-title"><i class="fa fa-lock"></i> Mot de Passe</div>
                <div class="mdp-note">
                    💡 Laissez vide pour garder le mot de passe actuel
                </div>
                <label>🔒 Nouveau Mot de Passe</label>
                <input type="password" name="mot_de_passe" placeholder="Laisser vide = pas de changement">

                <div class="sec-title"><i class="fa fa-shield-alt"></i> Rôle</div>
                <div class="role-grid">
                    <label class="role-card admin <?= $user['role']=='admin'?'selected':'' ?>" onclick="selectRole(this,'admin')">
                        <input type="radio" name="role" value="admin" <?= $user['role']=='admin'?'checked':'' ?>>
                        <div class="role-ico">🛡️</div>
                        <div class="role-name">Admin</div>
                        <div class="role-desc">Accès complet</div>
                    </label>
                    <label class="role-card receptionniste <?= $user['role']=='receptionniste'?'selected':'' ?>" onclick="selectRole(this,'receptionniste')">
                        <input type="radio" name="role" value="receptionniste" <?= $user['role']=='receptionniste'?'checked':'' ?>>
                        <div class="role-ico">🎫</div>
                        <div class="role-name">Réceptionniste</div>
                        <div class="role-desc">Réservations</div>
                    </label>
                    <label class="role-card technicien <?= $user['role']=='technicien'?'selected':'' ?>" onclick="selectRole(this,'technicien')">
                        <input type="radio" name="role" value="technicien" <?= $user['role']=='technicien'?'checked':'' ?>>
                        <div class="role-ico">🔧</div>
                        <div class="role-name">Technicien</div>
                        <div class="role-desc">Maintenance</div>
                    </label>
                </div>

                <button type="submit" class="btn-submit">
                    💾 ENREGISTRER LES MODIFICATIONS
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function selectRole(el,role){
    document.querySelectorAll('.role-card').forEach(c=>c.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input[type=radio]').checked=true;
}
</script>
</body>
</html>