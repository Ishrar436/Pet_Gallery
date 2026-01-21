<?php

    session_start();

include "../db/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

$logged_in_user = (int)$_SESSION['user_id'];
$message = "";


$admin_check_query = mysqli_query($conn, "SELECT is_admin FROM users WHERE user_id = '$logged_in_user'");
$admin_row = mysqli_fetch_assoc($admin_check_query);
$is_currently_admin = ($admin_row && $admin_row['is_admin'] == 1);

if (isset($_GET['id']) && $is_currently_admin) {
    $user_id = (int)$_GET['id'];
    $is_admin_viewing = true; 
} else {
    $user_id = $logged_in_user;
    $is_admin_viewing = false;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile']) ) {
    $full_name    = mysqli_real_escape_string($conn, $_POST['full_name']);
    $phone_number = mysqli_real_escape_string($conn, $_POST['phone_number']);
    $occupation   = mysqli_real_escape_string($conn, $_POST['occupation']);
    $home_address = mysqli_real_escape_string($conn, $_POST['home_address']);
    $gender       = mysqli_real_escape_string($conn, $_POST['gender']);

    $update_sql = "UPDATE users SET 
                   full_name = '$full_name', 
                   phone_number = '$phone_number', 
                   occupation = '$occupation', 
                   home_address = '$home_address', 
                   gender = '$gender' 
                   WHERE user_id = '$user_id'";

    if (mysqli_query($conn, $update_sql)) {
        $message = "<div class='alert success'>Profile updated successfully!</div>";
    } else {
        $message = "<div class='alert error'>Update failed: " . mysqli_error($conn) . "</div>";
    }
}


$user_res = mysqli_query($conn, "SELECT * FROM users WHERE user_id = '$user_id'");
$user = mysqli_fetch_assoc($user_res);

if (!$user) {
    die("User not found.");
}


$stats_res = mysqli_query($conn, "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Accepted' THEN 1 ELSE 0 END) as accepted,
    SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) as rejected
    FROM appointments WHERE user_id = '$user_id'");
$stats = mysqli_fetch_assoc($stats_res);


$requests_query = "
    SELECT 
        a.*,
        p.petName,
        p.breed,
        sh.shelter_name AS shelter_name
    FROM appointments a
    LEFT JOIN pets p ON a.pet_id = p.id
    LEFT JOIN shelter sh ON a.shelter_id = sh.shelter_id
    WHERE a.user_id = '$user_id'
    ORDER BY a.appointment_time DESC
";
$requests_res = mysqli_query($conn, $requests_query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $is_admin_viewing ? "Viewing Profile" : "My Dashboard"; ?></title>
    <link rel="stylesheet" href="/try/EditProfile/Edit_Profile.css">
    <link rel="stylesheet" href="../nav/NavBar.css">

</head>
<body>
    <?php include "../nav/NavBar.php"; ?>

    <div class="dashboard-container">
        <aside class="sidebar">
            <?php if ($is_admin_viewing): ?>
                <a href="../Admin/Admin.php?tab=users" class="back-link">← Back to Admin Panel</a>
                <div class="admin-view-badge">ADMIN VIEW MODE</div>
            <?php endif; ?>

            <div class="profile-header">
                <div class="user-initials">
                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                </div>
                <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>

            <div class="mini-stats">
                <div class="stat-item">
                    <strong><?php echo (int)$stats['total']; ?></strong>
                    <span>Applied</span>
                </div>
                <div class="stat-item green">
                    <strong><?php echo (int)$stats['accepted']; ?></strong>
                    <span>Approved</span>
                </div>
                <div class="stat-item red">
                    <strong><?php echo (int)$stats['rejected']; ?></strong>
                    <span>Rejected</span>
                </div>
            </div>
        </aside>

        <main class="main-content">
            
            <?php if ($stats['accepted'] > 0): ?>
                <div class="notification-banner">
                    🎉 <?php echo $is_admin_viewing ? "This user has" : "Good news! You have"; ?> <strong><?php echo $stats['accepted']; ?></strong> approved adoption request(s).
                </div>
            <?php endif; ?>

            <section class="content-box">
                <div class="box-header">
                    <h2>Personal Information</h2>
                    
                        <button type="button" id="editBtn" class="btn-small">Edit</button>
                   
                </div>
                
                <?php echo $message; ?>

                <form id="profileForm" method="POST">
                    <div class="grid-inputs">
                        <div class="field">
                            <label>Full Name</label>
                            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly required>
                        </div>
                        <div class="field">
                            <label>Phone</label>
                            <input type="text" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>" readonly>
                        </div>
                        <div class="field">
                            <label>Gender</label>
                            <select name="gender" disabled>
                                <option value="Male" <?php if($user['gender'] == 'Male') echo 'selected'; ?>>Male</option>
                                <option value="Female" <?php if($user['gender'] == 'Female') echo 'selected'; ?>>Female</option>
                                <option value="Other" <?php if($user['gender'] == 'Other') echo 'selected'; ?>>Other</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Occupation</label>
                            <input type="text" name="occupation" value="<?php echo htmlspecialchars($user['occupation']); ?>" readonly>
                        </div>
                        <div class="field full">
                            <label>Home Address</label>
                            <textarea name="home_address" readonly><?php echo htmlspecialchars($user['home_address']); ?></textarea>
                        </div>
                    </div>

                    
                    <div id="formActions" class="form-actions hidden">
                        <button type="submit" name="update_profile" class="btn-primary">Save Changes</button>
                        <button type="button" id="cancelBtn" class="btn-secondary">Cancel</button>
                    </div>
                  
                </form>
            </section>

            <section class="content-box">
                <h2>Adoption Requests</h2>
                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>Pet Info</th>
                                <th>Shelter</th> 
                                <th>Date/Time</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($requests_res)): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['petName']); ?></strong><br>
                                        <small><?php echo htmlspecialchars($row['breed']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['shelter_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['appointment_time'])); ?></td>
                                    <td>
                                        <span class="badge <?php echo strtolower($row['status']); ?>">
                                            <?php echo $row['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="../PetDetails/PetDetails.php?id=<?php echo $row['pet_id']; ?>" class="view-btn" style="text-decoration:none; display:inline-block; text-align:center;">View Pet</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script src="/try/EditProfile/Edit_Profile.js"></script>
</body>
</html>