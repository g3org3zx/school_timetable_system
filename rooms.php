<?php 
include "config/db.php"; 
include "header.php"; 

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_room'])) {
    $room_name = trim($_POST['room_name']);
    $room_code = trim($_POST['room_code']);
    $capacity  = (int)$_POST['capacity'];
    
    $errors = [];
    
    if (empty($room_name)) $errors[] = "Room name is required!";
    if (empty($room_code)) $errors[] = "Room code is required!";
    
    if (!empty($errors)) {
        foreach ($errors as $err) {
            echo "<p class='error'>$err</p>";
        }
    } else {
        try {
            $sql = "INSERT INTO rooms (room_name, room_code, capacity) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssi", $room_name, $room_code, $capacity);
            
            if (mysqli_stmt_execute($stmt)) {
                echo "<p class='success'>✅ Room added successfully!</p>";
            } else {
                throw new Exception(mysqli_error($conn));
            }
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            $errorCode = mysqli_errno($conn);
            
            if ($errorCode === 1062) {
                if (strpos($errorMsg, 'unique_room_code') !== false) {
                    echo "<p class='error'>❌ This room code already exists!</p>";
                } else {
                    echo "<p class='error'>❌ Duplicate entry detected.</p>";
                }
            } else {
                echo "<p class='error'>❌ Database error: " . htmlspecialchars($errorMsg) . "</p>";
            }
        }
        
        if (isset($stmt)) mysqli_stmt_close($stmt);
    }
}
?>

<h1>Manage Rooms</h1>

<h2>Add New Room</h2>
<form method="post" style="max-width:500px; margin-bottom:40px;">
    <p>
        <label>Room Name <span style="color:red;">*</span> (e.g. Classroom 101, Science Lab)</label><br>
        <input type="text" name="room_name" required style="width:100%; padding:8px;">
    </p>
    <p>
        <label>Room Code <span style="color:red;">*</span> (e.g. R101, LAB-3)</label><br>
        <input type="text" name="room_code" required style="width:100%; padding:8px;" placeholder="Must be unique">
    </p>
    <p>
        <label>Capacity (number of students)</label><br>
        <input type="number" name="capacity" min="1" value="30" style="width:100%; padding:8px;">
    </p>
    <button type="submit" name="add_room" style="padding:10px 20px; background:#3498db; color:white; border:none; cursor:pointer;">
        Add Room
    </button>
</form>

<h2>Current Rooms</h2>
<?php
$result = mysqli_query($conn, "SELECT * FROM rooms ORDER BY room_code");
if (mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse:collapse; width:100%; background:white;'>";
    echo "<tr style='background:#ecf0f1;'><th>Room Code</th><th>Room Name</th><th>Capacity</th><th>Added On</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($row['room_code']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($row['room_name']) . "</td>";
        echo "<td>" . $row['capacity'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No rooms added yet.</p>";
}
?>

<?php include "footer.php"; ?>