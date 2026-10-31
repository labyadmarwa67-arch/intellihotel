<?php

include '../config.php';

// récupération données

$nom = $_POST['nom'];

$cin = $_POST['cin'];

$telephone = $_POST['telephone'];

$email = $_POST['email'];

$date_debut = $_POST['date_debut'];

$date_fin = $_POST['date_fin'];

$chambre_id = $_POST['chambre_id'];


// vérifier client existe

$checkClient = "SELECT * FROM clients
WHERE cin='$cin'";

$resultClient = $conn->query($checkClient);


// client déjà existe

if($resultClient->num_rows > 0){

    $client = $resultClient->fetch_assoc();

    $client_id = $client['id'];

}else{

// insertion nouveau client

$sqlClient = "INSERT INTO clients

(nom, cin, telephone, email)

VALUES

('$nom',
'$cin',
'$telephone',
'$email')";

$conn->query($sqlClient);

$client_id = $conn->insert_id;

}


// récupérer chambre

$sqlChambre = "SELECT * FROM chambres
WHERE id='$chambre_id'";

$resChambre = $conn->query($sqlChambre);

$chambre = $resChambre->fetch_assoc();

$prix = $chambre['prix_base'];


// calcul durée séjour

$debut = strtotime($date_debut);

$fin = strtotime($date_fin);

$jours = ($fin - $debut) / (60 * 60 * 24);


// sécurité

if($jours <= 0){

    die("Dates invalides");

}


// calcul prix total

$prix_total = $jours * $prix;


// insertion réservation

$sqlReservation = "INSERT INTO reservations

(client_id,
chambre_id,
date_debut,
date_fin,
statut,
prix_total)

VALUES

('$client_id',
'$chambre_id',
'$date_debut',
'$date_fin',
'confirmée',
'$prix_total')";

$conn->query($sqlReservation);


// update état chambre

$update = "UPDATE chambres

SET etat='reservee'

WHERE id='$chambre_id'";

$conn->query($update);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Réservation Confirmée</title>

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

.card{

    width:700px;

    background:rgba(255,255,255,0.97);

    border-radius:25px;

    overflow:hidden;

    box-shadow:0 10px 40px rgba(0,0,0,0.3);

    animation:fadeIn 1s ease;

}

@keyframes fadeIn{

from{

    opacity:0;
    transform:translateY(30px);

}

to{

    opacity:1;
    transform:translateY(0);

}

}

/* HEADER */

.top{

    background:#27ae60;

    color:white;

    text-align:center;

    padding:40px;

}

.top .icon{

    font-size:90px;
    margin-bottom:15px;

}

.top h1{

    font-size:40px;
    margin-bottom:10px;

}

.top p{

    font-size:18px;

}

/* CONTENT */

.content{

    padding:40px;

}

.grid{

    display:grid;

    grid-template-columns:1fr 1fr;

    gap:20px;

    margin-bottom:30px;

}

.box{

    background:#f8f9fa;

    padding:20px;

    border-radius:15px;

}

.box h3{

    color:#2c3e50;
    margin-bottom:10px;

}

.box p{

    color:#555;
    line-height:1.8;

}

.total{

    background:#ecfdf5;

    padding:25px;

    border-radius:20px;

    text-align:center;

    margin-bottom:30px;

}

.total h2{

    color:#27ae60;
    margin-bottom:10px;

}

.total p{

    font-size:40px;

    color:#2c3e50;

    font-weight:bold;

}

/* ANALYSIS */

.analysis{

    background:#eef6ff;

    border-left:6px solid #3498db;

    padding:25px;

    border-radius:15px;

}

.analysis h2{

    color:#3498db;
    margin-bottom:15px;

}

.analysis p{

    color:#555;
    line-height:2;

}

/* BUTTON */

.btn{

    display:block;

    width:100%;

    margin-top:30px;

    text-align:center;

    background:#3498db;

    color:white;

    padding:16px;

    border-radius:12px;

    text-decoration:none;

    font-size:18px;

    transition:0.3s;

}

.btn:hover{

    background:#2980b9;

}

/* MOBILE */

@media(max-width:768px){

.grid{

    grid-template-columns:1fr;

}

.top h1{

    font-size:30px;

}

.total p{

    font-size:30px;

}

}

</style>

</head>

<body>

<div class="card">

<!-- HEADER -->

<div class="top">

<div class="icon">

🎉

</div>

<h1>

Réservation Confirmée

</h1>

<p>

Merci d’avoir choisi IntelliHotel

</p>

</div>

<!-- CONTENT -->

<div class="content">

<div class="grid">

<div class="box">

<h3>
👤 Informations Client
</h3>

<p>

<b>Nom :</b>
<?php echo $nom; ?>

<br><br>

<b>CIN :</b>
<?php echo $cin; ?>

<br><br>

<b>Téléphone :</b>
<?php echo $telephone; ?>

</p>

</div>

<div class="box">

<h3>
🏨 Informations Chambre
</h3>

<p>

<b>Numéro :</b>
<?php echo $chambre['numero']; ?>

<br><br>

<b>Type :</b>
<?php echo $chambre['type']; ?>

<br><br>

<b>Vue :</b>
<?php echo $chambre['vue']; ?>

</p>

</div>

</div>

<!-- TOTAL -->

<div class="total">

<h2>
💰 Montant Total
</h2>

<p>

<?php echo $prix_total; ?> DH

</p>

</div>

<!-- DETAILS -->

<div class="grid">

<div class="box">

<h3>
📅 Séjour
</h3>

<p>

<b>Date début :</b>
<?php echo $date_debut; ?>

<br><br>

<b>Date fin :</b>
<?php echo $date_fin; ?>

<br><br>

<b>Nombre nuits :</b>
<?php echo $jours; ?>

</p>

</div>

<div class="box">

<h3>
📌 Statut
</h3>

<p>

✅ Réservation confirmée

<br><br>

🏨 Chambre réservée

<br><br>

📊 Données enregistrées

</p>

</div>

</div>

<!-- ANALYSIS -->

<div class="analysis">

<h2>
📊 Analyse et Traitement des Données
</h2>

<p>

Les informations de réservation sont automatiquement
stockées dans la base de données afin de produire :

<br><br>

✔️ Des statistiques sur les réservations  
<br>

✔️ L’analyse des revenus hôteliers  
<br>

✔️ Le taux d’occupation des chambres  
<br>

✔️ Les préférences des clients  
<br>

✔️ Des indicateurs d’aide à la décision

</p>

</div>

<a href="chambres.php"
class="btn">

⬅ Retour aux chambres

</a>

</div>

</div>

</body>
</html>
  