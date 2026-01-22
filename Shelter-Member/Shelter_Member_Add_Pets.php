<?php
session_start();
include "../db/db.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'shelter') {
    die("Unauthorized access. Shelter members only.");
}

$shelter_id = $_SESSION['shelter_id'];
$user_id = $_SESSION['user_id'];

$shelter_sql = "SELECT s.shelter_name, u.full_name, u.email, u.phone_number 
                FROM shelter s
                JOIN users u ON u.user_id = '$user_id'
                WHERE s.shelter_id = '$shelter_id' 
                LIMIT 1";

$shelter_result = mysqli_query($conn, $shelter_sql);

if ($shelter_result && mysqli_num_rows($shelter_result) > 0) {
    $details = mysqli_fetch_assoc($shelter_result);
    $my_shelter_name = $details['shelter_name'];
    $my_member_name = $details['full_name'];
    $my_member_email = $details['email'];
} else {

    $my_shelter_name = "Unknown Shelter";
    $my_member_name = "Member";
}



if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_pet'])) {

    $pet_name = mysqli_real_escape_string($conn, $_POST['pet_name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $breed = mysqli_real_escape_string($conn, $_POST['breed']);
    $age = mysqli_real_escape_string($conn, $_POST['age']);
    $color = mysqli_real_escape_string($conn, $_POST['color']);
    $behaviour = mysqli_real_escape_string($conn, $_POST['behaviour']);
    $is_vax = (int) $_POST['is_vaccinated'];

    $base64_final = "";
    if (!empty($_FILES['pet_photo']['tmp_name'])) {
        $imageData = file_get_contents($_FILES['pet_photo']['tmp_name']);

        $base64 = 'data:' . $_FILES['pet_photo']['type'] . ';base64,' . base64_encode($imageData);

        $base64_final = mysqli_real_escape_string($conn, $base64);
    }


    $sql = "INSERT INTO pets (
                pet_name, category, breed, age, behaviour, color, 
                pet_photo, shelter_id, is_vaccinated, adoption_status, request_status
            ) VALUES (
                '$pet_name', '$category', '$breed', '$age', '$behaviour', '$color', 
                '$base64_final', '$shelter_id', $is_vax, 'Available', 'None'
            )";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Pet added successfully!'); window.location='Shelter_Member.php';</script>";
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Pet | Shelter Dashboard</title>
    <link rel="stylesheet" href="./Shelter_Member_Add_Pets.css">
</head>

<body>

    <div class="container">
        <h2>Add New Pet to Inventory</h2>

        <form action="" method="POST" enctype="multipart/form-data">

            <div class="system-info">
                <div class="form-group">
                    <label>Shelter ID</label>
                    <input type="text" value="<?php echo $shelter_id; ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Shelter Name</label>
                    <input type="text" value="<?php echo htmlspecialchars($my_shelter_name); ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Member ID</label>
                    <input type="text" value="<?php echo $user_id; ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Adding By</label>
                    <input type="text" value="<?php echo htmlspecialchars($my_member_name); ?>" readonly>
                </div>
            </div>

            <div class="form-group">
                <label>Pet Name</label>
                <input type="text" name="pet_name" required placeholder="Enter pet name">
            </div>

            <div style="display: flex; gap: 10px;">
                <div class="form-group" style="flex: 1;">
                    <label>Category</label>
                    <select name="category">
                        <option value="Dog">Dog</option>
                        <option value="Cat">Cat</option>
                        <option value="Bird">Bird</option>
                    </select>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Breed</label>
                    <input type="text" name="breed" placeholder="e.g. Husky">
                </div>
            </div>

            <div style="display: flex; gap: 10px;">
                <div class="form-group" style="flex: 1;">
                    <label>Age</label>
                    <input type="text" name="age" placeholder="e.g. 2 Months">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Color</label>
                    <input type="text" name="color" placeholder="e.g. White">
                </div>
            </div>

            <div class="form-group">
                <label>Vaccinated?</label>
                <select name="is_vaccinated">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>

            <div class="form-group">
                <label>Behaviour</label>
                <textarea name="behaviour" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label>Pet Photo</label>
                <input type="file" name="pet_photo" accept="image/*" required onchange="preview(this)">
                <div class="preview-box">
                    <img id="imgPreview" src="#" style="display:none;">
                    <span id="text">Preview</span>
                </div>
            </div>

            <button type="submit" name="submit_pet" class="btn-submit">Add Pet</button>
        </form>
    </div>

    <script src="./Shelter_Member_Add_Pets.js"></script>

</body>

</html>