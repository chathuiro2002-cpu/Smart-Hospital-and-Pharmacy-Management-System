<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'doctor') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id'])) {
    header("Location: current_queue.php");
    exit();
}

$id = (int)$_GET['id'];

$stmt = mysqli_prepare($conn, "UPDATE appointments SET status = 'Completed' WHERE appointment_id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

header("Location: current_queue.php");
exit();