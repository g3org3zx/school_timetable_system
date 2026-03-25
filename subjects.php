<?php 
include "config/db.php"; 
include "header.php"; 

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_subject'])) {
    $subject_name = trim($_POST['subject_name']);
    $subject_code = trim($_POST['subject_code']);
    
    $errors = [];
    
    if (empty($subject_name)) $errors[] = "Subject name is required!";
    if (empty($subject_code)) $errors[] = "Subject code is required!";
    
    if (!empty($errors)) {
        foreach ($errors as $err) {
            echo "<p class='error'>$err</p>";
        }
    } else {
        try {
            $sql = "INSERT INTO subjects (subject_name, subject_code) VALUES (?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $subject_name, $subject_code);
            
            if (mysqli_stmt_execute($stmt)) {
                echo "<p class='success'>✅ Subject added successfully!</p>";
            } else {
                throw new Exception(mysqli_error($conn));
            }
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            $errorCode = mysqli_errno($conn);
            
            if ($errorCode === 1062) {
                if (strpos($errorMsg, 'unique_subject_code') !== false) {
                    echo "<p class='error'>❌ This subject code already exists!</p>";
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

<h1>Manage Subjects</h1>

<h2>Add New Subject</h2>
<form method="post" style="max-width:500px; margin-bottom:40px;">
    <p>
        <label>Subject Name <span style="color:red;">*</span> (e.g. Mathematics, Biology)</label><br>
        <input type="text" name="subject_name" required style="width:100%; padding:8px;">
    </p>
    <p>
        <label>Subject Code <span style="color:red;">*</span> (e.g. MAT, BIO, ENG)</label><br>
        <input type="text" name="subject_code" required style="width:100%; padding:8px;" placeholder="Must be unique">
    </p>
    <button type="submit" name="add_subject" style="padding:10px 20px; background:#3498db; color:white; border:none; cursor:pointer;">
        Add Subject
    </button>
</form>

<h2>Current Subjects</h2>
<?php
$result = mysqli_query($conn, "SELECT * FROM subjects ORDER BY subject_code");
if (mysqli_num_rows($result) > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse:collapse; width:100%; background:white;'>";
    echo "<tr style='background:#ecf0f1;'><th>Subject Code</th><th>Subject Name</th><th>Added On</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($row['subject_code']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($row['subject_name']) . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No subjects added yet.</p>";
}
?>

<?php include "footer.php"; ?>