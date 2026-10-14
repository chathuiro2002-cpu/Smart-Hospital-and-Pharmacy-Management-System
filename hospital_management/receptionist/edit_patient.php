<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id'])) {
    header("Location: view_patients.php");
    exit();
}

$id = (int)$_GET['id'];
$message = "";
$message_type = "danger";

$stmt = mysqli_prepare($conn, "SELECT * FROM patients WHERE patient_id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$row) {
    header("Location: view_patients.php");
    exit();
}

if (isset($_POST['update'])) {

    $name = trim($_POST['name']);
    $date_of_birth = $_POST['date_of_birth'];
    $age = intval($_POST['age']);
    $gender = $_POST['gender'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    $date_obj = DateTime::createFromFormat('Y-m-d', $date_of_birth);
    $valid_date = $date_obj && $date_obj->format('Y-m-d') === $date_of_birth;

    if ($name === "" || $phone === "" || !in_array($gender, ['Male', 'Female']) || !$valid_date) {
        $message = "Please provide valid values for all fields.";
    } else {

        $update = mysqli_prepare($conn,
            "UPDATE patients SET name = ?, date_of_birth = ?, age = ?, gender = ?, phone = ?, address = ? WHERE patient_id = ?");
        mysqli_stmt_bind_param($update, "ssissi",
            $name, $date_of_birth, $age, $gender, $phone, $address, $id);

        if (mysqli_stmt_execute($update)) {
            $message = "Patient updated successfully!";
            $message_type = "success";

            $stmt2 = mysqli_prepare($conn, "SELECT * FROM patients WHERE patient_id = ?");
            mysqli_stmt_bind_param($stmt2, "i", $id);
            mysqli_stmt_execute($stmt2);
            $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2));
        } else {
            $message = "Error: " . htmlspecialchars(mysqli_error($conn));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Patient | Smart Hospital & Pharmacy Management System</title>

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
        .btn-save{ background:linear-gradient(135deg,#51cf66,#2b8a3e); border:none; color:#fff; font-weight:600; border-radius:10px; }
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
        <h3><i class="bi bi-pencil-square me-2"></i>Edit Patient</h3>

        <?php if($message != ""): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($row['name']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Date of Birth</label>
                <input type="date" name="date_of_birth" class="form-control" value="<?php echo htmlspecialchars($row['date_of_birth']); ?>" max="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Age</label>
                <input type="number" name="age" class="form-control" value="<?php echo (int)$row['age']; ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-select" required>
                    <option value="Male" <?php echo $row['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo $row['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($row['phone']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control" required><?php echo htmlspecialchars($row['address']); ?></textarea>
            </div>

            <button type="submit" name="update" class="btn btn-save">Update Patient</button>
            <a href="view_patients.php" class="btn btn-secondary">Back</a>

        </form>
    </div>
</div>

<footer class="text-center py-3">
    <hr>
    <p>Smart Hospital Management System &copy; 2026</p>
</footer>

</body>
</html>