<?php
$servername = "localhost";
$username = "root";
$password = "pass123"; // Your XAMPP password (usually blank)
$dbname = "imlibrary"; // From your schema.sql file
$port = 3307;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);  

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>