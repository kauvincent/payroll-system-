<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View | Payroll</title>
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

<h2>Employee Payroll Records</h2>

<?php
$conn = mysqli_connect('localhost', 'root', '', 'nssfdb');
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// JOIN query: Get employee details and position's gross salary
$sql = "
    SELECT 
        e.Ename, e.address, e.dob, e.contact, e.email, e.position, e.gender,
        s.gross_salary,
        e.ura_cut, e.nssf_employee_cut, e.nssf_employer_cut, e.net_salary
    FROM 
        employee_records AS e
    INNER JOIN 
        salary_table AS s 
    ON 
        e.position = s.position
";

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    echo "<table>";
    echo "<tr>
            <th>Name</th><th>Address</th><th>DOB</th><th>Contact</th>
            <th>Email</th><th>Position</th><th>Gender</th>
            <th>Gross Salary</th><th>URA Cut</th><th>NSSF Employee</th>
            <th>NSSF Employer</th><th>Net Salary</th>
          </tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>{$row['Ename']}</td>";
        echo "<td>{$row['address']}</td>";
        echo "<td>{$row['dob']}</td>";
        echo "<td>{$row['contact']}</td>";
        echo "<td>{$row['email']}</td>";
        echo "<td>{$row['position']}</td>";
        echo "<td>{$row['gender']}</td>";
        echo "<td>" . number_format($row['gross_salary']) . "</td>";
        echo "<td>" . number_format($row['ura_cut']) . "</td>";
        echo "<td>" . number_format($row['nssf_employee_cut']) . "</td>";
        echo "<td>" . number_format($row['nssf_employer_cut']) . "</td>";
        echo "<td>" . number_format($row['net_salary']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
  echo "<table>";
  echo "<tr>
          <th>Name</th><th>Address</th><th>DOB</th><th>Contact</th>
          <th>Email</th><th>Position</th><th>Gender</th>
          <th>Gross Salary</th><th>URA Cut</th><th>NSSF Employee</th>
          <th>NSSF Employer</th><th>Net Salary</th>
        </tr>";
  echo "<tr><td colspan='12'>No records found in the database</td></tr>";
  echo "</table>";
}


mysqli_close($conn);


?>

</body>
</html>
