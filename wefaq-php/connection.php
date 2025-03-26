<?php 
$host = "localhost";
$username = "root";
$password = "root";
$database = "wefaq_database";

$conn = mysqli_connect($host, $username, $password, $database);
if($conn){
    echo "<script>alert('Connected successfully')</script>";
}
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
