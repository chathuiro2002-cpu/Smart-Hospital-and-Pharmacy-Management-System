<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

$low_stock = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM medicines WHERE quantity < 10"));

$search = "";

if (isset($_GET['search']) && trim($_GET['search']) !== "") {
    $search = trim($_GET['search']);
    $like = "%" . $search . "%";

    $stmt = mysqli_prepare($conn, "SELECT * FROM medicines WHERE medicine_name LIKE ? ORDER BY medicine_name");
    mysqli_stmt_bind_param($stmt, "s", $like);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, "SELECT * FROM medicines ORDER BY medicine_name");
}

$total = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicines | Smart Hospital & Pharmacy Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body{ background-color:#f4f6f9; font-family:'Segoe UI', Roboto, sans-serif; }
        .navbar-brand{ font-weight:700; }
        .navbar-brand i{ margin-right:8px; color:#4dabf7; }
        .page-header{ margin-top:2rem; margin-bottom:1.25rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem; }
        .page-header h2{ font-weight:700; color:#1e293b; margin-bottom:0.15rem; }
        .page-header p{ color:#64748b; margin-bottom:0; font-size:0.9rem; }
        .toolbar{ background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06); padding:1rem; margin-bottom:1.25rem; }
        .table-card{ background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06); overflow:hidden; }
        thead th{ background:#1e293b; color:#fff; font-weight:600; font-size:0.85rem; text-transform:uppercase; }
        tbody td{ vertical-align:middle; font-size:0.92rem; }
        .empty-state{ text-align:center; padding:3rem 1rem; color:#94a3b8; }
        .empty-state i{ font-size:2.5rem; margin-bottom:0.5rem; display:block; }
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
        <div>
            <h2><i class="bi bi-capsule me-2"></i>Medicines</h2>
            <p><?php echo $total; ?> medicine<?php echo $total != 1 ? 's' : ''; ?> found</p>
        </div>
        <a href="dashboard.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
    </div>

    <?php if($low_stock > 0): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i>
            Warning! <?php echo $low_stock; ?> medicine(s) are low in stock.
        </div>
    <?php endif; ?>

    <div class="toolbar">
        <form method="GET" class="row g-2">
            <div class="col-md-9 col-sm-8">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control"
                           placeholder="Search by medicine name..."
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-3 col-sm-4 d-grid">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>
    </div>

    <div class="table-card">
        <?php if($total > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Medicine Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Stock Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td>#<?php echo (int)$row['medicine_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['medicine_name']); ?></td>
                        <td><?php echo (int)$row['quantity']; ?></td>
                        <td>$<?php echo number_format((float)$row['price'], 2); ?></td>
                        <td>
                            <?php if($row['quantity'] < 10): ?>
                                <span class="badge bg-danger">LOW STOCK</span>
                            <?php else: ?>
                                <span class="badge bg-success">IN STOCK</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                No medicines found<?php echo $search !== "" ? ' for "' . htmlspecialchars($search) . '"' : ''; ?>.
            </div>
        <?php endif; ?>
    </div>

</div>

<footer class="text-center py-3 mt-4">
    <hr>
    <p>Smart Hospital Management System &copy; 2026</p>
</footer>

</body>
</html>