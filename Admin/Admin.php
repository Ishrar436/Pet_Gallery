<?php
session_start();

include "../db/db.php";
include "../nav/NavBar.php";

if (!isset($_SESSION['user_id'])) {
   
    header("Location: Login.php"); 
    exit();
}

$current_user_id = (int)$_SESSION['user_id'];


$admin_check_sql = "SELECT is_admin, full_name FROM users WHERE user_id = $current_user_id";
$admin_check_result = mysqli_query($conn, $admin_check_sql);
$admin_data = mysqli_fetch_assoc($admin_check_result);

if (!$admin_data || $admin_data['is_admin'] != 1) {
    die("ACCESS DENIED: You are not an Administrator.");
}




if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    

    if (isset($_POST['action_member_status'])) {
        $member_id = (int)$_POST['member_id'];
        $new_status = mysqli_real_escape_string($conn, $_POST['status']); 
        $admin_id = (int)$_SESSION['user_id'];
        $is_rejected_val = ($new_status === 'Rejected') ? 1 : 0;
        
        $sql = "UPDATE sheltermember SET status = '$new_status', approved_by = $admin_id, is_rejected = $is_rejected_val WHERE member_id = $member_id";
        mysqli_query($conn, $sql);
    }

  if (isset($_POST['action_pet_decision'])) {
    $pet_id = (int)$_POST['pet_id'];
    $decision = $_POST['action_pet_decision'];
    
    if ($decision === 'approve') {
    
        $sql = "UPDATE pets SET adminApproved = 1 WHERE id = $pet_id";
        $status_msg = "approved";
    } else {
  
        $sql = "UPDATE pets SET adminApproved = -1 WHERE id = $pet_id";
        $status_msg = "rejected";
    }
    
    if (mysqli_query($conn, $sql)) {
        header("Location: admin.php?tab=PetAdd&status=" . $status_msg);
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
    exit();
    }

    if (isset($_POST['action_adoption_decision'])) {
    $pet_id = (int)$_POST['pet_id'];
    $decision = mysqli_real_escape_string($conn, $_POST['decision']); 
    $admin_id = (int)$_SESSION['user_id'];
    $applicant_id = (int)$_POST['applicant_id']; 

    if ($decision === 'Accepted') {
      
        $sql = "UPDATE pets SET 
                requestStatus = 'Accepted', 
                adoption_status = 'Adopted', 
                approvedBy = $admin_id,
                requestedNotification = 0 
                WHERE id = $pet_id";
        $msg = "Congratulations! Your adoption request has been approved.";
    } else {
      
        $sql = "UPDATE pets SET 
                requestStatus = 'Rejected', 
                adoption_status = 'Available', 
                final_adoption_by = NULL, 
                requestedNotification = 0 
                WHERE id = $pet_id";
        $msg = "We regret to inform you that your final adoption request was not approved.";
    }

    if (mysqli_query($conn, $sql)) {
        
        mysqli_query($conn, "INSERT INTO notifications (user_id, pet_id, message, is_read) 
                             VALUES ($applicant_id, $pet_id, '$msg', 0)");
                             
        echo "<script>alert('Decision processed: $decision'); window.location.href='admin_dashboard.php?tab=Adoptions';</script>";
    }
}

    if (isset($_POST['action_delete_pet_decision'])) {
    $pet_id = (int)$_POST['pet_id'];
    $decision = $_POST['decision']; 
    
    if ($decision == 'approve') {
        $sql = "UPDATE pets SET deletion_status = 'Deleted', deleted_by = $current_user_id WHERE id = $pet_id";
    } else {
        $sql = "UPDATE pets SET deletion_status = NULL, deleted_req_by = NULL WHERE id = $pet_id";
    }
    mysqli_query($conn, $sql);
    }
    
    header("Location: admin.php?tab=" . (isset($_POST['current_tab']) ? $_POST['current_tab'] : 'dashboard'));
    exit();
}


$sql_members = "SELECT 
                    sm.*, 
                    s.shelter_name as actual_shelter_name, 
                    l.name as location_name 
                FROM sheltermember sm
                LEFT JOIN shelter s ON sm.shelter_name = s.shelter_id
                LEFT JOIN locations l ON sm.location = l.id
                WHERE sm.status = 'Pending'";

$res_members = mysqli_query($conn, $sql_members);

$sql_all_shelters = "SELECT s.*, l.name as location_name FROM shelter s 
                     LEFT JOIN locations l ON s.location_id = l.id";
$res_all_shelters = mysqli_query($conn, $sql_all_shelters);

$sql_all_users = "SELECT 
                    u.user_id, 
                    u.full_name, 
                    u.email, 
                    u.is_admin, 
                    u.is_shelter_member,
                    u.is_approved,
                    u.shelter_id,
                    s.shelter_name
                  FROM users u
                  LEFT JOIN shelter s ON u.shelter_id = s.shelter_id
                  ORDER BY u.user_id DESC";

$res_all_users = mysqli_query($conn, $sql_all_users);

$sql_del_req = "SELECT 
                    p.*, 
                    sm.full_name AS requester_name, 
                    s.shelter_name AS shelterName
                FROM pets p 
                JOIN sheltermember sm ON p.deleted_req_by = sm.member_id 
                LEFT JOIN shelter s ON p.shelterId = s.shelter_id 
                WHERE p.deletion_status = 'Pending Delete'";

$res_del_req = mysqli_query($conn, $sql_del_req);

$sql_pet_approval = "SELECT p.id, p.petName, p.breed, p.addedBy, s.shelter_name 
                     FROM pets p 
                     LEFT JOIN shelter s ON p.shelterId = s.shelter_id 
                     WHERE p.adminApproved = 0 
                     AND (p.deletion_status IS NULL OR p.deletion_status != 'Deleted')";
$res_pet_approval = mysqli_query($conn, $sql_pet_approval);

$sql_adoptions = "SELECT p.id, p.petName, p.breed, p.addedBy, p.requestStatus, 
                  s.shelter_name AS shelterName, l.name AS location
                  FROM pets p
                  LEFT JOIN shelter s ON p.shelterId = s.shelter_id
                  LEFT JOIN locations l ON s.location_id = l.id
                  WHERE p.adminApproved = 1"; 
$res_adoptions = mysqli_query($conn, $sql_adoptions);







$final_pet_approval = "SELECT 
                        p.id, 
                        p.petName, 
                        p.breed, 
                        p.addedBy, 
                        p.final_adoption_by,
                        p.appointmentDatetime,
                        s.shelter_name,
                        u.full_name AS applicant_name
                     FROM pets p 
                     LEFT JOIN shelter s ON p.shelterId = s.shelter_id 
                     LEFT JOIN users u ON p.final_adoption_by = u.user_id
                     WHERE p.requestStatus = 'Pending' 
                     AND p.requestedNotification = 1 
                     AND p.final_adoption_by IS NOT NULL
                     AND (p.deletion_status IS NULL OR p.deletion_status != 'Deleted')";

$final_adoption = mysqli_query($conn, $final_pet_approval);
$sql_adopted_history = "SELECT p.*, s.shelter_name, u.full_name AS adopter_name 
                        FROM pets p 
                        LEFT JOIN shelter s ON p.shelterId = s.shelter_id 
                        LEFT JOIN users u ON p.final_adoption_by = u.user_id
                        WHERE p.adoption_status = 'Adopted'
                        AND (p.deletion_status IS NULL OR p.deletion_status != 'Deleted')
                        ORDER BY p.id DESC";

$history_result = mysqli_query($conn, $sql_adopted_history);
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Rifat Pet Gallery</title>
    <link rel="stylesheet" href="../Admin/Admin.css">
    <link rel="stylesheet" href="../nav/NavBar.css">
</head>
<body>

<div class="admin-container">
    
    <aside class="sidebar">
        <div class="logo">Admin Panel</div>
        <div class="user-info">Hello, <?php echo htmlspecialchars($admin_data['full_name']); ?></div>
        
        <nav>
                    <a href="?tab=dashboard" class="<?php echo $active_tab == 'dashboard' ? 'active' : ''; ?>">Dashboard Overview</a>
                    <a href="?tab=members" class="<?php echo $active_tab == 'members' ? 'active' : ''; ?>">Shelter Member Approvals</a>
                    <a href="?tab=Adoptions" class="<?php echo $active_tab == 'Adoptions' ? 'active' : ''; ?>">Pet Adoption Approvals</a>
                    <a href="?tab=PetAdd" class="<?php echo $active_tab == 'PetAdd' ? 'active' : ''; ?>">Pet ADDED Requests</a>
                    <a href="?tab=deletions" class="<?php echo $active_tab == 'deletions' ? 'active' : ''; ?>">Pet Deletion Requests</a>
                    
                    <a href="?tab=shelters" class="<?php echo $active_tab == 'shelters' ? 'active' : ''; ?>">Shelter Management</a>
                    <a href="?tab=users" class="<?php echo $active_tab == 'users' ? 'active' : ''; ?>">User Management</a>

    
        </nav>
    </aside>

    <main class="content">
        
        <?php if($active_tab == 'dashboard'): ?>
        <section id="dashboard">
            <h1>Dashboard Overview</h1>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Pending Members</h3>
                    <p><?php echo mysqli_num_rows($res_members); ?></p>
                    <a href="?tab=members">view all</a>
                </div>
                <div class="stat-card">
                    <h3>Pending Pets</h3>
                    <p><?php echo mysqli_num_rows($res_pet_approval); ?></p>
                    <a href="?tab=PetAdd">view all</a>
                </div>
                <div class="stat-card">
                    <h3>Deletion Requests</h3>
                    <p><?php echo mysqli_num_rows($res_del_req); ?></p>
                    <a href="?tab=deletions">view all</a>
                </div>
                <div class="stat-card">
                    <h3>Shelters</h3>
                    <p><?php echo mysqli_num_rows($res_all_shelters); ?></p>
                    <a href="?tab=shelters">view all</a>
                </div>
                <div class="stat-card">
                    <h3>Total users</h3>
                    <p><?php echo mysqli_num_rows($res_all_users); ?></p>
                    <a href="?tab=users">view all</a>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <?php if($active_tab == 'members'): ?>
        <section>
            <h1>Approve Shelter Members</h1>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Shelter Name</th>
                        <th>Shelter Location</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($res_members)): ?>
                       <?php //print_r($row); ?>
                        
                    <tr>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['actual_shelter_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['location_name']); ?></td>
                        <td><span class="status-badge pending">Pending</span></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="current_tab" value="members">
                                <input type="hidden" name="member_id" value="<?php echo $row['member_id']; ?>">
                                <input type="hidden" name="action_member_status" value="1">
                                <button type="submit" name="status" value="Approved" class="btn btn-approve">Approve</button>
                                <button type="submit" name="status" value="Rejected" class="btn btn-reject">Reject</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
        <?php endif; ?>
                       
<?php if($active_tab == 'Adoptions'): ?>

    <section>

        <h1 style="color: #2c3e50; margin-bottom: 20px;">⏳ Pending Pet Adoption Requests</h1>

        <div class="pet-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">

            <?php 

            if (mysqli_num_rows($final_adoption) > 0): 

                while($row = mysqli_fetch_assoc($final_adoption)): 

            ?>

                <div class="pet-card" style="border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: #fff;">

                    <div class="img-container">

                        <?php if(!empty($row['petPhoto'])): ?>

                            <img src="data:image/jpeg;base64,<?php echo base64_encode($row['petPhoto']); ?>" alt="Pet" style="width:100%; height:200px; object-fit:cover;">

                        <?php endif; ?>

                    </div>

                    <div class="pet-info" style="padding: 15px;">

                        <h3><?php echo htmlspecialchars($row['petName']); ?></h3>

                        <p><strong>Applicant:</strong> <?php echo htmlspecialchars($row['applicant_name']); ?></p>

                           <a href="../EditProfile/Edit_Profile.php?id=<?php echo $row['final_adoption_by']; ?>" class="btn-view-user" 

                            >

                                👤 View Applicant Profile

                            </a>

                        <hr style="margin: 10px 0; border: 0; border-top: 1px solid #eee;">



                    



                        <div class="action-buttons" style="display: flex; gap: 5px;">

                            <form method="POST" onsubmit="return confirm('Approve this adoption?');" style="flex: 1;">

                                <input type="hidden" name="pet_id" value="<?php echo $row['id']; ?>">

                                <input type="hidden" name="applicant_id" value="<?php echo $row['final_adoption_by']; ?>">

                                <input type="hidden" name="decision" value="Accepted">

                                <button type="submit" name="action_adoption_decision" style="width: 100%; background: #27ae60; color: white; border: none; padding: 10px; border-radius: 4px; cursor: pointer; font-weight: bold;">Approve</button>

                            </form>



                            <form method="POST" onsubmit="return confirm('Reject this adoption?');" style="flex: 1;">

                                <input type="hidden" name="pet_id" value="<?php echo $row['id']; ?>">

                                <input type="hidden" name="applicant_id" value="<?php echo $row['final_adoption_by']; ?>">

                                <input type="hidden" name="decision" value="Rejected">

                                <button type="submit" name="action_adoption_decision" style="width: 100%; background: #e74c3c; color: white; border: none; padding: 10px; border-radius: 4px; cursor: pointer; font-weight: bold;">Reject</button>

                            </form>

                        </div>

                    </div>

                </div>

            <?php endwhile; else: ?>

                <div class="no-data" style="grid-column: 1/-1; padding: 20px; background: #f9f9f9; text-align: center; border-radius: 8px;">No pending adoption requests.</div>

            <?php endif; ?>

        </div>

    </section>



    <hr style="margin: 40px 0; border: 0; border-top: 2px dashed #ccc;">



    <section>

        <h1 style="color: #27ae60; margin-bottom: 20px;">✅ Successfully Adopted Pets</h1>

        <div class="pet-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">

            <?php 

            if (mysqli_num_rows($history_result) > 0): 

                while($h_row = mysqli_fetch_assoc($history_result)): 

            ?>

                <div class="pet-card" style="border: 1px solid #c3e6cb; border-radius: 8px; overflow: hidden; background: #f0fff4;">

                    <div class="img-container" style="position: relative;">

                        <?php if(!empty($h_row['petPhoto'])): ?>

                            <img src="data:image/jpeg;base64,<?php echo base64_encode($h_row['petPhoto']); ?>" alt="Pet" style="width:100%; height:180px; object-fit:cover; filter: grayscale(30%);">

                        <?php endif; ?>

                        <span style="position: absolute; top: 10px; right: 10px; background: #27ae60; color: white; padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: bold;">ADOPTED</span>

                    </div>



                    <div class="pet-info" style="padding: 15px;">

                        <h3 style="margin-top: 0; color: #1b5e20;"><?php echo htmlspecialchars($h_row['petName']); ?></h3>

                        

                        <p><strong>New Owner:</strong> 

                            <?php echo htmlspecialchars($h_row['adopter_name']); ?> 

                          <a href="../EditProfile/Edit_Profile.php?id=<?php echo $row['final_adoption_by']; ?>" class="btn-view-user" 

                            >

                                👤 View Applicant Profile

                            </a>

                        </p>

                        

                        <p><strong>Shelter:</strong> <?php echo htmlspecialchars($h_row['shelter_name']); ?></p>

                        <p><strong>Breed:</strong> <?php echo htmlspecialchars($h_row['breed']); ?></p>

                        

                        <div style="margin-top: 15px; padding: 10px; background: #fff; border-radius: 4px; border: 1px solid #d4edda; font-size: 0.85rem; color: #155724;">

                            🎉 This pet has found their forever home!

                        </div>

                    </div>

                </div>

            <?php endwhile; else: ?>

                <div class="no-data" style="grid-column: 1/-1; padding: 20px; background: #f9f9f9; text-align: center; border-radius: 8px;">No adoption history found.</div>

            <?php endif; ?>

        </div>

    </section>

<?php endif; ?>

        <?php if($active_tab == 'deletions'): ?>
        <section>
            <h1>Pet Deletion Requests</h1>
            <table class="data-table">
                <thead>
                        <tr>
                            <th>Pet Name</th>
                            <th>Shelter</th>
                            <th>Requested By</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($res_del_req)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['petName']); ?></td>
                        <td><?php echo htmlspecialchars($row['shelterName']); ?></td>
                        <td><?php echo htmlspecialchars($row['requester_name']); ?></td>
                        <td><span class="status-badge danger">Pending Delete</span></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="pet_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="action_delete_pet_decision" value="1">
                                <button type="submit" name="decision" value="approve" class="btn btn-reject">Delete</button>
                                <button type="submit" name="decision" value="reject" class="btn btn-approve">Keep</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
        <?php endif; ?>

        <?php if($active_tab == 'PetAdd'): ?>
        <section>
            <h1>Pet Added Requests</h1>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Pet Name</th>
                        <th>Breed</th>
                        <th>Shelter</th>
                        <th>Added By</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($res_pet_approval)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['petName']); ?></td>
                        <td><?php echo htmlspecialchars($row['breed']); ?></td>
                        <td><?php echo htmlspecialchars($row['shelter_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['addedBy']); ?></td>
                        <td>
                        <form method="POST">
                                <input type="hidden" name="current_tab" value="PetAdd">
                                <input type="hidden" name="pet_id" value="<?php echo $row['id']; ?>">
                                
                                <button type="submit" name="action_pet_decision" value="approve" class="btn btn-approve">
                                    Approve Post
                                </button>
                                
                                <button type="submit" name="action_pet_decision" value="reject" class="btn btn-reject">
                                    Reject Post
                                </button>
                        </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
        <?php endif; ?>

        <?php if($active_tab == 'shelters'): ?>
                <section>
                    <h1>Shelter Management</h1>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Shelter ID</th>
                                <th>Shelter Name</th>
                                <th>Location</th>
                                <th>Pet Count</th>
                                <th>view</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($s_row = mysqli_fetch_assoc($res_all_shelters)): ?>
                            
                            <tr>
                                <td><?php echo $s_row['shelter_id']; ?></td>
                                <td><?php echo htmlspecialchars($s_row['shelter_name']); ?></td>
                                <td><?php echo htmlspecialchars($s_row['location_name']); ?></td>
                                <td><?php echo $s_row['pets_count']; ?></td>
                                <td>
                                    <a href="../Shelter/SheltersPets.php?shelter_id=<?php echo $s_row['shelter_id']; ?>" class="view-btn">
                                        View Pets
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </section>
        <?php endif; ?>

        <?php if($active_tab == 'users'): ?>
            <section>
                <h1>User Management</h1>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Primary Role</th>
                            <th>Status</th>
                            <th>View</th>
                        
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($u_row = mysqli_fetch_assoc($res_all_users)): ?>
                       
                    <tr>
                        <td><?php echo $u_row['user_id']; ?></td>
                        <td><?php echo htmlspecialchars($u_row['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($u_row['email']); ?></td>
                        <td>
                                <?php 
                                        if ($u_row['is_admin'] == 1) {
                                            echo '<span class="status-badge accepted">System Admin</span>';
                                        } 
                                        elseif ($u_row['is_shelter_member'] == 1) {
                                            echo '<span class="status-badge">Shelter Member</span>';
                                        } 
                                        else {
                                            echo '<span class="status-badge" style="background: #bdc3c7;">Standard User</span>';
                                        }
                                ?>
                        </td>

                        <td>
                            <?php 
                            if ($u_row['is_admin'] == 1) {
                                // Admins are always "Active"
                                echo '<span class="status-badge accepted">Full Access</span>';
                            } 
                            elseif ($u_row['is_shelter_member'] == 1) {
                                if ($u_row['is_approved'] == 1) {
                                    // Approved Member
                                    echo '<span class="status-badge accepted">Approved (' . htmlspecialchars($u_row['shelter_name'] ?? 'N/A') . ')</span>';
                                } else {
                                    // Waiting for Admin Approval
                                    echo '<span class="status-badge pending">Pending Approval</span>';
                                }
                            } 
                            else {
                                // Regular public users
                                echo '<span class="status-badge" style="background: #ecf0f1; color: #7f8c8d;">Public Account</span>';
                            }
                            ?>
                        </td>
                        <td>
                          <a href="../EditProfile/Edit_Profile.php?id=<?php echo $u_row['user_id']; ?>">view</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                
                </tbody>
                </table>
            </section>
         <?php endif; ?>





    </main>
</div>

<script src="../Admin/Admin.js"></script>
<script src="../nav/NavBar.js"></script>
</body>
</html>