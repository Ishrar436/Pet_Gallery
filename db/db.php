<?php
$host = "localhost";
$user = "root";
$pass = "12345";
$db   = "rifatpetgallery";


$conn = mysqli_connect($host, $user, $pass, $db);


if (!$conn) {
    echo("Connection failed: " . mysqli_connect_error());
}

?>
