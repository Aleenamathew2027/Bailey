<?php
$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "Beauty";

// Step 1: Connect to MySQL (without selecting DB yet)
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

