<?php //Cosito para checar si hay conexion
$db_server = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "lovecrafty";

// Create connection
$conn = mysqli_connect($db_server,$db_user,$db_pass,$db_name);

// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}
echo "Connected successfully";
?>