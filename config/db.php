<?php
$host     = "localhost";
$user     = "root";           // XAMPP default
$password = "";               // XAMPP default — empty
$database = "school_timetable_system";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// For testing only — remove or comment this line later
// echo "Connected to database successfully!";
?>