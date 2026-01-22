<?php
session_start();
include "../db/db.php";
include "../Password_hased.php"; 


if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_user_email'])) {
    $cookie_email = mysqli_real_escape_string($conn, $_COOKIE['remember_user_email']);
    $cookie_sql = "SELECT * FROM users WHERE email = '$cookie_email' LIMIT 1";
    $cookie_res = mysqli_query($conn, $cookie_sql);
    
    if ($user = mysqli_fetch_assoc($cookie_res)) {
       
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['username'];
        $_SESSION['role'] = ($user['is_admin'] == 1) ? 'admin' : (($user['is_shelter_member'] == 1) ? 'shelter' : 'member');
        header("Location: ../index.php");
        exit();
    }
}


if (isset($_POST['login_btn'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password_input = $_POST['password'];


    $sql = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        
        if (verify_password($password_input, $user['password'])) {
            
            
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['username'];

            
            if (isset($_POST['remember_me'])) {
                $expiry = time() + (30 * 24 * 60 * 60); 
                setcookie("remember_user_email", $email, $expiry, "/");
            } else {
               
                setcookie("remember_user_email", "", time() - 3600, "/");
            }

       
            if (!empty($user['is_admin']) && $user['is_admin'] == 1) {
                $_SESSION['role'] = 'admin';
                header("Location: ../index.php");
                exit();
            } 
            elseif ($user['is_shelter_member'] == 1) {
                if ($user['is_approved'] == 1) {
                    $_SESSION['role'] = 'shelter';
                    $_SESSION['shelter_id'] = $user['shelter_id'];
                    header("Location: ../Shelter-member/Shelter_member.php");
                    exit();
                } else {
                    $_SESSION['error_msg'] = "Your shelter account is pending admin approval.";
                 
                    header("Location: login.php");
                    exit();
                }
            } 
            else {
                $_SESSION['role'] = 'member';
                header("Location: ../index.php");
                exit();
            }
        } else {
            $_SESSION['error_msg'] = "Invalid Email or Password!";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['error_msg'] = "Invalid Email or Password!";
        header("Location: login.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | PetAdopt</title>
    <link rel="stylesheet" href="./Login.css">
</head>
<body>
    <div class="login-card">
        <h2>Login</h2>
        
        <?php if(isset($_SESSION['error_msg'])): ?>
            <div style="background-color: #fee2e2; color: #b91c1c; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; border: 1px solid #fecaca; font-size: 0.9rem;">
                <?php 
                    echo $_SESSION['error_msg']; 
                    unset($_SESSION['error_msg']); 
                ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="Enter your email" 
                       value="<?php echo isset($_COOKIE['remember_user_email']) ? htmlspecialchars($_COOKIE['remember_user_email']) : ''; ?>" required>
            </div>

            <div class="input-group" id="password-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter password" required>
                
            </div>

            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 20px;">
                <input type="checkbox" name="remember_me" id="remember" <?php echo isset($_COOKIE['remember_user_email']) ? 'checked' : ''; ?>>
                <label for="remember" style="font-size: 0.85rem; color: #666; cursor: pointer;">Remember me for 30 days</label>
            </div>

            <button type="submit" name="login_btn" class="login-btn">Log In</button>
        </form>
        
        <p style="margin-top: 20px; font-size: 0.9rem; text-align: center;">
            Don't have an account? <a href="../registration/registration.php" style="color: #3498db; text-decoration: none; font-weight: bold;">Register here</a>
        </p>
    </div>
</body>
</html>