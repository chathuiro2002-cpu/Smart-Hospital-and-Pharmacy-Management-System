<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_connect("localhost", "root", "", "hospital_management");
} catch (mysqli_sql_exception $e) {
    // Avoid leaking connection details in a raw stack trace.
    die("Database connection failed. Please contact the system administrator.");
}

mysqli_set_charset($conn, "utf8mb4");

?>