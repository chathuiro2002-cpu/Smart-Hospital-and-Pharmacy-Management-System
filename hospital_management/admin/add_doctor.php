<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$message = "";
$message_type = "info";

if (isset($_POST['save'])) {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $phone = trim($_POST['phone']);
    $room_number = trim($_POST['room_number']);
    $availability = $_POST['availability'];
    $category_id = intval($_POST['category_id']);

    // Basic validation
   if ($name === "" || $email === "" || $password === "" || $phone === "") {
    $message = "Please fill in all required fields.";
    $message_type = "danger";

} elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
    $message = "Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number.";
    $message_type = "danger";

} elseif (!preg_match('/^07[0-9]{8}$/', $phone)) {
    $message = "Please enter a valid Sri Lankan mobile number.";
    $message_type = "danger";

} elseif (!in_array($availability, ['Available', 'Not Available'], true)) {
    $message = "Invalid availability value.";
    $message_type = "danger";
        $message = "Invalid availability value.";
        $message_type = "danger";
    } else {

        // Check email isn't already used
        $check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        $check_result = mysqli_stmt_get_result($check);

        if (mysqli_num_rows($check_result) > 0) {
            $message = "That email is already in use by another account.";
            $message_type = "danger";
        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            mysqli_begin_transaction($conn);
            try {
                /* 1. INSERT INTO USERS (LOGIN) */
                $user_stmt = mysqli_prepare($conn,
                    "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'doctor')");
                mysqli_stmt_bind_param($user_stmt, "sss", $name, $email, $hashed_password);
                mysqli_stmt_execute($user_stmt);

                $user_id = mysqli_insert_id($conn);

                /* 2. INSERT INTO DOCTORS */
                $doctor_stmt = mysqli_prepare($conn,
                    "INSERT INTO doctors
                    (user_id, name, phone, room_number, availability, category_id)
                    VALUES (?, ?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param(
                        $doctor_stmt,
                        "issssi",
                        $user_id,
                        $name,
                        $phone,
                        $room_number,
                        $availability,
                        $category_id
                    );
                mysqli_stmt_execute($doctor_stmt);

                mysqli_commit($conn);

                $message = "Doctor added successfully!";
                $message_type = "success";

            } catch (mysqli_sql_exception $e) {
                mysqli_rollback($conn);
                $message = "Error adding doctor: " . htmlspecialchars($e->getMessage());
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
    <title>Add Doctor | Smart Hospital & Pharmacy Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body{ background-color:#f4f6f9; font-family:'Segoe UI', Roboto, sans-serif; }
        .navbar-brand{ font-weight:700; }
        .navbar-brand i{ margin-right:8px; color:#4dabf7; }
        .form-card{ background:#fff; border-radius:14px; box-shadow:0 2px 10px rgba(0,0,0,0.06); padding:2rem; max-width:640px; margin:2rem auto; }
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
        <h3><i class="bi bi-person-plus me-2"></i>Add Doctor</h3>

        <?php if($message != ""): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email (used for login)</label>
               <input type="email"
       name="email"
       class="form-control"
       placeholder="example@hospital.com"
       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
               <input type="password"
       name="password"
       class="form-control"
       pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$"
       title="Password must contain at least 8 characters, one uppercase letter, one lowercase letter and one number."
       required>
            </div>

            <div class="mb-3">
                 <label class="form-label">Phone</label>
                <input type="tel"
       name="phone"
       class="form-control"
       pattern="^07[0-9]{8}$"
       maxlength="10"
       minlength="10"
       placeholder="07XXXXXXXX"
       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select" required>
                    <option value="">Select Category</option>
                    <?php
                    $cats = mysqli_query($conn, "SELECT * FROM doctor_categories");
                    while($c = mysqli_fetch_assoc($cats)) {
                    ?>
                        <option value="<?php echo (int)$c['category_id']; ?>">
                            <?php echo htmlspecialchars($c['category_name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Room Number</label>
                <input type="text" name="room_number" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Availability</label>
                <select name="availability" class="form-select" required>
                    <option value="Available">Available</option>
                    <option value="Not Available">Not Available</option>
                </select>
            </div>

            <button type="submit" name="save" class="btn btn-save">Save Doctor</button>
            <a href="dashboard.php" class="btn btn-secondary">Back</a>
        </form>
    </div>
</div>

<footer class="text-center py-3">
    <hr>
    <p>Smart Hospital Management System @ 2026</p>
</footer>

</body>
</html>