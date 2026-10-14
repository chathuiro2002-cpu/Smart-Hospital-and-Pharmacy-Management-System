<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

$query = "
SELECT
    a.queue_number,
    p.patient_token,
    p.name AS patient_name,
    d.name AS doctor_name,
    a.appointment_date,
    a.status
FROM appointments a
JOIN patients p ON a.patient_id = p.patient_id
JOIN doctors d ON a.doctor_id = d.doctor_id
WHERE a.status = 'Pending'
ORDER BY a.appointment_id ASC
";

$result = mysqli_query($conn, $query);
$total = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue Management | Smart Hospital & Pharmacy Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body{ background-color:#f4f6f9; font-family:'Segoe UI', Roboto, sans-serif; }
        .navbar-brand{ font-weight:700; }
        .navbar-brand i{ margin-right:8px; color:#4dabf7; }
        .page-header{ margin-top:2rem; margin-bottom:1.25rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; }
        .page-header h2{ font-weight:700; color:#1e293b; margin-bottom:0.15rem; }
        .page-header p{ color:#64748b; margin-bottom:0; font-size:0.9rem; }
        .table-card{ background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06); overflow:hidden; }
        thead th{ background:#1e293b; color:#fff; font-weight:600; font-size:0.85rem; text-transform:uppercase; }
        tbody td{ vertical-align:middle; font-size:0.92rem; }
        .empty-state{ text-align:center; padding:3rem 1rem; color:#94a3b8; }
        .empty-state i{ font-size:2.5rem; margin-bottom:0.5rem; display:block; }
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
        <div>
            <h2><i class="bi bi-people me-2"></i>Hospital Queue</h2>
            <p><?php echo $total; ?> patient<?php echo $total != 1 ? 's' : ''; ?> waiting</p>
        </div>
        <a href="../admin/dashboard.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
    </div>

    <div class="table-card">
        <?php if($total > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>Queue No</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['queue_number']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($row['patient_token']); ?>
                                -
                            <?php echo htmlspecialchars($row['patient_name']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['doctor_name']); ?></td>
                        <td><?php echo htmlspecialchars(date("d-m-Y", strtotime($row['appointment_date']))); ?></td>
                        <td><span class="badge bg-warning text-dark"><?php echo htmlspecialchars($row['status']); ?></span></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                No patients currently waiting.
            </div>
        <?php endif; ?>
    </div>

</div>

<footer class="text-center py-3 mt-4">
    <hr>
    <p>Smart Hospital Management System &copy; 2026</p>
</footer>

</body>
</html>