<!DOCTYPE html>
<html lang="fr">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>IntelliHotel</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{

    font-family:Arial, sans-serif;
    background:#f4f6f9;

}

/* HEADER */

header{

    background:#2c3e50;
    color:white;
    padding:20px 50px;

    display:flex;
    justify-content:space-between;
    align-items:center;

}

.logo{

    font-size:28px;
    font-weight:bold;

}

nav a{

    color:white;
    text-decoration:none;
    margin-left:20px;
    font-size:16px;

}

/* HERO */

.hero{

    height:90vh;

    background:
    linear-gradient(
    rgba(0,0,0,0.5),
    rgba(0,0,0,0.5)
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

    font-size:60px;
    margin-bottom:20px;

}

.hero-content p{

    font-size:22px;
    margin-bottom:30px;

}

.btn{

    background:#3498db;
    color:white;

    padding:15px 35px;

    border-radius:10px;

    text-decoration:none;

    font-size:18px;

    transition:0.3s;

}

.btn:hover{

    background:#2980b9;

}

/* SECTION */

.section{

    padding:70px 50px;
    text-align:center;

}

.section h2{

    font-size:40px;
    margin-bottom:20px;

    color:#2c3e50;

}

.section p{

    font-size:18px;
    color:gray;

}

/* CARDS */

.cards{

    display:flex;
    justify-content:center;
    gap:30px;
    flex-wrap:wrap;

    margin-top:40px;

}

.card{

    background:white;

    width:300px;

    border-radius:15px;

    overflow:hidden;

    box-shadow:0 5px 20px rgba(0,0,0,0.1);

}

.card img{

    width:100%;
    height:220px;
    object-fit:cover;

}

.card-content{

    padding:20px;

}

.card-content h3{

    margin-bottom:10px;
    color:#2c3e50;

}

/* FOOTER */

footer{

    background:#2c3e50;
    color:white;
    text-align:center;
    padding:20px;

}

/* RESPONSIVE */

@media(max-width:768px){

.hero-content h1{

    font-size:40px;

}

.hero-content p{

    font-size:18px;

}

header{

    flex-direction:column;

}

nav{

    margin-top:15px;

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

<a href="#">Réservation</a>

<a href="#">Contact</a>

</nav>

</header>

<!-- HERO -->

<section class="hero">

<div class="hero-content">

<h1>

Bienvenue à IntelliHotel

</h1>

<p>

Réservez votre chambre en ligne
facilement et rapidement

</p>

<a href="chambres.php"
class="btn">

Réserver Maintenant

</a>

</div>

</section>

<!-- SECTION -->

<section class="section">

<h2>

Pourquoi choisir IntelliHotel ?

</h2>

<p>

Une plateforme intelligente intégrant
la réservation et l’analyse des données

</p>

<div class="cards">

<div class="card">

<img src="https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?q=80&w=1200">

<div class="card-content">

<h3>Chambres Luxueuses</h3>

<p>

Des chambres modernes avec vue magnifique

</p>

</div>

</div>

<div class="card">

<img src="https://images.unsplash.com/photo-1522798514-97ceb8c4f1c8?q=80&w=1200">

<div class="card-content">

<h3>Réservation Rapide</h3>

<p>

Réservez directement depuis votre téléphone

</p>

</div>

</div>

<div class="card">

<img src="https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?q=80&w=1200">

<div class="card-content">

<h3>Analyse Intelligente</h3>

<p>

Analyse des réservations et des revenus

</p>

</div>

</div>

</div>

</section>

<!-- FOOTER -->

<footer>

IntelliHotel © 2026

</footer>

</body>
</html>