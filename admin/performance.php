<?php

$conn = new mysqli('localhost', 'root', '', 'intellihotel');
$conn->set_charset("utf8");

/* =========================
   PERFORMANCE QUERY
========================= */

$sql = "

SELECT 
    u.nom,

    COUNT(rr.reservation_id) AS total_reservations,

    SUM(
        CASE 
            WHEN r.statut != 'annulee' 
            THEN r.prix_total 
            ELSE 0 
        END
    ) AS revenus,

    COUNT(
        CASE 
            WHEN r.statut = 'annulee' 
            THEN 1 
        END
    ) AS annulations

FROM utilisateurs u

LEFT JOIN reservation_receptionniste2 rr
    ON u.id = rr.receptionniste_id

LEFT JOIN reservations r
    ON rr.reservation_id = r.id

WHERE u.role = 'receptionniste'

GROUP BY u.id, u.nom

ORDER BY revenus DESC

";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="fr">

<head>
<meta charset="UTF-8">
<title>Performance Réceptionnistes</title>

<style>

body{
    font-family: Arial;
    background: #f1f5f9;
    padding: 40px;
}

h1{
    margin-bottom: 30px;
    color: #0f172a;
}

table{
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
}

th{
    background: #0ea5e9;
    color: white;
    padding: 18px;
}

td{
    padding: 18px;
    border-bottom: 1px solid #ddd;
}

tr:hover{
    background: #f8fafc;
}

.rank{
    font-size: 22px;
}

.good{
    color: #16a34a;
    font-weight: bold;
}

.medium{
    color: #f59e0b;
    font-weight: bold;
}

.bad{
    color: #ef4444;
    font-weight: bold;
}

</style>

</head>

<body>

<h1>📊 Analyse Performance Réceptionnistes</h1>

<table>

<tr>
    <th>Classement</th>
    <th>Réceptionniste</th>
    <th>Réservations</th>
    <th>Revenus</th>
    <th>Taux Réussite</th>
</tr>

<?php

$rank = 1;

while($row = $result->fetch_assoc()){

    $total = $row['total_reservations'];
    $ann = $row['annulations'];

    $success = ($total > 0)
        ? round((($total - $ann) / $total) * 100, 1)
        : 0;

?>

<tr>

    <td class="rank">
        <?php
        if($rank == 1) echo "🥇";
        elseif($rank == 2) echo "🥈";
        elseif($rank == 3) echo "🥉";
        else echo "#".$rank;
        ?>
    </td>

    <td><?php echo $row['nom']; ?></td>

    <td><?php echo $total; ?></td>

    <td><?php echo $row['revenus']; ?> DH</td>

    <td>
        <?php
        if($success >= 80){
            echo "<span class='good'>🔥 ".$success."%</span>";
        }
        elseif($success >= 50){
            echo "<span class='medium'>⭐ ".$success."%</span>";
        }
        else{
            echo "<span class='bad'>❌ ".$success."%</span>";
        }
        ?>
    </td>

</tr>

<?php $rank++; } ?>

</table>

</body>
</html>