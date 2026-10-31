<?php

include '../config.php';

// récupérer chambres

$sql = "SELECT * FROM chambres";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="fr">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Nos Chambres - IntelliHotel</title>

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

    background:#eef2f7;

}

/* HEADER */

header{

    background:#1e293b;

    color:white;

    padding:20px 60px;

    display:flex;

    justify-content:space-between;

    align-items:center;

    position:sticky;

    top:0;

    z-index:1000;

}

.logo{

    font-size:30px;

    font-weight:bold;

}

nav a{

    color:white;

    text-decoration:none;

    margin-left:25px;

    transition:0.3s;

    font-size:17px;

}

nav a:hover{

    color:#38bdf8;

}

/* HERO */

.hero{

    height:450px;

    background:
    linear-gradient(
    rgba(0,0,0,0.5),
    rgba(0,0,0,0.6)
    ),

    url('https://images.unsplash.com/photo-1566073771259-6a8506099945?q=80&w=1920');

    background-size:cover;

    background-position:center;

    display:flex;

    justify-content:center;

    align-items:center;

    text-align:center;

    color:white;

}

.hero-content h1{

    font-size:65px;

    margin-bottom:20px;

}

.hero-content p{

    font-size:22px;

    line-height:1.8;

}

/* SECTION */

.section{

    width:92%;

    margin:60px auto;

}

/* ANALYSIS */

.analysis{

    background:white;

    padding:30px;

    border-radius:20px;

    margin-bottom:50px;

    box-shadow:0 5px 20px rgba(0,0,0,0.08);

}

.analysis h2{

    color:#0f172a;

    margin-bottom:15px;

}

.analysis p{

    color:#555;

    line-height:2;

}

/* GRID */

.grid{

    display:grid;

    grid-template-columns:
    repeat(auto-fit,minmax(340px,1fr));

    gap:35px;

}

/* CARD */

.card{

    background:white;

    border-radius:22px;

    overflow:hidden;

    box-shadow:0 8px 25px rgba(0,0,0,0.08);

    transition:0.4s;

    border:1px solid #eee;

}

.card:hover{

    transform:translateY(-10px);

}

.card img{

    width:100%;

    height:270px;

    object-fit:cover;

    transition:0.5s;

}

.card:hover img{

    transform:scale(1.08);

}

/* CONTENT */

.content{

    padding:28px;

}

.content h2{

    color:#0f172a;

    margin-bottom:18px;

    font-size:28px;

}

.info{

    margin-bottom:12px;

    color:#555;

    font-size:17px;

}

.price{

    margin-top:20px;

    color:#16a34a;

    font-size:34px;

    font-weight:bold;

}

/* STATUS */

.status{

    display:inline-block;

    margin-top:18px;

    padding:10px 18px;

    border-radius:30px;

    color:white;

    font-size:14px;

}

.vide{

    background:#16a34a;

}

.reservee{

    background:#f59e0b;

}

.occupee{

    background:#ef4444;

}

/* BUTTON */

.btn{

    display:block;

    margin-top:25px;

    text-align:center;

    background:#0ea5e9;

    color:white;

    text-decoration:none;

    padding:15px;

    border-radius:12px;

    font-size:18px;

    transition:0.3s;

}

.btn:hover{

    background:#0284c7;

}

/* FOOTER */

footer{

    background:#1e293b;

    color:white;

    text-align:center;

    padding:30px;

    margin-top:60px;

}

/* MOBILE */

@media(max-width:768px){

header{

    flex-direction:column;

}

nav{

    margin-top:15px;

}

.hero-content h1{

    font-size:40px;

}

.hero-content p{

    font-size:18px;

}

}

</style>

</head>

<body>

<!-- HEADER -->

<header>

<div class="logo">

🏨 IntelliHotel

</div>

<nav>

<a href="#">Accueil</a>

<a href="#">Chambres</a>

<a href="#">Réservations</a>

<a href="#">Contact</a>

</nav>

</header>

<!-- HERO -->

<section class="hero">

<div class="hero-content">

<h1>

Luxury Hotel Experience

</h1>

<p>

Découvrez des chambres élégantes,
modernes et adaptées à tous les besoins.

</p>

</div>

</section>

<!-- SECTION -->

<div class="section">

<!-- ANALYSIS -->

<div class="analysis">

<h2>

📊 Analyse et Traitement des Données

</h2>

<p>

Le système IntelliHotel exploite les données
des réservations afin d’analyser les revenus,
les chambres les plus demandées,
les préférences des clients
et les périodes de forte activité.

</p>

</div>

<!-- GRID -->

<div class="grid">

<?php while($row = $result->fetch_assoc()) { ?>

<div class="card">

<?php

$image = "";

// SINGLE

if($row['type'] == "Single"){

$image =
"https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?q=80&w=1200";

}

// DOUBLE

elseif($row['type'] == "Double"){

$image =
"https://images.unsplash.com/photo-1522798514-97ceb8c4f1c8?q=80&w=1200";

}

// SUITE

elseif($row['type'] == "Suite"){

$image =
"https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?q=80&w=1200";

}

// FAMILIALE

elseif($row['type'] == "Familiale"){

$image =
"https://images.unsplash.com/photo-1590490360182-c33d57733427?q=80&w=1200";

}

// DELUXE

elseif($row['type'] == "Deluxe"){

$image =
"https://images.unsplash.com/photo-1618773928121-c32242e63f39?q=80&w=1200";

}

// DEFAULT

else{

$image =
"https://images.unsplash.com/photo-1566073771259-6a8506099945?q=80&w=1200";

}

?>

<img src="<?php echo $image; ?>">

<div class="content">

<h2>

🛏️ Chambre
<?php echo $row['numero']; ?>

</h2>

<p class="info">

<i class="fa fa-bed"></i>

Type :
<b><?php echo $row['type']; ?></b>

</p>

<p class="info">

<i class="fa fa-eye"></i>

Vue :
<b><?php echo $row['vue']; ?></b>

</p>

<p class="info">

<i class="fa fa-hotel"></i>

Etat :
<b><?php echo $row['etat']; ?></b>

</p>

<p class="price">

<?php echo $row['prix_base']; ?>
DH / Nuit

</p>

<?php

$statusClass = "";

if($row['etat'] == "vide"){

$statusClass = "vide";

}

elseif($row['etat'] == "reservee"){

$statusClass = "reservee";

}

else{

$statusClass = "occupee";

}

?>

<span class="status <?php echo $statusClass; ?>">

<?php echo $row['etat']; ?>

</span>

<a href="reservation.php?id=<?php echo $row['id']; ?>"
class="btn">

✨ Réserver Maintenant

</a>

</div>

</div>

<?php } ?>

</div>

</div>

<!-- FOOTER -->

<footer>

© 2026 IntelliHotel |
Smart Hotel Management & Data Analytics

</footer>

</body>
</html>