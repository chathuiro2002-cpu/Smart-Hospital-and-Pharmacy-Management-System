<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id'])) {
    die("Invalid prescription.");
}

$id = (int)$_GET['id'];

$stmt = mysqli_prepare($conn, "
SELECT
    p.prescription_id,
    pt.name AS patient_name,
    d.name AS doctor_name,
    p.diagnosis,
    p.notes,
    p.prescription_date,
    m.medicine_name,
    pm.dosage,
    pm.quantity
FROM prescriptions p
JOIN patients pt ON p.patient_id = pt.patient_id
JOIN doctors d ON p.doctor_id = d.doctor_id
LEFT JOIN prescription_medicines pm ON p.prescription_id = pm.prescription_id
LEFT JOIN medicines m ON pm.medicine_id = m.medicine_id
WHERE p.prescription_id = ?
");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    die("Prescription not found.");
}

$first = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Prescription | Smart Hospital & Pharmacy Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body{ margin:40px; font-family:'Segoe UI', Roboto, sans-serif; }
        table{ width:100%; }
        @media print { .no-print{ display:none; } }
    </style>

    <script>
        window.onload = function(){ window.print(); };
    </script>
</head>
<body>

<div class="container">

    <h2 class="text-center">Smart Hospital Management System</h2>
    <hr>

    <p><strong>Prescription ID:</strong> #<?php echo (int)$first['prescription_id']; ?></p>
    <p><strong>Patient:</strong> <?php echo htmlspecialchars($first['patient_name']); ?></p>
    <p><strong>Doctor:</strong> <?php echo htmlspecialchars($first['doctor_name']); ?></p>
    <p><strong>Date:</strong> <?php echo htmlspecialchars($first['prescription_date']); ?></p>
    <p><strong>Diagnosis:</strong> <?php echo htmlspecialchars($first['diagnosis']); ?></p>

    <h4 class="mt-4">Medicines</h4>

    <table class="table table-bordered">
        <tr class="table-secondary">
            <th>Medicine</th>
            <th>Dosage</th>
            <th>Quantity</th>
        </tr>
        <tr>
            <td><?php echo htmlspecialchars($first['medicine_name'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($first['dosage'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($first['quantity'] ?? ''); ?></td>
        </tr>
        <?php while($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row['medicine_name'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($row['dosage'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($row['quantity'] ?? ''); ?></td>
        </tr>
        <?php } ?>
    </table>

    <p><strong>Notes:</strong> <?php echo htmlspecialchars($first['notes']); ?></p>

    <br><br>

    <div class="text-end">
        <b>Dr. <?php echo htmlspecialchars($first['doctor_name']); ?></b>
        <br>
        Medical Officer
    </div>

    <hr>
    <p class="text-center">Smart Hospital Management System &copy; 2026</p>

</div>

</body>
</html>