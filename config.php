<?php

$host = "localhost";

$user = "root";

$password = "";

$database = "intellihotel";

// connexion
$conn = new mysqli(
$host,
$user,
$password,
$database
);

// test connexion
if($conn->connect_error){

    die("Connexion échouée : "
    . $conn->connect_error);

}

?>