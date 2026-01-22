<?php

    session_start();

include "../db/db.php";

$msg = "";
$error = "";
$already_booked = false;
$current_status = "";
$s_id = "";


if (isset($_GET['id'])) {
    $pet_id = mysqli_real_escape_string($conn, $_GET['id']);

 
    $query = "SELECT pets.*, shelter.shelter_name 
              FROM pets 
              LEFT JOIN shelter ON pets.shelterId = shelter.shelter_id 
              WHERE pets.id = '$pet_id'";
              
    $result = mysqli_query($conn, $query);
    $pet_Info = mysqli_fetch_assoc($result);

    if (!$pet_Info) {
        die("Pet not found.");
    }
    
    $s_id = $pet_Info['shelterId']; 

 
    if (isset($_SESSION['user_id'])) {
        $u_id = $_SESSION['user_id'];
        $check_query = "SELECT status FROM appointments 
                        WHERE pet_id = '$pet_id' AND user_id = '$u_id' 
                        AND status != 'Rejected' LIMIT 1";
        $check_res = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_res) > 0) {
            $already_booked = true;
            $app_data = mysqli_fetch_assoc($check_res);
            $current_status = $app_data['status'];
           
        }
    }

} else {
    header("Location: ../Pet-Gallery/FindPets.php");
    exit();
}


if (isset($_POST['book_appointment'])) {
    if (!isset($_SESSION['user_id'])) {
        echo "<script>alert('Please login'); window.location.href='../Login/Login.php';</script>";
        exit();
    }

    if ($already_booked) {
        $error = "You already have an active request for this pet.";
    } else {
        $appointment_time = mysqli_real_escape_string($conn, $_POST['appointment_time']);
        $user_id = $_SESSION['user_id'];

        $insert_sql = "INSERT INTO appointments (pet_id, user_id, shelter_id, appointment_time, status) 
                       VALUES ('$pet_id', '$user_id', '$s_id', '$appointment_time', 'Pending')";
        
        if (mysqli_query($conn, $insert_sql)) {
            $msg = "Appointment requested successfully!";
            $already_booked = true; 
            $current_status = "Pending";
            
            $notification_msg = "New appointment request for " . $pet_Info['petName'];
            mysqli_query($conn, "INSERT INTO notifications (shelter_id, pet_id, message, is_read) 
                                 VALUES ('$s_id', '$pet_id', '$notification_msg', 0)");
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}


if (isset($_POST['confirm_adoption'])) {
    if (!isset($_SESSION['user_id'])) {
        exit("Unauthorized access.");
    }

    $user_id = $_SESSION['user_id'];
    
 
    $final_sql = "UPDATE pets SET 
                  requestStatus = 'Pending', 
                  requestedNotification = 1, 
                  adoption_status = 'Pending', 
                  final_adoption_by = '$user_id' 
                  WHERE id = '$pet_id'";

    if (mysqli_query($conn, $final_sql)) {
        header("Location: PetDetails.php?id=$pet_id&adoption_msg=success");
        exit();
    } else {
        $error = "Finalization failed: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pet_Info['pet_name']); ?> | Details</title>
    <link rel="stylesheet" href="../nav/NavBar.css">
    <link rel="stylesheet" href="PetDetails.css">
</head>
<body>

<?php include "../nav/NavBar.php"; ?>

<div class="details-container">
    <div class="pet-header">
        <a href="../Pet-Gallery/FindPets.php" class="back-link">← Back to Search</a>
    </div>

    <div class="details-grid">
        <div class="image-section">
            <?php if(!empty($pet_Info['petPhoto'])): ?>
                <img src="<?php echo $pet_Info['petPhoto']; ?>" id="petPreview" alt="Pet Image" class="main-img">
               
         
            <?php endif; ?>
        </div>

        <div class="info-section">
            <div class="title-area">
                <h1>Meet <?php echo htmlspecialchars($pet_Info['petName']); ?></h1>
                <span class="shelter-name">📍 <?php echo htmlspecialchars($pet_Info['shelter_name'] ?? 'Private Owner'); ?></span>
            </div>
            
            <hr>

            <div class="tags">
                <span class="tag">🧬 <?php echo htmlspecialchars($pet_Info['breed']); ?></span>
                <span class="tag">🐾 <?php echo htmlspecialchars($pet_Info['category']); ?></span>
                <span class="tag">⏳ <?php echo htmlspecialchars($pet_Info['age']); ?></span>
                <span class="tag <?php echo $pet_Info['isVaccinated'] ? 'green' : 'red'; ?>">
                    <?php echo $pet_Info['isVaccinated'] ? '✓ Vaccinated' : '✗ Not Vaccinated'; ?>
                </span>
            </div>

            <div class="description">
                <h3>About Me</h3>
                <p><?php echo nl2br(htmlspecialchars($pet_Info['behaviour'])); ?></p>
                <p><strong>Primary Color:</strong> <?php echo htmlspecialchars($pet_Info['color']); ?></p>
            </div>

            <hr>

                     
             
             
             
             <div class="appointment-card">
    <h3>Appointment & Adoption</h3>
    
    <?php if ($already_booked): ?>
        <div class="status-box">
            <?php 
           
            $is_final_approved = ($pet_Info['adoption_status'] === 'Adopted' && isset($_SESSION['user_id']) && $_SESSION['user_id'] == $pet_Info['final_adoption_by']);
            $is_waiting_approval = ($pet_Info['requestedNotification'] == 1 || $pet_Info['requestStatus'] === 'Pending');
            ?>

            <?php if ($is_final_approved): ?>
                <div class="final-success" style="background: #e8f5e9; border-left: 5px solid #2e7d32; padding: 20px; border-radius: 8px; text-align: center;">
                    <h2 style="color: #1b5e20; margin-bottom: 10px;">🎉 Congratulations!</h2>
                    <p style="color: #1b5e20; font-size: 1.1rem; line-height: 1.5;">
                        Your adoption request for <strong><?php echo htmlspecialchars($pet_Info['petName']); ?></strong> has been officially approved! 
                        <br><br>
                        <strong>Next Step:</strong> You can now come to the shelter to collect your new family member. Please bring a valid ID.
                    </p>
                </div>

            <?php elseif ($is_waiting_approval): ?>
                <div class="approval-wait" style="background: #fff9e6; border-left: 5px solid #f1c40f; padding: 15px; border-radius: 8px;">
                    <p style="color: #856404; font-weight: bold; margin: 0;">⏳ Waiting for Admin Approval</p>
                    <p style="font-size: 0.85rem; color: #856404; margin-top: 5px;">
                        Your final adoption request is being reviewed. We will notify you once the shelter confirms the handover.
                    </p>
                </div>

            <?php elseif ($current_status === 'Accepted'): ?>
                <div class="adoption-action" style="margin-top: 20px; padding-top: 15px; border-top: 1px dashed #ccc;">
                    <p><strong>Visit Approved!</strong> You've met <?php echo htmlspecialchars($pet_Info['petName']); ?>. Click below to finalize the adoption.</p>
                    <form method="POST">
                        <input type="hidden" name="pet_id" value="<?php echo $pet_id; ?>">
                        <button type="submit" name="confirm_adoption" class="btn-adopt" style="background: #27ae60; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; width: 100%;">
                            🐾 Finalize Adoption
                        </button>
                    </form>
                </div>

            <?php else: ?>
                <p>Request submitted for this pet.</p>
                <span class="status-badge <?php echo strtolower($current_status); ?>">
                    Status: <?php echo $current_status; ?>
                </span>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <?php if($msg) echo "<p class='success-alert'>$msg</p>"; ?>
        <?php if($error) echo "<p class='error-alert'>$error</p>"; ?>

        <form method="POST">
    <?php 
    $current_role = isset($_SESSION['role']) ? strtolower(trim($_SESSION['role'])) : '';
    $is_restricted = ($current_role === 'admin' || $current_role === 'shelter'); 
    ?>

    <label>Choose a date & time to visit:</label>
    
    <input type="datetime-local" 
           name="appointment_time" 
           required 
           min="<?php echo date('Y-m-d\TH:i'); ?>" 
           <?php echo $is_restricted ? 'readonly style="background:#eee;"' : ''; ?>>

    <button type="submit" 
            name="book_appointment" 
            class="book-btn" 
            <?php echo $is_restricted ? 'disabled' : ''; ?>>
        <?php echo $is_restricted ? 'Restricted for ' . ucfirst($current_role) : 'Request Appointment'; ?>
    </button>
    
    <?php if ($is_restricted): ?>
        <p style="color: red; font-size: 0.8em; margin-top: 5px;">
            Admins and Shelters cannot book appointments.
        </p>
    <?php endif; ?>
</form>
    <?php endif; ?>
</div>
    </div>
</div>
<script src="/nav/NavBar.js"></script>
</body>
</html>