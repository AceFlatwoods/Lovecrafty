<?php
$host = 'localhost';
$dbname = 'lovecrafty';
$username = 'root';
$password = '';
$port = 3307; // Add this line to specify the port

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
} // COMO NOTA ESTO ES PARA MI LAPTOP, TODAS LAS DEMAS DEBERIAN DE FUNCIONAR CON EL DE ABAJO
?>

<?php
// $host = 'localhost';
// $dbname = 'lovecrafty';
// $username = 'root';
// $password = '';

// try {
//     $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
//     $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// } catch (PDOException $e) {
//     die("Database connection failed: " . $e->getMessage());
// }
?>