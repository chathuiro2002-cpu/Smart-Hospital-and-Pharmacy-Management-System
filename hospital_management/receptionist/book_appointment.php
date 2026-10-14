<?php
session_start();
include '../config/database.php';
include '../config/notification.php';

if (!isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

$patients = mysqli_query($conn, "SELECT patient_id, patient_token, name FROM patients ORDER BY patient_token");
$doctors = mysqli_query($conn, "SELECT * FROM doctors ORDER BY name");

$message = "";
$message_type = "info";

if (isset($_POST['save'])) {

    $patient_id = intval($_POST['patient_id']);
    $doctor_id = intval($_POST['doctor_id']);
    $appointment_date = $_POST['appointment_date'];

    $date_obj = DateTime::createFromFormat('Y-m-d', $appointment_date);
    $valid_date = $date_obj && $date_obj->format('Y-m-d') === $appointment_date;

    if (!$valid_date) {
        $message = "Please provide a valid appointment date.";
        $message_type = "danger";
    } else {

        $stmt = mysqli_prepare($conn,
            "INSERT INTO appointments (patient_id, doctor_id, appointment_date, status) VALUES (?, ?, ?, 'Pending')");
        mysqli_stmt_bind_param($stmt, "iis", $patient_id, $doctor_id, $appointment_date);

        if (mysqli_stmt_execute($stmt)) {

            $appointment_id = mysqli_insert_id($conn);
            $queue_number = "Q" . str_pad($appointment_id, 5, "0", STR_PAD_LEFT);

            $queue_stmt = mysqli_prepare($conn, "UPDATE appointments SET queue_number = ? WHERE appointment_id = ?");
            mysqli_stmt_bind_param($queue_stmt, "si", $queue_number, $appointment_id);
            mysqli_stmt_execute($queue_stmt);

            $count_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM appointments WHERE status = 'Pending'");
            $current_queue = mysqli_fetch_assoc($count_result)['total'];

            addNotification(
                $conn,
                $patient_id,
                "Appointment Booked",
                "Appointment booked successfully.\nQueue Number: $queue_number\nCurrent Queue: $current_queue"
            );

            $message = "Appointment booked successfully! Queue Number: <b>" . htmlspecialchars($queue_number) . "</b> &mdash; Current Queue: <b>" . (int)$current_queue . "</b>";
            $message_type = "success";

        } else {
            $message = "Error: " . htmlspecialchars(mysqli_error($conn));
            $message_type = "danger";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment | Smart Hospital & Pharmacy Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body{ background-color:#f4f6f9; font-family:'Segoe UI', Roboto, sans-serif; }
        .navbar-brand{ font-weight:700; }
        .navbar-brand i{ margin-right:8px; color:#4dabf7; }
        .form-card{ background:#fff; border-radius:14px; box-shadow:0 2px 10px rgba(0,0,0,0.06); padding:2rem; max-width:560px; margin:2rem auto; }
        .form-card h3{ font-weight:700; color:#1e293b; margin-bottom:1.25rem; }
        .form-label{ font-weight:600; color:#334155; font-size:0.9rem; }
        .form-control, .form-select{ border-radius:10px; }
        .btn-save{ background:linear-gradient(135deg,#22b8cf,#1971c2); border:none; color:#fff; font-weight:600; border-radius:10px; }
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
    <div class="form-card">
        <h3><i class="bi bi-calendar-plus me-2"></i>Book Appointment</h3>

        <?php if($message != ""): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo $message; /* pre-escaped above */ ?></div>
        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Patient</label>
                <select name="patient_id" class="form-select" required>
                    <option value="">Select Patient</option>
                    <?php while($patient = mysqli_fetch_assoc($patients)) { ?>
                        <option value="<?php echo (int)$patient['patient_id']; ?>">
                            <?php echo htmlspecialchars($patient['patient_token']); ?> - <?php echo htmlspecialchars($patient['name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Doctor</label>
                <select name="doctor_id" class="form-select" required>
                    <option value="">Select Doctor</option>
                    <?php while($doctor = mysqli_fetch_assoc($doctors)) { ?>
                        <option value="<?php echo (int)$doctor['doctor_id']; ?>">
                            <?php echo htmlspecialchars($doctor['name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Appointment Date</label>
                <input type="date" name="appointment_date" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <button type="submit" name="save" class="btn btn-save">Book Appointment</button>
            <a href="dashboard.php" class="btn btn-secondary">Back</a>

        </form>
    </div>
</div>

<footer class="text-center py-3">
    <hr>
    <p>Smart Hospital Management System &copy; 2026</p>
</footer>

</body>
</html>