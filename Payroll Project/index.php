<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home | Payroll</title>
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

  <form action="index.php" method="POST">
    <h2>Register Employee</h2>
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
    // Connect to DB
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

// Check if employee already exists
$check_query = "SELECT * FROM employee_records WHERE Ename = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param('s', $employee_name);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
 echo "<script>alert('This record already exists!');</script>";
} else {
 $safe_position = mysqli_real_escape_string($conn, $position);
 $sql = "SELECT gross_salary FROM salary_table WHERE position = '$safe_position'";
 $salary_result = mysqli_query($conn, $sql);

 if ($salary_result && mysqli_num_rows($salary_result) > 0) {
     $row = mysqli_fetch_assoc($salary_result);
     $gross = $row['gross_salary'];

     // Function to calculate deductions
     function deductions ($gross) {
         //calculate NSSF 
         // employer NSSF | 10% of Gross Salary
         $nssf_employer_cut = 0.1 * $gross;
     
         // employee NSSF | 5% of Gross Salary 
         $nssf_employee_cut = 0.05 * $gross;
     
         //Total NSSF 
         $nssf_share = $nssf_employer_cut + $nssf_employee_cut; 
         $taxableIncome = $gross - $nssf_share;
     
         //URA tax calculation 
         if ($taxableIncome <= 235000) {
            $ura_cut = 0;
             $net_pay = $taxableIncome - $ura_cut;

             return [
                'net_pay' => $net_pay,
                'ura_cut' => $ura_cut,
                'nssf_employee_cut' => $nssf_employee_cut,
                'nssf_employer_cut' => $nssf_employer_cut
            ];
     
         } elseif ($taxableIncome > 235000 && $taxableIncome <= 355000) {
             $ura_cut = ($taxableIncome - 235000) * 0.1;
             $net_pay = $taxableIncome - $ura_cut;
             
             return [
                'net_pay' => $net_pay,
                'ura_cut' => $ura_cut,
                'nssf_employee_cut' => $nssf_employee_cut,
                'nssf_employer_cut' => $nssf_employer_cut
            ];
     
         } elseif ($taxableIncome > 355000 && $taxableIncome <= 410000) {
             $ura_cut = ($taxableIncome - 355000) * 0.2 + 10000;
             $net_pay = $taxableIncome - $ura_cut;
             
             return [
                'net_pay' => $net_pay,
                'ura_cut' => $ura_cut,
                'nssf_employee_cut' => $nssf_employee_cut,
                'nssf_employer_cut' => $nssf_employer_cut
            ];
     
         } elseif ($taxableIncome > 410000 && $taxableIncome <= 10000000) {
             $ura_cut = ($taxableIncome - 410000) * 0.3 + 25000;
             $net_pay = $taxableIncome - $ura_cut;
             
             return [
                'net_pay' => $net_pay,
                'ura_cut' => $ura_cut,
                'nssf_employee_cut' => $nssf_employee_cut,
                'nssf_employer_cut' => $nssf_employer_cut
            ];
     
         } elseif ($taxableIncome > 10000000) {
             $ura_cut = (($taxableIncome - 410000) * 0.3 + 25000) + (($taxableIncome - 10000000) * 0.1);
             $net_pay = $taxableIncome - $ura_cut;
             
             return [
                'net_pay' => $net_pay,
                'ura_cut' => $ura_cut,
                'nssf_employee_cut' => $nssf_employee_cut,
                'nssf_employer_cut' => $nssf_employer_cut
            ];
         }
     }

     //Storing the Array to a Variable
 $container = deductions($gross);

 //Extracting individual values from the array
 $net_salary = $container['net_pay'];
 $ura_cut = $container['ura_cut'];
$nssf_employee_cut = $container['nssf_employee_cut'];
$nssf_employer_cut = $container['nssf_employer_cut'];

 // Insert new employee record
 $insert_sql = "INSERT INTO employee_records (Ename, address, dob, contact, email, position, gender, ura_cut, nssf_employee_cut, nssf_employer_cut, net_salary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
 $insert_stmt = $conn->prepare($insert_sql);
 $insert_stmt->bind_param('sssssssdddd', $employee_name, $address, $dob, $contact, $email, $position, $gender, $ura_cut, $nssf_employee_cut, $nssf_employer_cut, $net_salary);
 $insert_stmt->execute();

 echo "<script>alert('Record Saved!');</script>";
 $insert_stmt->close();
      } else {
         echo "<script>alert('Position not found. Please enter a valid position');</script>";
     }

}

$check_stmt->close();
$conn->close(); 
}

?>