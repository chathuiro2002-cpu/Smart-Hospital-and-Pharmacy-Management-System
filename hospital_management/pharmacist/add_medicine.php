<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['pharmacist', 'admin'])) {
    header("Location: ../login.php");
    exit();
}

$message = "";
$message_type = "info";

if (isset($_POST['save'])) {

    $medicine_name = trim($_POST['medicine_name']);
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];

    if ($medicine_name === "" || !is_numeric($quantity) || $quantity < 0 || !is_numeric($price) || $price < 0) {
        $message = "Please enter a valid medicine name, quantity, and price.";
        $message_type = "danger";
    } else {

        $quantity = (int)$quantity;
        $price = (float)$price;

        $check = mysqli_prepare($conn,
            "SELECT medicine_id FROM medicines WHERE TRIM(LOWER(medicine_name)) = TRIM(LOWER(?))");
        mysqli_stmt_bind_param($check, "s", $medicine_name);
        mysqli_stmt_execute($check);
        $check_result = mysqli_stmt_get_result($check);

        if (mysqli_num_rows($check_result) > 0) {

            $row = mysqli_fetch_assoc($check_result);
            $medicine_id = $row['medicine_id'];

            $update = mysqli_prepare($conn,
                "UPDATE medicines SET quantity = quantity + ?, price = ? WHERE medicine_id = ?");
            mysqli_stmt_bind_param($update, "idi", $quantity, $price, $medicine_id);

            if (mysqli_stmt_execute($update)) {
                $message = "Medicine stock updated successfully!";
                $message_type = "success";
            } else {
                $message = "Error: " . htmlspecialchars(mysqli_error($conn));
                $message_type = "danger";
            }

        } else {

            $insert = mysqli_prepare($conn,
                "INSERT INTO medicines (medicine_name, quantity, price) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($insert, "sid", $medicine_name, $quantity, $price);

            if (mysqli_stmt_execute($insert)) {
                $message = "Medicine added successfully!";
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
    <title>Add Medicine | Smart Hospital & Pharmacy Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body{ background-color:#f4f6f9; font-family:'Segoe UI', Roboto, sans-serif; }
        .navbar-brand{ font-weight:700; }
        .navbar-brand i{ margin-right:8px; color:#4dabf7; }
        .form-card{ background:#fff; border-radius:14px; box-shadow:0 2px 10px rgba(0,0,0,0.06); padding:2rem; max-width:520px; margin:2rem auto; }
        .form-card h3{ font-weight:700; color:#1e293b; margin-bottom:1.25rem; }
        .form-label{ font-weight:600; color:#334155; font-size:0.9rem; }
        .form-control{ border-radius:10px; }
        .btn-save{ background:linear-gradient(135deg,#fcc419,#f08c00); border:none; color:#fff; font-weight:600; border-radius:10px; }
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
        <h3><i class="bi bi-capsule me-2"></i>Add Medicine</h3>

        <?php if($message != ""): ?>
            <div class="alert alert-<?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Medicine Name</label>
                <input type="text" name="medicine_name" class="form-control" placeholder="e.g. Paracetamol 500mg" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" class="form-control" min="0" placeholder="Quantity to add" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Price</label>
                <input type="number" step="0.01" min="0" name="price" class="form-control" placeholder="Unit price" required>
            </div>
            <div class="form-text mb-3">
                <i class="bi bi-info-circle"></i>
                If this medicine already exists, the quantity will be added to existing stock and the price will be updated.
            </div>
            <button type="submit" name="save" class="btn btn-save">Save Medicine</button>
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