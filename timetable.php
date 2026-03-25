<?php 
include "config/db.php"; 
include "header.php"; 

// Handle form submission with conflict detection
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_entry'])) {
    $class_id   = (int)$_POST['class_id'];
    $subject_id = (int)$_POST['subject_id'];
    $teacher_id = (int)$_POST['teacher_id'];
    $room_id    = (int)$_POST['room_id'];
    $day        = $_POST['day_of_week'];
    $start      = $_POST['start_time'];
    $end        = $_POST['end_time'];

    // === CONFLICT CHECKS ===
    $conflicts = [];

    // 1. Teacher conflict
    $sql = "SELECT * FROM timetable 
            WHERE teacher_id = ? 
            AND day_of_week = ? 
            AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?))";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isssss", $teacher_id, $day, $end, $start, $start, $end);
    mysqli_stmt_execute($stmt);
    if (mysqli_stmt_get_result($stmt)->num_rows > 0) {
        $conflicts[] = "Teacher is already scheduled at this time on " . $day;
    }
    mysqli_stmt_close($stmt);

    // 2. Room conflict
    $sql = "SELECT * FROM timetable 
            WHERE room_id = ? 
            AND day_of_week = ? 
            AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?))";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isssss", $room_id, $day, $end, $start, $start, $end);
    mysqli_stmt_execute($stmt);
    if (mysqli_stmt_get_result($stmt)->num_rows > 0) {
        $conflicts[] = "Room is already booked at this time on " . $day;
    }
    mysqli_stmt_close($stmt);

    // 3. Class conflict
    $sql = "SELECT * FROM timetable 
            WHERE class_id = ? 
            AND day_of_week = ? 
            AND ((start_time < ? AND end_time > ?) OR (start_time < ? AND end_time > ?))";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isssss", $class_id, $day, $end, $start, $start, $end);
    mysqli_stmt_execute($stmt);
    if (mysqli_stmt_get_result($stmt)->num_rows > 0) {
        $conflicts[] = "Class already has a lesson at this time on " . $day;
    }
    mysqli_stmt_close($stmt);

    if (!empty($conflicts)) {
        echo "<p class='error'><strong>Conflict detected!</strong><br>";
        foreach ($conflicts as $c) {
            echo "• " . $c . "<br>";
        }
        echo "</p>";
    } else {
        // No conflicts → insert
        try {
            $sql = "INSERT INTO timetable 
                    (class_id, subject_id, teacher_id, room_id, day_of_week, start_time, end_time) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "iiiisss", $class_id, $subject_id, $teacher_id, $room_id, $day, $start, $end);
            
            if (mysqli_stmt_execute($stmt)) {
                echo "<p class='success'>✅ Lesson added successfully! No conflicts.</p>";
            } else {
                throw new Exception(mysqli_error($conn));
            }
            mysqli_stmt_close($stmt);
        } catch (Exception $e) {
            echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}
?>

<h1>Timetable Management</h1>

<h2>Add New Lesson</h2>
<form method="post" style="max-width:700px; margin-bottom:50px; background:#fff; padding:20px; border-radius:8px;">
    <!-- Same form as before (class, subject, teacher, room, day, start time, end time) -->
    <p>
        <label>Class <span style="color:red;">*</span></label><br>
        <select name="class_id" required style="width:100%; padding:8px;">
            <option value="">Select Class</option>
            <?php
            $res = mysqli_query($conn, "SELECT id, class_name, class_code FROM classes ORDER BY class_code");
            while ($row = mysqli_fetch_assoc($res)) {
                echo "<option value='{$row['id']}'>{$row['class_code']} - {$row['class_name']}</option>";
            }
            ?>
        </select>
    </p>

    <p>
        <label>Subject <span style="color:red;">*</span></label><br>
        <select name="subject_id" required style="width:100%; padding:8px;">
            <option value="">Select Subject</option>
            <?php
            $res = mysqli_query($conn, "SELECT id, subject_name, subject_code FROM subjects ORDER BY subject_code");
            while ($row = mysqli_fetch_assoc($res)) {
                echo "<option value='{$row['id']}'>{$row['subject_code']} - {$row['subject_name']}</option>";
            }
            ?>
        </select>
    </p>

    <p>
        <label>Teacher <span style="color:red;">*</span></label><br>
        <select name="teacher_id" required style="width:100%; padding:8px;">
            <option value="">Select Teacher</option>
            <?php
            $res = mysqli_query($conn, "SELECT id, staff_id, name FROM teachers ORDER BY staff_id");
            while ($row = mysqli_fetch_assoc($res)) {
                echo "<option value='{$row['id']}'>{$row['staff_id']} - {$row['name']}</option>";
            }
            ?>
        </select>
    </p>

    <p>
        <label>Room <span style="color:red;">*</span></label><br>
        <select name="room_id" required style="width:100%; padding:8px;">
            <option value="">Select Room</option>
            <?php
            $res = mysqli_query($conn, "SELECT id, room_code, room_name FROM rooms ORDER BY room_code");
            while ($row = mysqli_fetch_assoc($res)) {
                echo "<option value='{$row['id']}'>{$row['room_code']} - {$row['room_name']}</option>";
            }
            ?>
        </select>
    </p>

    <p>
        <label>Day of Week <span style="color:red;">*</span></label><br>
        <select name="day_of_week" required style="width:100%; padding:8px;">
            <option value="">Select Day</option>
            <option value="Monday">Monday</option>
            <option value="Tuesday">Tuesday</option>
            <option value="Wednesday">Wednesday</option>
            <option value="Thursday">Thursday</option>
            <option value="Friday">Friday</option>
        </select>
    </p>

    <div style="display:flex; gap:20px;">
        <p style="flex:1;">
            <label>Start Time <span style="color:red;">*</span></label><br>
            <input type="time" name="start_time" required style="width:100%; padding:8px;">
        </p>
        <p style="flex:1;">
            <label>End Time <span style="color:red;">*</span></label><br>
            <input type="time" name="end_time" required style="width:100%; padding:8px;">
        </p>
    </div>

    <button type="submit" name="add_entry" style="padding:12px 30px; background:#27ae60; color:white; border:none; cursor:pointer; font-size:16px;">
        Add to Timetable
    </button>
</form>

<h2>Current Timetable</h2>
<?php
$result = mysqli_query($conn, "
    SELECT t.*, 
           c.class_code, c.class_name,
           s.subject_code, s.subject_name,
           te.staff_id, te.name as teacher_name,
           r.room_code, r.room_name
    FROM timetable t
    LEFT JOIN classes c ON t.class_id = c.id
    LEFT JOIN subjects s ON t.subject_id = s.id
    LEFT JOIN teachers te ON t.teacher_id = te.id
    LEFT JOIN rooms r ON t.room_id = r.id
    ORDER BY FIELD(t.day_of_week, 'Monday','Tuesday','Wednesday','Thursday','Friday'), t.start_time
");

if (mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse:collapse; width:100%; background:white;'>";
    echo "<tr style='background:#ecf0f1;'>
            <th>Day</th><th>Time</th><th>Class</th><th>Subject</th><th>Teacher</th><th>Room</th>
          </tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td><strong>" . $row['day_of_week'] . "</strong></td>";
        echo "<td>" . $row['start_time'] . " - " . $row['end_time'] . "</td>";
        echo "<td>" . $row['class_code'] . " " . $row['class_name'] . "</td>";
        echo "<td>" . $row['subject_code'] . " " . $row['subject_name'] . "</td>";
        echo "<td>" . $row['staff_id'] . " " . $row['teacher_name'] . "</td>";
        echo "<td>" . $row['room_code'] . " " . $row['room_name'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No timetable entries yet.</p>";
}
?>

<?php include "footer.php"; ?>