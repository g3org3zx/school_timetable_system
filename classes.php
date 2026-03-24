<?php 
include "config/db.php"; 
include "header.php"; 

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_class'])) {
    $class_name = trim($_POST['class_name']);
    $class_code = trim($_POST['class_code']);
    
    $errors = [];
    
    if (empty($class_name)) $errors[] = "Class name is required!";
    if (empty($class_code)) $errors[] = "Class code is required!";
    
    if (!empty($errors)) {
        foreach ($errors as $err) {
            echo "<p class='error'>$err</p>";
        }
    } else {
        try {
            $sql = "INSERT INTO classes (class_name, class_code) VALUES (?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $class_name, $class_code);
            
            if (mysqli_stmt_execute($stmt)) {
                echo "<p class='success'>✅ Class added successfully!</p>";
            } else {
                throw new Exception(mysqli_error($conn));
            }
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            $errorCode = mysqli_errno($conn);
            
            if ($errorCode === 1062) {
                if (strpos($errorMsg, 'unique_class_code') !== false) {
                    echo "<p class='error'>❌ This class code already exists!</p>";
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

<h1>Manage Classes</h1>

<h2>Add New Class</h2>
<form method="post" style="max-width:500px; margin-bottom:40px;">
    <p>
        <label>Class Name <span style="color:red;">*</span> (e.g. Form 3, Grade 10)</label><br>
        <input type="text" name="class_name" required style="width:100%; padding:8px;">
    </p>
    <p>
        <label>Class Code <span style="color:red;">*</span> (e.g. 3A, 4B, F1A)</label><br>
        <input type="text" name="class_code" required style="width:100%; padding:8px;" placeholder="Must be unique">
    </p>
    <button type="submit" name="add_class" style="padding:10px 20px; background:#3498db; color:white; border:none; cursor:pointer;">
        Add Class
    </button>
</form>

<h2>Current Classes</h2>
<?php
$result = mysqli_query($conn, "SELECT * FROM classes ORDER BY class_code");
if (mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse:collapse; width:100%; background:white;'>";
    echo "<tr style='background:#ecf0f1;'><th>Class Code</th><th>Class Name</th><th>Added On</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($row['class_code']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($row['class_name']) . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No classes added yet.</p>";
}
?>

<?php include "footer.php"; ?>