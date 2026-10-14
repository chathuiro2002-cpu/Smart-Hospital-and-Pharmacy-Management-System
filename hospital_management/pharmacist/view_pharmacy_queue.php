<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['pharmacist', 'admin'])) {
    header("Location: ../login.php");
    exit();
}

$query = "
SELECT
    pq.queue_id,
    pq.queue_number,
    pq.prescription_id,
    pq.status,
    p.name AS patient_name
FROM pharmacy_queue pq
INNER JOIN patients p ON pq.patient_id = p.patient_id
ORDER BY pq.queue_id ASC
";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Database Error: " . htmlspecialchars(mysqli_error($conn)));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Queue | Smart Hospital & Pharmacy Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body{ background-color:#f4f6f9; font-family:'Segoe UI', Roboto, sans-serif; }
        .navbar-brand{ font-weight:700; }
        .navbar-brand i{ margin-right:8px; color:#4dabf7; }
        .content-card{ background:#fff; border-radius:14px; box-shadow:0 2px 10px rgba(0,0,0,0.06); overflow:hidden; margin:2rem auto; max-width:1000px; }
        .content-card .card-header{ background:#1e293b; color:#fff; padding:1.25rem 1.5rem; font-weight:700; }
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

        <div class="card-header">
            <i class="bi bi-hourglass-split"></i> Pharmacy Queue
        </div>

        <div class="p-4">

            <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success">Medicine dispensed successfully.</div>
            <?php endif; ?>

            <a href="dashboard.php" class="btn btn-secondary mb-3"><i class="bi bi-arrow-left"></i> Back</a>

            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Queue No</th>
                            <th>Patient Name</th>
                            <th>Prescription ID</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['queue_number']) ?></td>
                                    <td><?= htmlspecialchars($row['patient_name']) ?></td>
                                    <td>#<?= (int)$row['prescription_id'] ?></td>
                                    <td>
                                        <?php if ($row['status'] == "Waiting") { ?>
                                            <span class="badge bg-warning text-dark">Waiting</span>
                                        <?php } elseif ($row['status'] == "Completed") { ?>
                                            <span class="badge bg-success">Completed</span>
                                        <?php } else { ?>
                                            <span class="badge bg-secondary"><?= htmlspecialchars($row['status']) ?></span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <a href="../doctor/view_prescriptions.php?id=<?= (int)$row['prescription_id'] ?>"
                                           class="btn btn-primary btn-sm">View Prescription</a>

                                        <?php if ($row['status'] == "Waiting") { ?>
                                            <a href="dispense_medicine.php?id=<?= (int)$row['prescription_id'] ?>"
                                               class="btn btn-success btn-sm"
                                               onclick="return confirm('Dispense medicine for this prescription?');">
                                                Dispense
                                            </a>
                                        <?php } else { ?>
                                            <span class="text-success fw-bold">Done</span>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No Patients in Pharmacy Queue</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<footer class="text-center py-3">
    <hr>
    <p>Smart Hospital Management System &copy; 2026</p>
</footer>

</body>
</html>