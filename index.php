<?php
include "config/db.php";

echo "<h1 style='text-align:center; color:#2c3e50;'>School Timetable System</h1>";
echo "<div style='text-align:center; font-size:1.3em; margin:30px;'>";

if ($conn) {
    echo "<p style='color:green; font-weight:bold;'>✅ Database connection successful!</p>";
    
    // Quick proof: count rows in teachers table
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM teachers");
    $row = mysqli_fetch_assoc($result);
    echo "<p>Teachers in database right now: <strong>" . $row['total'] . "</strong></p>";
    
    // Same for other tables (just for fun)
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM classes");
    $row = mysqli_fetch_assoc($result);
    echo "<p>Classes in database: <strong>" . $row['total'] . "</strong></p>";
} else {
    echo "<p style='color:red; font-weight:bold;'>❌ Connection failed!</p>";
}

echo "</div>";
?>