<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

// Medicines are aggregated with a plain-text separator (not raw HTML) so each
// piece can be escaped individually before being joined with <br> for display.
$base_query = "
SELECT
    p.prescription_id,
    pt.name AS patient_name,
    d.name AS doctor_name,
    p.diagnosis,
    p.notes,
    p.prescription_date,
    GROUP_CONCAT(
        CONCAT(m.medicine_name, ' (', pm.dosage, ', Qty: ', pm.quantity, ')')
        SEPARATOR '||'
    ) AS medicines
FROM prescriptions p
INNER JOIN patients pt ON p.patient_id = pt.patient_id
INNER JOIN doctors d ON p.doctor_id = d.doctor_id
LEFT JOIN prescription_medicines pm ON p.prescription_id = pm.prescription_id
LEFT JOIN medicines m ON pm.medicine_id = m.medicine_id
";

$group_by = "
GROUP BY p.prescription_id, pt.name, d.name, p.diagnosis, p.notes, p.prescription_date
ORDER BY p.prescription_id DESC
";

if (isset($_GET['id']) && ctype_digit((string)$_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = mysqli_prepare($conn, $base_query . " WHERE p.prescription_id = ? " . $group_by);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $base_query . $group_by);
}

if (!$result) {
    die("Database Error: " . htmlspecialchars(mysqli_error($conn)));
}

// Send people back to wherever makes sense for their role
$back_link = "../login.php";
switch ($_SESSION['role']) {
    case 'doctor': $back_link = "dashboard.php"; break;
    case 'pharmacist': $back_link = "../pharmacist/view_pharmacy_queue.php"; break;
    case 'admin': $back_link = "../admin/dashboard.php"; break;
    case 'receptionist': $back_link = "../receptionist/dashboard.php"; break;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prescriptions | Smart Hospital & Pharmacy Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body{ background-color:#f4f6f9; font-family:'Segoe UI', Roboto, sans-serif; }
        .navbar-brand{ font-weight:700; }
        .navbar-brand i{ margin-right:8px; color:#4dabf7; }
        .page-header{ margin-top:2rem; margin-bottom:1.25rem; }
        .page-header h2{ font-weight:700; color:#1e293b; }
        .table-card{ background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06); overflow:hidden; }
        thead th{ background:#1e293b; color:#fff; font-weight:600; font-size:0.85rem; text-transform:uppercase; }
        tbody td{ vertical-align:middle; font-size:0.9rem; }
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

    <div class="page-header">
        <h2><i class="bi bi-file-earmark-text me-2"></i>Prescription List</h2>
    </div>

    <a href="<?php echo htmlspecialchars($back_link); ?>" class="btn btn-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Back
    </a>

    <div class="table-card">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Diagnosis</th>
                        <th>Medicines</th>
                        <th>Notes</th>
                        <th>Date</th>
                        <th>Dispense</th>
                        <th>Print</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td>#<?php echo (int)$row['prescription_id']; ?></td>
                            <td><?php echo htmlspecialchars($row['patient_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['diagnosis']); ?></td>
                            <td>
                                <?php
                                if (!empty($row['medicines'])) {
                                    $parts = explode('||', $row['medicines']);
                                    echo implode('<br>', array_map('htmlspecialchars', $parts));
                                } else {
                                    echo '<span class="text-muted">None</span>';
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['notes']); ?></td>
                            <td><?php echo htmlspecialchars(date("d-m-Y", strtotime($row['prescription_date']))); ?></td>
                            <td>
                                <?php
                                $status_stmt = mysqli_prepare($conn, "SELECT status FROM pharmacy_queue WHERE prescription_id = ?");
                                mysqli_stmt_bind_param($status_stmt, "i", $row['prescription_id']);
                                mysqli_stmt_execute($status_stmt);
                                $status = mysqli_fetch_assoc(mysqli_stmt_get_result($status_stmt));

                                if ($status && $status['status'] == "Waiting") {
                                ?>
                                    <a href="../pharmacist/dispense_medicine.php?id=<?php echo (int)$row['prescription_id']; ?>"
                                       class="btn btn-success btn-sm">Dispense</a>
                                <?php } else { ?>
                                    <span class="badge bg-success">Completed</span>
                                <?php } ?>
                            </td>
                            <td>
                                <a href="print_prescription.php?id=<?php echo (int)$row['prescription_id']; ?>"
                                   target="_blank" class="btn btn-primary btn-sm">Print</a>
                            </td>
                        </tr>
                        <?php } ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No Prescriptions Found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<footer class="text-center py-3 mt-4">
    <hr>
    <p>Smart Hospital Management System &copy; 2026</p>
</footer>

</body>
</html>