<?php
include "../db/db.php";
include "../nav/NavBar.php";

if (isset($_GET['shelter_id'])) {
    $s_id = mysqli_real_escape_string($conn, $_GET['shelter_id']);

 
    $shelter_q = mysqli_query($conn, "SELECT shelter_name FROM shelter WHERE shelter_id = '$s_id'");
    $shelter_data = mysqli_fetch_assoc($shelter_q);

    
    $pet_query = "SELECT * FROM pets WHERE ShelterId = '$s_id' AND adoption_status = 'Available'";
    $pet_result = mysqli_query($conn, $pet_query);
   

} else {
    header("Location: AllShelters.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pets at <?php echo $shelter_data['shelter_name']; ?></title>
    <link rel="stylesheet" href="SheltersPets.css">
</head>
<body>

<div class="container">
    <a href="/try/Shelter/All_Shelter.php" class="back-link">← All Shelters</a>
    <h1>Pets available at <?php echo htmlspecialchars($shelter_data['shelter_name']); ?></h1>

    <div class="pet-grid">
        <?php if(mysqli_num_rows($pet_result) > 0): ?>
            <?php while($pet = mysqli_fetch_assoc($pet_result)): ?>
             
                <div class="pet-card">
                       <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'shelter')): ?>
                            <div class="status-overlay">
                                <?php if ($pet['adminApproved'] == 1): ?>
                                    <span class="badge-approved">Approved</span>
                                <?php else: ?>
                                    <span class="badge-pending">Pending Approval</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <img src="<?php echo $pet['petPhoto']; ?>" alt="Pet" width="200">

                        <h3><?php echo htmlspecialchars($pet['petName']); ?></h3>
                        <p><?php echo htmlspecialchars($pet['breed']); ?></p>
                        
                        <a href="../PetDetails/PetDetails.php?id=<?php echo $pet['id']; ?>" class="details-btn">
                            Learn More
                        </a>
                 </div>
             <?php endwhile; ?>
        <?php else: ?>
            <p>No pets currently available at this location.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>