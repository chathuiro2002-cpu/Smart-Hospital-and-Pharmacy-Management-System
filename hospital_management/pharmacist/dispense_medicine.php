<?php
session_start();
include '../config/database.php';
include '../config/notification.php';

/* =========================
   AUTH CHECK — must happen before anything else
========================= */
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'pharmacist') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id'])) {
    die("Invalid prescription ID.");
}

$prescription_id = (int)$_GET['id'];

/* =========================
   CHECK QUEUE ENTRY
========================= */
$queue_stmt = mysqli_prepare($conn, "SELECT * FROM pharmacy_queue WHERE prescription_id = ?");
mysqli_stmt_bind_param($queue_stmt, "i", $prescription_id);
mysqli_stmt_execute($queue_stmt);
$queue = mysqli_fetch_assoc(mysqli_stmt_get_result($queue_stmt));

if (!$queue) {
    die("Prescription not found in Pharmacy Queue.");
}

if ($queue['status'] == "Completed") {
    die("Medicine has already been dispensed for this prescription.");
}

/* =========================
   GET MEDICINES FOR THIS PRESCRIPTION (single query, reused below)
========================= */
$med_stmt = mysqli_prepare($conn, "
    SELECT pm.medicine_id, pm.quantity, m.medicine_name, m.quantity AS stock
    FROM prescription_medicines pm
    JOIN medicines m ON pm.medicine_id = m.medicine_id
    WHERE pm.prescription_id = ?
");
mysqli_stmt_bind_param($med_stmt, "i", $prescription_id);
mysqli_stmt_execute($med_stmt);
$med_result = mysqli_stmt_get_result($med_stmt);

$medicines = [];
while ($row = mysqli_fetch_assoc($med_result)) {
    $medicines[] = $row;
}

if (count($medicines) == 0) {
    die("No medicines found for this prescription.");
}

/* =========================
   CHECK STOCK BEFORE MAKING ANY CHANGES
========================= */
foreach ($medicines as $row) {
    if ($row['stock'] < $row['quantity']) {
        die("Insufficient stock for " . htmlspecialchars($row['medicine_name']));
    }
}

/* =========================
   TRANSACTION: deduct stock + mark queue completed
========================= */
mysqli_begin_transaction($conn);

try {
    $deduct_stmt = mysqli_prepare($conn, "UPDATE medicines SET quantity = quantity - ? WHERE medicine_id = ?");
    $complete_stmt = mysqli_prepare($conn, "UPDATE pharmacy_queue SET status = 'Completed' WHERE prescription_id = ?");

    foreach ($medicines as $row) {
        mysqli_stmt_bind_param($deduct_stmt, "ii", $row['quantity'], $row['medicine_id']);
        mysqli_stmt_execute($deduct_stmt);
    }

    mysqli_stmt_bind_param($complete_stmt, "i", $prescription_id);
    mysqli_stmt_execute($complete_stmt);

    mysqli_commit($conn);

    /* =========================
       NOTIFICATION — only sent now, after a real successful dispense,
       using the actual patient from the queue record.
    ========================= */
    addNotification(
        $conn,
        $queue['patient_id'],
        "Medicine Dispensed",
        "Your medicines have been dispensed successfully."
    );

    header("Location: view_pharmacy_queue.php?success=1");
    exit();

} catch (Exception $e) {
    mysqli_rollback($conn);
    die("Dispense failed. Please try again or contact the administrator.");
}