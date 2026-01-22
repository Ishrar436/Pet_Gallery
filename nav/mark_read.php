<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/try/db/db.php";
session_start();

if (isset($_GET['notif_id']) && isset($_SESSION['user_id'])) {
    $notif_id = mysqli_real_escape_string($conn, $_GET['notif_id']);
    $pet_id = mysqli_real_escape_string($conn, $_GET['pet_id']);
    $role = $_SESSION['role']; 

    
    if ($role === 'shelter') {
        $current_id = $_SESSION['shelter_id'];
        $column = "shelter_id";
    } else {
        $current_id = $_SESSION['user_id'];
        $column = "user_id";
    }

    $query = "UPDATE notifications SET is_read = 1 
              WHERE id = '$notif_id' AND $column = '$current_id'";
    mysqli_query($conn, $query);

    if ($role === 'shelter') {
        header("Location: /try/Shelter-Member/Shelter_member_view.php?id=" . $pet_id);
    } else {
        header("Location: /try/PetDetails/PetDetails.php?id=" . $pet_id);
    }
    exit();

} else {
    header("Location: /try/index.php");
    exit();
}
?>