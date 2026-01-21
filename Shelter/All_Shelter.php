<?php
include "../db/db.php";



$query = "SELECT s.*, l.name AS area_name 
          FROM shelter s
          INNER JOIN locations l ON s.location_id = l.id";
$result = mysqli_query($conn, $query);


$data = mysqli_fetch_all($result, MYSQLI_ASSOC); 

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Our Partner Shelters</title>
    <link rel="stylesheet" href="/try/Shelter/All_Shelter.css">
    <link rel="stylesheet" href="/try/nav/NavBar.css">
</head>
<body>
 <?php include "../nav/NavBar.php"; ?>

<div class="container">
    <h1 style="text-align:center; margin: 20px 0;">Find a Shelter</h1>
    
    <div class="shelter-grid">
        <?php if (!empty($data)): ?>
            <?php foreach($data as $row): ?>
                <div class="shelter-card">
                    <div class="shelter-info">
                        <h2><?php echo htmlspecialchars($row['shelter_name']); ?></h2>
                        
                        <p class="loc-tag">📍 Area: <strong><?php echo htmlspecialchars($row['area_name']); ?></strong></p>
                        
                        <div class="stats">
                            <span>👥 Members: <?php echo $row['members']; ?></span>
                            <span>🐾 Pets: <?php echo $row['pets_count']; ?></span>
                        </div>
                    </div>
                    
                    <div class="card-buttons">
                        <a href="./SheltersPets.php?shelter_id=<?php echo $row['shelter_id']; ?>" class="btn-view">
                            View All Pets
                        </a>
                        <a href="../Appointments/BookShelter.php?id=<?php echo $row['shelter_id']; ?>" class="btn-appoint">
                            Book Appointment
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No shelters found in the database.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>