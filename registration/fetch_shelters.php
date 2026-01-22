<?php
 Move header to the top
header('Content-Type: application/json');

 1. Check your path! If this file is in /registration/, use "../db/db.php"
include "../db/db.php"; 

if (!isset($_GET['location_id'])) {
    echo json_encode([]);
    exit;
}

$location_id = intval($_GET['location_id']);

$sql = "SELECT shelter_id AS id, shelter_name AS name
        FROM shelter
        WHERE location_id = $location_id";

$result = mysqli_query($conn, $sql);

$shelters = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $shelters[] = $row;
    }
}


echo json_encode($shelters);
exit;