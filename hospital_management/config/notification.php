<?php

function addNotification($conn, $patient_id, $title, $message)
{
    $patient_id = intval($patient_id);

    $stmt = mysqli_prepare($conn,
        "INSERT INTO notifications (patient_id, title, message) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iss", $patient_id, $title, $message);
    mysqli_stmt_execute($stmt);
}
?>
