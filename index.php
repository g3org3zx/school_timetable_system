<?php include "config/db.php"; ?>
<?php include "header.php"; ?>

<h1>Welcome to the School Timetable System</h1>

<?php if ($conn): ?>
    <p class="success">✅ Database is connected and ready.</p>
    
    <ul style="font-size:1.2em; line-height:1.8;">
        <li>Teachers in system: <strong><?php 
            $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM teachers"); 
            $row = mysqli_fetch_assoc($r); 
            echo $row['c']; 
        ?></strong></li>
        
        <li>Classes in system: <strong><?php 
            $r = mysqli_query($conn, "SELECT COUNT(*) as c FROM classes"); 
            $row = mysqli_fetch_assoc($r); 
            echo $row['c']; 
        ?></strong></li>
    </ul>
    
    <p>Start by going to <a href="teachers.php">Manage Teachers</a></p>
<?php else: ?>
    <p class="error">❌ Cannot connect to database. Check config/db.php</p>
<?php endif; ?>

<?php include "footer.php"; ?>