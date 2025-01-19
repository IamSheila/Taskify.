<?php
// Database connection configuration

$host = 'localhost';    // MySQL host (use 'localhost' if running on local server like XAMPP/WAMP)
$db   = 'db_taskmanager'; // Name of your database
$user = 'root';          // MySQL username (default for XAMPP/WAMP is 'root')
$pass = '';              // MySQL password (default for XAMPP/WAMP is an empty string)

// Try to establish a PDO (PHP Data Object) connection to MySQL
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    // Set the PDO error mode to exception for error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // If connection fails, catch and display the error message
    echo 'Connection failed: ' . $e->getMessage();
    exit();  // Stop the script if the connection fails
}
?>
