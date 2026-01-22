<?php
include "../db/db.php"; 

session_start();

$sql = "SELECT * FROM locations";


$result = mysqli_query($conn, $sql);

$all_locations = [];

if (mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
   
    $all_locations[] = [
        "id"   => $row['id'],
        "name" => $row['name']
    ];
}

}
$SuccessMessage = '';


if (isset($_POST['register_member'])) {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $phone = $_POST['phone'];
    $occupation = $_POST['occupation'];
    $location_id = $_POST['location_id'];
    $Home_address = $_POST['home_address'];
    $gender = $_POST['gender']; 

    
    $query1 = "INSERT INTO users (full_name, username, email, password, phone_number, occupation, location_id, home_address, gender,is_shelter_member,is_admin,shelter_id) VALUES ('$fullname', '$username', '$email', '$password', '$phone', '$occupation', '$location_id', '$Home_address', '$gender',0,0,NULL)";
    mysqli_query($conn, $query1);
    $_SESSION['message'] = " Member Registered Successfully!";
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    

    
}


if (isset($_POST['register_shelter_member'])) {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $location_id = $_POST['location_id'];
    $shelter_id = $_POST['shelter_id'];
    $phone = $_POST['phone'];
  
    $Home_address = $_POST['home_address'];
    $gender = $_POST['gender'];


     1. Insert into shelter_members table
    $query1 = "INSERT INTO sheltermember (full_name, username, email, password, phone_number, occupation, location, shelter_name, home_address, gender) 
               VALUES ('$fullname', '$username', '$email', '$password', '$phone', 'Shelter Member', '$location_id', '$shelter_id', '$Home_address', '$gender')";
    mysqli_query($conn, $query1);

        $query2 = "INSERT INTO users (full_name, username, email, password, phone_number, occupation, location_id, home_address, gender,is_shelter_member,is_admin,shelter_id) VALUES ('$fullname', '$username', '$email', '$password', '$phone', 'Shelter Member', '$location_id', '$Home_address', '$gender',1,0,$shelter_id)"; 
                    mysqli_query($conn, $query2);
                     $_SESSION['message'] = " Shelter Member Registered Successfully! please wait for admin approval.    ";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit(); }


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Page</title>
    <link rel="stylesheet" href="../registration/registration.css">
</head>
<body>

    <div class="login-page" id="mainWrapper">
        
        <div id="imageSection" class="image-section member ">
            <a href="/">isre</a>
        </div>

        <div class="form-section">
            <div class="form-container">
                
                <div class="toggle-container">
                    <button class="toggle-btn active" id="btnMember" >Member</button>
                    <button class="toggle-btn " id="btnShelter" >Shelter Member</button>
                </div>

                <h2 id="displayTitle" style="display: block;">Create Member Account</h2>
                <h2 id="displayTitle2" style="display: none;">Register as Shelter Member</h2>
                <p id="displayDesc">Join our community to help and adopt pets.</p>
                <?php $SuccessMessage = isset($_SESSION['message']) ? $_SESSION['message'] : '';?>
              
                <form id="regForm-member" method="POST" style="display: block;">
                        <div class="input-grid">
                            <div class="input-group">
                                <label>Full Name</label>
                                <input type="text" name="fullname" placeholder="John Doe" required>
                            </div>
                            <div class="input-group">
                                <label>Username</label>
                                <input type="text" name="username" placeholder="johndoe123" required>
                            </div>
                            <div class="input-group">
                                <label>Email</label>
                                <input type="email" name="email" placeholder="name@example.com" required>
                            </div>
                            <div class="input-group">
                                <label>Password</label>
                                <input type="password" name="password" placeholder="••••••••" required>
                            </div>
                            <div class="input-group">
                                <label>Phone Number</label>
                                <input type="tel" name="phone" placeholder="+1 234 567 890">
                            </div>
                            <div class="input-group">
                                <label>Occupation</label>
                                <input type="text" name="occupation" placeholder="e.g. Doctor">
                            </div>
                            <div class="input-group">
                                <label>Location</label>
                                <select name="location_id" required>
                                    <option value="" disabled selected>Select City</option>
                                    <?php foreach ($all_locations as $loc): ?>
                                        <option value="<?php echo $loc['id']; ?>"><?php echo $loc['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="input-group full-width">
                            <label>Home Address</label>
                            <input type="text" name="home_address" placeholder="123 Street, City, Country">
                        </div>
                        <div class="input-group full-width">
                            <label>Gender</label>
                            <div class="radio-group">
                                <label><input type="radio" name="gender" value="male"> Male</label>
                                <label><input type="radio" name="gender" value="female"> Female</label>
                                <label><input type="radio" name="gender" value="other"> Other</label>
                            </div>
                        </div>
                        <button type="submit" name="register_member" class="submit-btn Member">Register as Member</button>
</form>




               <form id="regForm-shelter" method="POST" style="display: none;">
            <div class="input-grid">
                <div class="input-group">
                    <label>Full Name</label>
                    <input type="text" name="fullname" required>
                </div>
                <div class="input-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="input-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <div class="input-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone">
                </div>
                <div class="input-group">
                    <label>Location</label>
                    <select name="location_id" id="locationSelect" required>
                        <option value="" disabled selected>Select City</option>
                        <?php foreach ($all_locations as $loc): ?>
                            <option value="<?php echo $loc['id']; ?>"><?php echo $loc['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="input-group">
                    <label>Shelter Name</label>
                    <select name="shelter_id" id="shelterSelect" required>
                        <option value="" disabled selected>Select a city first</option>
                    </select>
                </div>
            </div>
            <div class="input-group full-width">
                <label>Home Address</label>
                <input type="text" name="home_address">
            </div>
            <div class="input-group full-width">
                <label>Gender</label>
                <div class="radio-group">
                    <label><input type="radio" name="gender" value="male"> Male</label>
                    <label><input type="radio" name="gender" value="female"> Female</label>
                    <label><input type="radio" name="gender" value="other"> Other</label>
                </div>
            </div>
            <button type="submit" name="register_shelter_member" class="submit-btn Shelter-member">Register as Shelter Member</button>
</form>



                    <div class="AlreadyLink">
                        
                        <p >Already have an account? <a href="../Login/Login.php">Login</a></p>
                    </div>
                
            </div>
        </div>
    </div>

    <script src="../registration/registration.js"></script>
</body>
</html>