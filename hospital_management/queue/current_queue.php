<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'doctor') {
    header("Location: ../login.php");
    exit();
}

$query = "
SELECT
    a.appointment_id,
    a.queue_number,
    p.name AS patient_name,
    d.name AS doctor_name
FROM appointments a
JOIN patients p ON a.patient_id = p.patient_id
JOIN doctors d ON a.doctor_id = d.doctor_id
WHERE a.status = 'Pending'
ORDER BY a.appointment_id ASC
LIMIT 1
";

$result = mysqli_query($conn, $query);
$current = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Current Queue | Smart Hospital & Pharmacy Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body{ background-color:#f4f6f9; font-family:'Segoe UI', Roboto, sans-serif; }
        .navbar-brand{ font-weight:700; }
        .navbar-brand i{ margin-right:8px; color:#4dabf7; }
        .queue-card{ background:#fff; border-radius:16px; box-shadow:0 4px 16px rgba(0,0,0,0.08); padding:2.5rem; max-width:520px; margin:3rem auto; text-align:center; }
        .queue-number{ font-size:3rem; font-weight:700; color:#1971c2; margin-bottom:0.25rem; }
        .queue-label{ color:#64748b; text-transform:uppercase; letter-spacing:0.5px; font-size:0.85rem; margin-bottom:1.5rem; }
        .info-row{ text-align:left; border-top:1px solid #f1f3f5; padding-top:1rem; margin-top:1rem; }
        .info-row strong{ color:#1e293b; }
        .btn-complete{ background:linear-gradient(135deg,#51cf66,#2b8a3e); border:none; color:#fff; font-weight:600; border-radius:10px; padding:0.7rem 1.5rem; margin-top:1.5rem; }
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

    <h2 class="text-center mt-4"><i class="bi bi-person-check me-2"></i>Current Queue</h2>

    <?php if($current): ?>
        <div class="queue-card">
            <div class="queue-number"><?php echo htmlspecialchars($current['queue_number']); ?></div>
            <div class="queue-label">Now Serving</div>

            <div class="info-row">
                <p class="mb-1"><strong>Patient:</strong> <?php echo htmlspecialchars($current['patient_name']); ?></p>
                <p class="mb-0"><strong>Doctor:</strong> <?php echo htmlspecialchars($current['doctor_name']); ?></p>
            </div>

            <form method="GET" action="complete_queue.php" onsubmit="return confirm('Mark this consultation as completed?');">
                <input type="hidden" name="id" value="<?php echo (int)$current['appointment_id']; ?>">
                <button type="submit" class="btn btn-complete">
                    <i class="bi bi-check-circle me-1"></i> Complete Consultation
                </button>
            </form>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center mt-4" style="max-width:520px; margin:0 auto;">
            <i class="bi bi-info-circle me-1"></i> No patients waiting.
        </div>
    <?php endif; ?>

</div>

<footer class="text-center py-3 mt-5">
    <hr>
    <p>Smart Hospital Management System &copy; 2026</p>
</footer>

</body>
</html>