<?php
session_start();
include 'config/database.php';

/* =========================
   UPDATE DOCTOR STATUS
========================= */
if (isset($_SESSION['role']) && $_SESSION['role'] == 'doctor' && isset($_SESSION['doctor_id'])) {

    $stmt = mysqli_prepare($conn, "UPDATE doctors SET availability = 'Not Available' WHERE doctor_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['doctor_id']);
    mysqli_stmt_execute($stmt);
}

/* =========================
   DESTROY SESSION
========================= */
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

/* =========================
   REDIRECT TO LOGIN
========================= */
header("Location: login.php");
exit();