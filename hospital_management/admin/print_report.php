<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$doctor_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM doctors"));
$patient_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM patients"));
$appointment_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM appointments"));
$prescription_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM prescriptions"));
$medicine_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM medicines"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hospital Report | Smart Hospital & Pharmacy Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body{ font-family:'Segoe UI', Roboto, sans-serif; }
        h2{ font-weight:700; color:#1e293b; }
        table{ margin-top:1.5rem; }
        thead th{ background:#1e293b; color:#fff; }
        @media print {
            .no-print{ display:none; }
        }
    </style>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</head>
<body>

<div class="container mt-5">

    <h2>Hospital Summary Report</h2>
    <p class="text-muted">Generated on <?php echo date('F j, Y \a\t g:i A'); ?></p>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Item</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Doctors</td>
                <td><?php echo $doctor_count; ?></td>
            </tr>
            <tr>
                <td>Total Patients</td>
                <td><?php echo $patient_count; ?></td>
            </tr>
            <tr>
                <td>Total Appointments</td>
                <td><?php echo $appointment_count; ?></td>
            </tr>
            <tr>
                <td>Total Prescriptions</td>
                <td><?php echo $prescription_count; ?></td>
            </tr>
            <tr>
                <td>Total Medicines</td>
                <td><?php echo $medicine_count; ?></td>
            </tr>
        </tbody>
    </table>

    <a href="reports.php" class="btn btn-secondary no-print">Back to Reports</a>

</div>

<footer class="text-center mt-5 no-print">
    <hr>
    <p>Smart Hospital Management System @ 2026</p>
</footer>

</body>
</html>