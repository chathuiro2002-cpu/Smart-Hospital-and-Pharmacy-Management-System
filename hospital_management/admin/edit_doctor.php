<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id'])) {
    header("Location: view_doctors.php");
    exit();
}

$id = (int)$_GET['id'];
$message = "";
$message_type = "danger";

// Get doctor data
$stmt = mysqli_prepare($conn, "SELECT * FROM doctors WHERE doctor_id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$doctor = mysqli_fetch_assoc($result);

if (!$doctor) {
    header("Location: view_doctors.php");
    exit();
}

if (isset($_POST['update'])) {

    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $room_number = trim($_POST['room_number']);
    $availability = $_POST['availability'];

   if ($name === "" || $phone === "") {
    $message = "Name and phone are required.";
} elseif (!preg_match('/^07[0-9]{8}$/', $phone)) {
    $message = "Phone number must be 10 digits and start with 07.";
} elseif (!in_array($availability, ['Available', 'Not Available'])) {
        $message = "Invalid availability value.";
    } else {
        $update = mysqli_prepare($conn,
            "UPDATE doctors
             SET name = ?, phone = ?, room_number = ?, availability = ?
             WHERE doctor_id = ?");
        mysqli_stmt_bind_param($update, "ssssi",
    $name, $phone, $room_number, $availability, $id);

        if (mysqli_stmt_execute($update)) {
            header("Location: view_doctors.php?updated=1");
            exit();
        } else {
            $message = "Error updating doctor: " . htmlspecialchars(mysqli_error($conn));
        }
    }

    // Keep the form populated with the attempted values if validation failed
    $doctor['name'] = $name;
    $doctor['phone'] = $phone;
    $doctor['room_number'] = $room_number;
    $doctor['availability'] = $availability;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Doctor | Smart Hospital & Pharmacy Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body{ background-color:#f4f6f9; font-family:'Segoe UI', Roboto, sans-serif; }
        .navbar-brand{ font-weight:700; }
        .navbar-brand i{ margin-right:8px; color:#4dabf7; }
        .form-card{ background:#fff; border-radius:14px; box-shadow:0 2px 10px rgba(0,0,0,0.06); padding:2rem; max-width:600px; margin:2rem auto; }
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
        <h3><i class="bi bi-pencil-square me-2"></i>Edit Doctor</h3>

        <?php if($message != ""): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($doctor['name']); ?>" class="form-control" required>
            </div>

           <div class="mb-3">
    <label class="form-label">Phone</label>

    <input type="tel"
           name="phone"
           value="<?php echo htmlspecialchars($doctor['phone']); ?>"
           class="form-control"
           pattern="^07[0-9]{8}$"
           maxlength="10"
           minlength="10"
           placeholder="07XXXXXXXX"
           required>
</div>

            <div class="mb-3">
                <label class="form-label">Room Number</label>
                <input type="text" name="room_number" value="<?php echo htmlspecialchars($doctor['room_number'] ?? ''); ?>" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Availability</label>
                <select name="availability" class="form-select" required>
                    <option value="Available" <?php echo ($doctor['availability'] ?? '') == 'Available' ? 'selected' : ''; ?>>Available</option>
                    <option value="Not Available" <?php echo ($doctor['availability'] ?? '') == 'Not Available' ? 'selected' : ''; ?>>Not Available</option>
                </select>
            </div>

            <button type="submit" name="update" class="btn btn-save">Update Doctor</button>
            <a href="view_doctors.php" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>

<footer class="text-center py-3">
    <hr>
    <p>Smart Hospital Management System @ 2026</p>
</footer>

</body>
</html>