<?php 
include "config/db.php"; 
include "header.php"; 

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_teacher'])) {
    $staff_id = trim($_POST['staff_id']);
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $subject  = trim($_POST['subject']);
    
    $errors = [];
    
    if (empty($staff_id)) {
        $errors[] = "Staff ID is required!";
    }
    if (empty($name)) {
        $errors[] = "Full name is required!";
    }
    
    if (!empty($errors)) {
        foreach ($errors as $err) {
            echo "<p class='error'>$err</p>";
        }
    } else {
        $sql = "INSERT INTO teachers (staff_id, name, email, subject) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssss", $staff_id, $name, $email, $subject);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<p class='success'>Teacher added successfully! ✅</p>";
        } else {
            $error = mysqli_error($conn);
            if (strpos($error, "Duplicate entry") !== false) {
                if (strpos($error, "unique_staff_id") !== false) {
                    echo "<p class='error'>This Staff ID already exists!</p>";
                } elseif (strpos($error, "unique_email") !== false) {
                    echo "<p class='error'>This email is already used by another teacher!</p>";
                } else {
                    echo "<p class='error'>Duplicate entry error.</p>";
                }
            } else {
                echo "<p class='error'>Error: " . $error . "</p>";
            }
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<h1>Manage Teachers</h1>

<!-- Add Teacher Form -->
<h2>Add New Teacher</h2>
<form method="post" style="max-width:500px; margin-bottom:40px;">
    <p>
        <label>Staff ID <span style="color:red;">*</span></label><br>
        <input type="text" name="staff_id" required placeholder="e.g. T001 or ST/2025/123" style="width:100%; padding:8px;">
    </p>
    <p>
        <label>Full Name <span style="color:red;">*</span></label><br>
        <input type="text" name="name" required style="width:100%; padding:8px;">
    </p>
    <p>
        <label>Email <span style="color:red;">*</span> (must be unique)</label><br>
        <input type="email" name="email" required style="width:100%; padding:8px;">
    </p>
    <p>
        <label>Main Subject</label><br>
        <input type="text" name="subject" style="width:100%; padding:8px;">
    </p>
    <button type="submit" name="add_teacher" style="padding:10px 20px; background:#3498db; color:white; border:none; cursor:pointer;">
        Add Teacher
    </button>
</form>

<!-- List all teachers -->
<h2>Current Teachers</h2>

<?php
$result = mysqli_query($conn, "SELECT * FROM teachers ORDER BY staff_id");
if (mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse:collapse; width:100%;'>";
    echo "<tr style='background:#ecf0f1;'>
            <th>Staff ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Subject</th>
            <th>Added</th>
          </tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['staff_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email'] ?: '-') . "</td>";
        echo "<td>" . htmlspecialchars($row['subject'] ?: '-') . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No teachers added yet.</p>";
}
?>

<?php include "footer.php"; ?>