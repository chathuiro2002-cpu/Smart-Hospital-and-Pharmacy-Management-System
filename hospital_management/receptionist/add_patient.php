<?php
session_start();
include '../config/database.php';
include '../config/notification.php';

if (!isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

$message = "";
$message_type = "info";

if (isset($_POST['save'])) {

    $name = trim($_POST['name']);
    $nic = trim($_POST['nic']);
    $date_of_birth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    // Server-side validation (client-side pattern attributes can be bypassed)
    $errors = [];

    if ($name === "") $errors[] = "Name is required.";
    if (!preg_match('/^([0-9]{9}[vVxX]|[0-9]{12})$/', $nic)) $errors[] = "Invalid NIC format.";
    if (!in_array($gender, ['Male', 'Female'])) $errors[] = "Invalid gender.";
    if (!preg_match('/^07[0-9]{8}$/', $phone)) $errors[] = "Invalid phone number.";

    $dob = DateTime::createFromFormat('Y-m-d', $date_of_birth);
    if (!$dob || $dob->format('Y-m-d') !== $date_of_birth || $dob > new DateTime()) {
        $errors[] = "Invalid date of birth.";
    }

    if (!empty($errors)) {
        $message = implode(' ', $errors);
        $message_type = "danger";
    } else {

        $check = mysqli_prepare($conn, "SELECT patient_id FROM patients WHERE nic = ?");
        mysqli_stmt_bind_param($check, "s", $nic);
        mysqli_stmt_execute($check);
        $check_result = mysqli_stmt_get_result($check);

        if (mysqli_num_rows($check_result) > 0) {
            $message = "NIC already exists.";
            $message_type = "danger";
        } else {

            $today = new DateTime();
            $age = $today->diff($dob)->y;

            $insert = mysqli_prepare($conn,
                "INSERT INTO patients (name, nic, age, gender, phone, address, date_of_birth)
                 VALUES (?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($insert, "ssissss",
                $name,
                $nic,
                $age,
                $gender,
                $phone,
                $address,
                $date_of_birth
            );

            if (mysqli_stmt_execute($insert)) {

                $patient_id = mysqli_insert_id($conn);
                $token = "PT" . str_pad($patient_id, 6, "0", STR_PAD_LEFT);

                $token_stmt = mysqli_prepare($conn, "UPDATE patients SET patient_token = ? WHERE patient_id = ?");
                mysqli_stmt_bind_param($token_stmt, "si", $token, $patient_id);
                mysqli_stmt_execute($token_stmt);

                addNotification(
                    $conn,
                    $patient_id,
                    "Welcome to Smart Hospital",
                    "Welcome to Smart Hospital.\nYour Lifetime Patient Token is: " . $token
                );

                $message = "Patient added successfully! Token: " . $token;
                $message_type = "success";

            } else {
                $message = "Error: " . htmlspecialchars(mysqli_error($conn));
                $message_type = "danger";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Patient | Smart Hospital & Pharmacy Management System</title>

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
        <h3><i class="bi bi-person-add me-2"></i>Add Patient</h3>

        <?php if($message != ""): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Patient Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">NIC Number</label>
                <input type="text" name="nic" class="form-control"
                       pattern="^([0-9]{9}[vVxX]|[0-9]{12})$"
                       title="Enter a valid Sri Lankan NIC" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Date of Birth</label>
                <input type="date" name="date_of_birth" id="dob" class="form-control" max="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Age</label>
                <input type="number" id="age" class="form-control" readonly>
            </div>

            <div class="mb-3">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-select" required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Phone</label>
                <input type="tel" name="phone" class="form-control"
                       pattern="^07[0-9]{8}$" maxlength="10" minlength="10"
                       placeholder="07XXXXXXXX" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control" required></textarea>
            </div>

            <button type="submit" name="save" class="btn btn-save">Save Patient</button>
            <a href="dashboard.php" class="btn btn-secondary">Back</a>

        </form>
    </div>
</div>

<footer class="text-center py-3">
    <hr>
    <p>Smart Hospital Management System &copy; 2026</p>
</footer>

<script>
document.getElementById("dob").addEventListener("change", function () {
    let dob = new Date(this.value);
    let today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    let month = today.getMonth() - dob.getMonth();
    if (month < 0 || (month === 0 && today.getDate() < dob.getDate())) {
        age--;
    }
    document.getElementById("age").value = age;
});
</script>

</body>
</html>