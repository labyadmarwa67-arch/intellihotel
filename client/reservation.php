<?php

include '../config.php';

// vérifier chambre

if(!isset($_GET['id'])){

    die("Chambre introuvable");

}

$id = $_GET['id'];

// récupérer chambre

$sql = "SELECT * FROM chambres
WHERE id = $id";

$result = $conn->query($sql);

$chambre = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="fr">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Réservation IntelliHotel</title>

<!-- ICONS -->

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{

    font-family:Arial, sans-serif;

    background:
    linear-gradient(
    rgba(0,0,0,0.6),
    rgba(0,0,0,0.6)
    ),

    url('https://images.unsplash.com/photo-1566073771259-6a8506099945?q=80&w=1920');

    background-size:cover;
    background-position:center;

    min-height:100vh;

    display:flex;
    justify-content:center;
    align-items:center;

    padding:20px;

}

/* CONTAINER */

.container{

    width:1100px;

    background:white;

    border-radius:25px;

    overflow:hidden;

    display:grid;

    grid-template-columns:1fr 1fr;

    box-shadow:0 10px 40px rgba(0,0,0,0.3);

}

/* LEFT */

.left{

    position:relative;

    background:#000;

}

.left img{

    width:100%;
    height:100%;

    object-fit:cover;

}

.overlay{

    position:absolute;

    top:0;
    left:0;

    width:100%;
    height:100%;

    background:
    linear-gradient(
    rgba(0,0,0,0.4),
    rgba(0,0,0,0.6)
    );

    display:flex;
    flex-direction:column;

    justify-content:flex-end;

    padding:40px;

    color:white;

}

.overlay h1{

    font-size:45px;

    margin-bottom:15px;

}

.overlay p{

    line-height:1.8;

    font-size:18px;

}

/* PHONE */

.phone{

    margin-top:30px;

    width:220px;

    background:black;

    border-radius:35px;

    padding:12px;

    box-shadow:0 5px 20px rgba(0,0,0,0.5);

}

.screen{

    background:white;

    border-radius:25px;

    overflow:hidden;

}

.screen img{

    width:100%;
    height:180px;

    object-fit:cover;

}

.mobile-content{

    padding:15px;

}

.mobile-content h3{

    color:#2c3e50;
    margin-bottom:10px;

}

.mobile-content p{

    color:#555;
    font-size:14px;

}

/* RIGHT */

.right{

    padding:50px;

    overflow:auto;

}

.top{

    margin-bottom:30px;

}

.top h2{

    color:#2c3e50;

    font-size:38px;

    margin-bottom:10px;

}

.top p{

    color:#777;

}

/* INFO */

.info-box{

    background:#f4f6f9;

    padding:20px;

    border-radius:15px;

    margin-bottom:25px;

}

.info{

    margin-bottom:10px;

    color:#555;

    font-size:17px;

}

.price{

    color:#27ae60;

    font-size:30px;

    font-weight:bold;

    margin-top:15px;

}

/* ANALYSIS */

.analysis{

    background:#ecfdf5;

    padding:20px;

    border-left:5px solid #27ae60;

    border-radius:12px;

    margin-bottom:30px;

}

.analysis h3{

    color:#27ae60;

    margin-bottom:10px;

}

.analysis p{

    color:#555;

    line-height:1.8;

}

/* FORM */

form{

    margin-top:20px;

}

.group{

    margin-bottom:20px;

}

label{

    display:block;

    margin-bottom:8px;

    font-weight:bold;

    color:#34495e;

}

.input-box{

    position:relative;

}

.input-box i{

    position:absolute;

    top:16px;
    left:15px;

    color:#3498db;

}

input{

    width:100%;

    padding:15px 15px 15px 45px;

    border:1px solid #ccc;

    border-radius:10px;

    font-size:16px;

    transition:0.3s;

}

input:focus{

    border-color:#3498db;

    outline:none;

}

/* BUTTON */

button{

    width:100%;

    background:#3498db;

    color:white;

    border:none;

    padding:18px;

    border-radius:12px;

    font-size:18px;

    cursor:pointer;

    transition:0.3s;

}

button:hover{

    background:#2980b9;

    transform:translateY(-2px);

}

/* MOBILE */

@media(max-width:950px){

.container{

    grid-template-columns:1fr;

}

.left{

    height:500px;

}

.phone{

    width:180px;

}

}

</style>

</head>

<body>

<div class="container">

<!-- LEFT -->

<div class="left">

<?php

$image = "";

// image chambre

if(
$chambre['type'] == "Single"
&&
$chambre['vue'] == "mer"
){

$image =
"https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?q=80&w=1200";

}

elseif(
$chambre['type'] == "Double"
&&
$chambre['vue'] == "piscine"
){

$image =
"https://images.unsplash.com/photo-1522798514-97ceb8c4f1c8?q=80&w=1200";

}

elseif(
$chambre['type'] == "Suite"
&&
$chambre['vue'] == "mer"
){

$image =
"https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?q=80&w=1200";

}

else{

$image =
"https://images.unsplash.com/photo-1566073771259-6a8506099945?q=80&w=1200";

}

?>

<img src="<?php echo $image; ?>">

<div class="overlay">

<h1>

🏨 IntelliHotel

</h1>

<p>

Réservez votre chambre directement
depuis votre téléphone avec une
expérience moderne et intelligente.

</p>

<!-- PHONE -->

<div class="phone">

<div class="screen">

<img src="<?php echo $image; ?>">

<div class="mobile-content">

<h3>

📱 Mobile Booking

</h3>

<p>

Réservation rapide et sécurisée
depuis smartphone.

</p>

</div>

</div>

</div>

</div>

</div>

<!-- RIGHT -->

<div class="right">

<div class="top">

<h2>

Réserver Maintenant

</h2>

<p>

Complétez vos informations
pour confirmer votre réservation.

</p>

</div>

<!-- INFO -->

<div class="info-box">

<p class="info">

🛏️ Type :
<b><?php echo $chambre['type']; ?></b>

</p>

<p class="info">

🌅 Vue :
<b><?php echo $chambre['vue']; ?></b>

</p>

<p class="info">

🏨 Chambre :
<b><?php echo $chambre['numero']; ?></b>

</p>

<p class="price">

<?php echo $chambre['prix_base']; ?>
DH / Nuit

</p>

</div>

<!-- ANALYSIS -->

<div class="analysis">

<h3>

📊 Analyse des Données

</h3>

<p>

Chaque réservation est automatiquement
stockée afin d’analyser les revenus,
les périodes de forte demande et
les préférences des clients.

</p>

</div>

<!-- FORM -->

<form action="save_reservation.php"
method="POST">

<input type="hidden"
name="chambre_id"
value="<?php echo $chambre['id']; ?>">

<div class="group">

<label>
Nom complet
</label>

<div class="input-box">

<i class="fa fa-user"></i>

<input type="text"
name="nom"
required>

</div>

</div>

<div class="group">

<label>
CIN
</label>

<div class="input-box">

<i class="fa fa-id-card"></i>

<input type="text"
name="cin"
required>

</div>

</div>

<div class="group">

<label>
Téléphone
</label>

<div class="input-box">

<i class="fa fa-phone"></i>

<input type="text"
name="telephone"
required>

</div>

</div>

<div class="group">

<label>
Email
</label>

<div class="input-box">

<i class="fa fa-envelope"></i>

<input type="email"
name="email"
required>

</div>

</div>

<div class="group">

<label>
Date début
</label>

<div class="input-box">

<i class="fa fa-calendar"></i>

<input type="date"
name="date_debut"
required>

</div>

</div>

<div class="group">

<label>
Date fin
</label>

<div class="input-box">

<i class="fa fa-calendar"></i>

<input type="date"
name="date_fin"
required>

</div>

</div>

<button type="submit">

✨ Confirmer Réservation

</button>

</form>

</div>

</div>

</body>
</html>