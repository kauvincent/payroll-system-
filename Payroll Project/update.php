<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Update | Payroll</title>
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

  <form action="update.php" method="POST">
    <h2>Update Record</h2>
    <input type="text" name="employee_name" required placeholder="Employee Name"><br><br>
    <input type="text" name="address" required placeholder="Address"><br><br>
    <input type="date" name="dob" required><br><br>
    <input type="text" name="contact" required placeholder="Contact"><br><br>
    <input type="email" name="email" required placeholder="Email address"><br><br>

    <select name="position" required>
      <option value="" selected disabled>Select your position</option>
      <option value="Manager">Manager</option>
      <option value="Treasurer">Treasurer</option>
      <option value="Human Resource">Human Resource</option>
      <option value="Technician">Technician</option>
    </select><br><br>

    <input type="radio" name="gender" value="Male" required> Male
    <input type="radio" name="gender" value="Female"> Female<br><br>

    <input type="submit" value="Enter"><br><br>
  </form>
</body>
</html>

<?php 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = mysqli_connect('localhost', 'root', '', 'nssfdb');
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Get and sanitize user input
    $employee_name = $_POST['employee_name'];
    $address = $_POST['address'];
    $dob = $_POST['dob'];
    $contact = $_POST['contact'];
    $email = $_POST['email'];
    $position = $_POST['position'];
    $gender = $_POST['gender'];

    // Check if employee exists
    $check_query = "SELECT * FROM employee_records WHERE Ename = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param('s', $employee_name);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        echo "<script>alert('Employee not found! Please enter a valid name.');</script>";
    } else {
        // Get new gross salary based on selected position
        $safe_position = mysqli_real_escape_string($conn, $position);
        $sql = "SELECT gross_salary FROM salary_table WHERE position = '$safe_position'";
        $salary_result = mysqli_query($conn, $sql);

        if ($salary_result && mysqli_num_rows($salary_result) > 0) {
            $row = mysqli_fetch_assoc($salary_result);
            $gross = $row['gross_salary'];

            // Deduction function
            function deductions($gross) {
                $nssf_employer_cut = 0.1 * $gross;
                $nssf_employee_cut = 0.05 * $gross;
                $nssf_share = $nssf_employer_cut + $nssf_employee_cut;
                $taxableIncome = $gross - $nssf_share;

                if ($taxableIncome <= 235000) {
                    $ura_cut = 0;
                } elseif ($taxableIncome <= 355000) {
                    $ura_cut = ($taxableIncome - 235000) * 0.1;
                } elseif ($taxableIncome <= 410000) {
                    $ura_cut = ($taxableIncome - 355000) * 0.2 + 10000;
                } elseif ($taxableIncome <= 10000000) {
                    $ura_cut = ($taxableIncome - 410000) * 0.3 + 25000;
                } else {
                    $ura_cut = (($taxableIncome - 410000) * 0.3 + 25000) + (($taxableIncome - 10000000) * 0.1);
                }

                $net_pay = $taxableIncome - $ura_cut;

                return [
                    'net_pay' => $net_pay,
                    'ura_cut' => $ura_cut,
                    'nssf_employee_cut' => $nssf_employee_cut,
                    'nssf_employer_cut' => $nssf_employer_cut
                ];
            }

            // Store results in variables
            $container = deductions($gross);
            $net_salary = $container['net_pay'];
            $ura_cut = $container['ura_cut'];
            $nssf_employee_cut = $container['nssf_employee_cut'];
            $nssf_employer_cut = $container['nssf_employer_cut'];

            // Update record in database
            $update_sql = "UPDATE employee_records SET address=?, dob=?, contact=?, email=?, position=?, gender=?, ura_cut=?, nssf_employee_cut=?, nssf_employer_cut=?, net_salary=? WHERE Ename=?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param('sssssssddds', $address, $dob, $contact, $email, $position, $gender, $ura_cut, $nssf_employee_cut, $nssf_employer_cut, $net_salary, $employee_name);
            $update_stmt->execute();

            echo "<script>alert('Record updated successfully!');</script>";

            $update_stmt->close();
        } else {
            echo "<p>Position not found in salary table.</p>";
        }
    }

    $check_stmt->close();
    $conn->close(); 
}
?>
