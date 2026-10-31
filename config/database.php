<?php
$conn = new mysqli('localhost', 'root', '', 'intellihotel');
$conn->set_charset("utf8mb4");
if ($conn->connect_error) {
    die("❌ Erreur: " . $conn->connect_error);
}
?>