<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Delete Employee Record</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
<nav>
    <ul>

    <li><a href="index.php">Home</a></li>
    <li><a href="view.php">View</a></li>
    <li><a href="update.php">Update</a></li>
    <li><a href="deletion.php">Delete</a></li>

    </ul>
    </nav>

<form method="POST">
<h2>Delete Employee Record</h2>
  <input type="text" id="ename" name="ename" required placeholder = "Employee name"> <br> <br>


  <input type="text" id="position" name="position" required placeholder = "Position"> <br> <br>

  <input type="submit" name="delete" value="Delete Record"> <br> <br>
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete"])) {
    $ename = trim($_POST["ename"]);
    $position = trim($_POST["position"]);

    // Connect to the DB
    $conn = mysqli_connect("localhost", "root", "", "nssfdb");

    if (!$conn) {
        die("<p class='message error'>Database connection failed: " . mysqli_connect_error() . "</p>");
    }

    // First, check if the employee exists with that name and position
    $check_sql = "SELECT * FROM employee_records WHERE Ename = ? AND position = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("ss", $ename, $position);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Proceed to delete
        $delete_sql = "DELETE FROM employee_records WHERE Ename = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("s", $ename);
        $delete_stmt->execute();

        if ($delete_stmt->affected_rows > 0) {
            echo "<script>alert('Record for $ename deleted successfully')</script>";
        } else {
            echo "<script>alert('Failed to delete record. Please try again')</script>";

        }

        $delete_stmt->close();
    } else {
        echo "<script>alert('No record found with that name and position')</script>";
    }

    $stmt->close();
    mysqli_close($conn);
}
?>

</body>
</html>
