<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$search = "";
$base_query = "
SELECT
    d.doctor_id,
    d.name,
    d.phone,
    d.room_number,
    d.availability,
    dc.category_name,
    u.email
FROM doctors d
LEFT JOIN doctor_categories dc ON d.category_id = dc.category_id
LEFT JOIN users u ON d.user_id = u.id
";

if (isset($_GET['search']) && trim($_GET['search']) !== "") {

    $search = trim($_GET['search']);
    $like = "%" . $search . "%";

    $stmt = mysqli_prepare($conn, $base_query . " WHERE d.name LIKE ? ORDER BY d.doctor_id DESC");
    mysqli_stmt_bind_param($stmt, "s", $like);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

} else {
    $result = mysqli_query($conn, $base_query . " ORDER BY d.doctor_id DESC");
}

if (!$result) {
    die("SQL Error: " . htmlspecialchars(mysqli_error($conn)));
}

$total = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctors | Smart Hospital & Pharmacy Management System</title>

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
        table{ margin-bottom:0; }
        thead th{ background:#1e293b; color:#fff; font-weight:600; font-size:0.85rem; text-transform:uppercase; letter-spacing:0.4px; border:none; vertical-align:middle; }
        tbody td{ vertical-align:middle; font-size:0.92rem; }
        .badge-available{ background:#ebfbee; color:#2b8a3e; font-weight:600; padding:0.35em 0.7em; border-radius:8px; }
        .badge-unavailable{ background:#fff5f5; color:#c92a2a; font-weight:600; padding:0.35em 0.7em; border-radius:8px; }
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
            <h2><i class="bi bi-person-badge me-2"></i>Doctors</h2>
            <p><?php echo $total; ?> doctor<?php echo $total != 1 ? 's' : ''; ?> found</p>
        </div>
        <div>
            <a href="dashboard.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
            <a href="add_doctor.php" class="btn btn-primary"><i class="bi bi-person-plus"></i> Add Doctor</a>
        </div>
    </div>

    <div class="toolbar">
        <form method="GET" class="row g-2">
            <div class="col-md-8 col-sm-7">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control"
                           placeholder="Search by doctor name..."
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-2 col-sm-3 d-grid">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
            <div class="col-md-2 col-sm-2 d-grid">
                <a href="view_doctors.php" class="btn btn-outline-secondary">Show All</a>
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
                        <th>Name</th>
                        <th>Email (Login)</th>
                        <th>Category</th>
                        <th>Room</th>
                        <th>Availability</th>
                        <th>Phone</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td>#<?php echo (int)$row['doctor_id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo !empty($row['email']) ? htmlspecialchars($row['email']) : '<span class="text-muted">No Login</span>'; ?></td>
                        <td><?php echo !empty($row['category_name']) ? htmlspecialchars($row['category_name']) : '<span class="text-muted">No Category</span>'; ?></td>
                        <td><?php echo htmlspecialchars($row['room_number']); ?></td>
                        <td>
                            <?php if($row['availability'] == 'Available'): ?>
                                <span class="badge-available">Available</span>
                            <?php else: ?>
                                <span class="badge-unavailable"><?php echo htmlspecialchars($row['availability']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td class="text-center">
                            <a href="edit_doctor.php?id=<?php echo (int)$row['doctor_id']; ?>" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <a href="delete_doctor.php?id=<?php echo (int)$row['doctor_id']; ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Delete this doctor? This cannot be undone.');">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                No doctors found<?php echo $search !== "" ? ' for "' . htmlspecialchars($search) . '"' : ''; ?>.
            </div>
        <?php endif; ?>
    </div>

</div>

<footer class="text-center py-3 mt-4">
    <hr>
    <p>Smart Hospital Management System @ 2026</p>
</footer>

</body>
</html>