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

$stmt = mysqli_prepare($conn, "DELETE FROM patients WHERE patient_id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    header("Location: view_patients.php?deleted=1");
    exit();
} else {
    header("Location: view_patients.php?deleted=0");
    exit();
}