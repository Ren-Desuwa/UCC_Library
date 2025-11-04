<?php

// 1. Load the composer autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

// 2. Point to the 'main' folder where your .env file is
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../main/');
$dotenv->load();

// 3. --- THIS IS THE FIX ---
//    Use the $_ENV superglobal instead of getenv()
$servername = $_ENV['DB_HOST'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$dbname = $_ENV['DB_NAME'];
// --- END FIX ---

$port = 3306; // Default MySQL port

// 4. Create connection (This is line 16)
$conn = new mysqli($servername, $username, $password, $dbname, $port);  

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>