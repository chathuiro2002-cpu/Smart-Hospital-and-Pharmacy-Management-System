<?php
session_start();

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'pharmacist')
{
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacist Dashboard | Smart Hospital & Pharmacy Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body{ background-color:#f4f6f9; font-family:'Segoe UI', Roboto, sans-serif; }
        .navbar-brand{ font-weight:700; }
        .navbar-brand i{ margin-right:8px; color:#4dabf7; }
        .page-header{ margin-top:2rem; margin-bottom:1.5rem; }
        .page-header h2{ font-weight:700; color:#1e293b; }
        .page-header p{ color:#64748b; margin-bottom:0; }
        .action-card{ border:none; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06); padding:1.1rem 1rem; text-decoration:none; display:flex; align-items:center; gap:0.75rem; color:#1e293b; font-weight:600; transition: all .15s ease; height:100%; background:#fff; }
        .action-card:hover{ box-shadow:0 6px 16px rgba(0,0,0,0.12); transform: translateY(-2px); color:#1e293b; }
        .action-card i{ font-size:1.3rem; width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; color:#fff; flex-shrink:0; }
        .icon-warning{ background:#fcc419; }
        .icon-success{ background:#51cf66; }
        .icon-dark{ background:#343a40; }
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
        <h2>Pharmacist Dashboard</h2>
        <p>Welcome back, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong>.</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <a href="add_medicine.php" class="action-card">
                <i class="bi bi-plus-circle icon-warning"></i> Add Medicine
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="view_medicines.php" class="action-card">
                <i class="bi bi-list-ul icon-success"></i> View Medicines
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="view_pharmacy_queue.php" class="action-card">
                <i class="bi bi-hourglass-split icon-dark"></i> Pharmacy Queue
            </a>
        </div>
    </div>

</div>

<footer class="text-center py-3">
    <hr>
    <p>Smart Hospital Management System &copy; 2026</p>
</footer>

</body>
</html>