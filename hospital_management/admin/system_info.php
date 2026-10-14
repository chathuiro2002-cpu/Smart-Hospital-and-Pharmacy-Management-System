<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$patient_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM patients"));
$doctor_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM doctors"));
$appointment_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM appointments"));
$prescription_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM prescriptions"));
$medicine_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM medicines"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Information | Smart Hospital & Pharmacy Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body{ background-color:#f4f6f9; font-family:'Segoe UI', Roboto, sans-serif; }
        .navbar-brand{ font-weight:700; }
        .navbar-brand i{ margin-right:8px; color:#4dabf7; }
        .content-card{ background:#fff; border-radius:14px; box-shadow:0 2px 10px rgba(0,0,0,0.06); padding:2rem; margin:2rem auto; max-width:700px; }
        .content-card h2{ font-weight:700; color:#1e293b; }
        thead th{ background:#1e293b; color:#fff; }
        footer{ color:#94a3b8; font-size:0.9rem; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark shadow-sm">
    <div class="container-fluid">
        <span class="navbar-brand"><i class="bi bi-hospital"></i> Smart Hospital & Pharmacy Management System</span>
        <a href="../logout.php" class="btn btn-outline-light btn-sm"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
</nav>

<div class="container">
    <div class="content-card">

        <h2><i class="bi bi-info-circle me-2"></i>System Information</h2>

        <table class="table table-bordered mt-3">
            <tbody>
                <tr>
                    <th>System Name</th>
                    <td>Smart Hospital & Pharmacy Management System</td>
                </tr>
                <tr>
                    <th>Version</th>
                    <td>1.0</td>
                </tr>
                <tr>
                    <th>Total Patients</th>
                    <td><?php echo $patient_count; ?></td>
                </tr>
                <tr>
                    <th>Total Doctors</th>
                    <td><?php echo $doctor_count; ?></td>
                </tr>
                <tr>
                    <th>Total Appointments</th>
                    <td><?php echo $appointment_count; ?></td>
                </tr>
                <tr>
                    <th>Total Prescriptions</th>
                    <td><?php echo $prescription_count; ?></td>
                </tr>
                <tr>
                    <th>Total Medicines</th>
                    <td><?php echo $medicine_count; ?></td>
                </tr>
                <tr>
                    <th>Database</th>
                    <td>MySQL</td>
                </tr>
                <tr>
                    <th>Server</th>
                    <td>XAMPP</td>
                </tr>
                <tr>
                    <th>Programming Language</th>
                    <td>PHP <?php echo htmlspecialchars(phpversion()); ?></td>
                </tr>
            </tbody>
        </table>

        <a href="dashboard.php" class="btn btn-secondary">Back</a>

    </div>
</div>

<footer class="text-center py-3">
    <hr>
    <p>Smart Hospital Management System @ 2026</p>
</footer>

</body>
</html>