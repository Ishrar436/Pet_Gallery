<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/try/db/db.php';

$notif_count = 0;
$id = ""; 
$current_user_id = "";


if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    

    if ($_SESSION['role'] == "member") {
        $id = "user_id";
        $current_user_id = $_SESSION['user_id'];
    } 

    elseif ($_SESSION['role'] == "shelter" && isset($_SESSION['shelter_id'])) {
        $id = "shelter_id";
        $current_user_id = $_SESSION['shelter_id'];
    }
   
    else {
        $id = "user_id";
        $current_user_id = $_SESSION['user_id'];
    }


    if (!empty($id)) {
        $notif_query = "SELECT COUNT(*) AS total FROM notifications WHERE $id = '$current_user_id' AND is_read = 0";
        $notif_res = mysqli_query($conn, $notif_query);
        if ($notif_res) {
            $notif_data = mysqli_fetch_assoc($notif_res);
            $notif_count = $notif_data['total'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/try/nav/NavBar.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="/try/index.php" class="nav-logo">
                <span class="paw-icon">🐾</span> PetGallery
            </a>

            <ul class="nav-links">
                <li><a href="/try/index.php">Home</a></li>

                <?php if (isset($_SESSION['role'])): ?>
                    
                    <?php if ($_SESSION['role'] === 'shelter'): ?>
                        <li><a href="/try/EditProfile/Edit_Profile.php">Edit Profile</a></li>
                        <li><a href="/try/Shelter-Member/Shelter_member_view.php" class="shelter-accent">Manage Pets</a></li>

                    <?php elseif ($_SESSION['role'] === 'admin'): ?>
                        <li><a href="/try/Shelter/All_Shelter.php">Shelters</a></li>
                        <li><a href="/try/EditProfile/Edit_Profile.php">Edit Profile</a></li>
                        <li><a href="/try/Admin/Admin.php">Admin</a></li>

                    <?php else: ?>
                        <li><a href="/try/Pet-Gallery/FindPets.php">Find a Pet</a></li>
                        <li><a href="/try/Shelter/All_Shelter.php">Shelters</a></li>
                        <li><a href="/try/EditProfile/Edit_Profile.php">Edit Profile</a></li>
                    <?php endif; ?>

                    <li class="nav-item-notif">
                        <a href="javascript:void(0)" class="notif-link" id="notif-icon">
                            <div class="css-bell"></div>
                            <?php if ($notif_count > 0): ?>
                                <span class="notif-badge"><?php echo $notif_count; ?></span>
                            <?php endif; ?>
                        </a>

                        <div class="notif-dropdown" id="notif-dropdown">
                            <div class="notif-content">
                                <?php
                              
                                $get_notif = "SELECT * FROM notifications WHERE $id = '$current_user_id' ORDER BY created_at DESC LIMIT 5";
                                $res_notif = mysqli_query($conn, $get_notif);

                                if ($res_notif && mysqli_num_rows($res_notif) > 0):
                                    while ($n = mysqli_fetch_assoc($res_notif)): ?>
                                        <div class="notif-item <?php echo $n['is_read'] == 0 ? 'unread' : 'is-read'; ?>">
                                            <p><?php echo htmlspecialchars($n['message']); ?></p>
                                            
                                            <?php if (!empty($n['pet_id'])): ?>
                                                <small>
                                                    <a href="/try/nav/mark_read.php?notif_id=<?php echo $n['id']; ?>&pet_id=<?php echo $n['pet_id']; ?>" style="color: #6366f1; font-weight: bold;">
                                                        View Details →
                                                    </a>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endwhile;
                                else: ?>
                                    <div class="notif-empty">No new notifications</div>
                                <?php endif; ?>
                            </div>
                            <a href="#" class="notif-footer">View All Notifications</a>
                        </div>
                    </li>

                <?php else: ?>
                    <li><a href="/try/Pet-Gallery/FindPets.php">Find a Pet</a></li>
                    <li><a href="/try/Shelter/All_Shelter.php">Shelters</a></li>
                <?php endif; ?>

                <li class="nav-auth">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="/try/Login/Login.php" class="login-link">Login</a>
                        <a href="/try/registration/registration.php" class="register-btn-nav">Join Now</a>
                    <?php else: ?>
                        <span class="user-name">
                            Hi, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?> 
                            <small>(<?php echo ucfirst($_SESSION['role']); ?>)</small>
                        </span>
                        <a href="/try/Logout.php" class="login-link" style="color: #e74c3c; margin-left: 10px;">Log Out</a>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
    </nav>

    <script src="/try/nav/NavBar.js"></script>
</body>
</html>