<?php
// Database configuration
$servername = "localhost";
$username = "";  // Your database username
$password = "";      // Your database password
$dbname = "dbms";  // Your database name

try {
    // Create a PDO connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // You can add this line to confirm connection
    // echo "Connected successfully"; 
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
// PDO connection
$host = 'localhost';
$user = '';
$password = '';
$db = 'elderly_care';

try {
    $connection = new PDO("mysql:host=$host;dbname=$db", $user, $password);
    // Set the PDO error mode to exception
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
