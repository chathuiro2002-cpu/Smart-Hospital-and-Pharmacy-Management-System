<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id'])) {
    header("Location: view_appointments.php");
    exit();
}

$id = (int)$_GET['id'];
$message = "";

$patients = mysqli_query($conn, "SELECT * FROM patients ORDER BY name");
$doctors = mysqli_query($conn, "SELECT * FROM doctors ORDER BY name");

$stmt = mysqli_prepare($conn, "SELECT * FROM appointments WHERE appointment_id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$row) {
    header("Location: view_appointments.php");
    exit();
}

if (isset($_POST['update'])) {

    $patient_id = intval($_POST['patient_id']);
    $doctor_id = intval($_POST['doctor_id']);
    $appointment_date = $_POST['appointment_date'];
    $status = $_POST['status'];

    $valid_statuses = ['Pending', 'Approved', 'Completed'];
    $date_obj = DateTime::createFromFormat('Y-m-d', $appointment_date);
    $valid_date = $date_obj && $date_obj->format('Y-m-d') === $appointment_date;

    if (!in_array($status, $valid_statuses) || !$valid_date) {
        $message = "Invalid status or date provided.";
    } else {
        $update = mysqli_prepare($conn,
            "UPDATE appointments SET patient_id = ?, doctor_id = ?, appointment_date = ?, status = ? WHERE appointment_id = ?");
        mysqli_stmt_bind_param($update, "iissi", $patient_id, $doctor_id, $appointment_date, $status, $id);
        mysqli_stmt_execute($update);

        header("Location: view_appointments.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Appointment | Smart Hospital & Pharmacy Management System</title>

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
        .btn-save{ background:linear-gradient(135deg,#4dabf7,#1971c2); border:none; color:#fff; font-weight:600; border-radius:10px; }
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
        <h3><i class="bi bi-pencil-square me-2"></i>Edit Appointment</h3>

        <?php if($message != ""): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Patient</label>
                <select name="patient_id" class="form-select" required>
                    <?php while($patient = mysqli_fetch_assoc($patients)) { ?>
                        <option value="<?php echo (int)$patient['patient_id']; ?>"
                            <?php echo $patient['patient_id'] == $row['patient_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($patient['name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Doctor</label>
                <select name="doctor_id" class="form-select" required>
                    <?php while($doctor = mysqli_fetch_assoc($doctors)) { ?>
                        <option value="<?php echo (int)$doctor['doctor_id']; ?>"
                            <?php echo $doctor['doctor_id'] == $row['doctor_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($doctor['name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Appointment Date</label>
                <input type="date" name="appointment_date" class="form-control"
                       value="<?php echo htmlspecialchars($row['appointment_date']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="Pending" <?php echo $row['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="Approved" <?php echo $row['status'] == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="Completed" <?php echo $row['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                </select>
            </div>

            <button type="submit" name="update" class="btn btn-save">Update Appointment</button>
            <a href="view_appointments.php" class="btn btn-secondary">Back</a>

        </form>
    </div>
</div>

<footer class="text-center py-3">
    <hr>
    <p>Smart Hospital Management System &copy; 2026</p>
</footer>

</body>
</html>