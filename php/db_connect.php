<?php
$servername = "localhost";
$username = "root";
$password = ""; // Your XAMPP password (usually blank)
$dbname = "imlibrary"; // From your schema.sql file

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>