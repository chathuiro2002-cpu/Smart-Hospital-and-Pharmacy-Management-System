<?php
session_start();

include '../config/database.php';
include '../config/notification.php';

// DOCTOR LOGIN CHECK

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'doctor') {
    header("Location: ../login.php");
    exit();
}

// DOCTOR SESSION CHECK

if (!isset($_SESSION['doctor_id'])) {
    die("Doctor session not found. Please login again.");
}

$doctor_id = intval($_SESSION['doctor_id']);

// GET LOGGED-IN DOCTOR DETAILS

$doc_stmt = mysqli_prepare(
    $conn,
    "SELECT doctor_id, name, room_number
     FROM doctors
     WHERE doctor_id = ?"
);

mysqli_stmt_bind_param($doc_stmt, "i", $doctor_id);
mysqli_stmt_execute($doc_stmt);

$doctor = mysqli_fetch_assoc(
    mysqli_stmt_get_result($doc_stmt)
);

if (!$doctor) {
    die("Doctor record not found.");
}

// GET DOCTOR'S APPOINTMENTS

$appointments_stmt = mysqli_prepare(
    $conn,
    "SELECT 
        a.appointment_id,
        a.patient_id,
        a.appointment_date,
        a.status,
        a.queue_number,
        p.patient_token,
        p.name AS patient_name
     FROM appointments a
     INNER JOIN patients p
        ON a.patient_id = p.patient_id
     WHERE a.doctor_id = ?
     ORDER BY a.appointment_date DESC, a.appointment_id DESC"
);

mysqli_stmt_bind_param(
    $appointments_stmt,
    "i",
    $doctor_id
);

mysqli_stmt_execute($appointments_stmt);

$appointments_result = mysqli_stmt_get_result(
    $appointments_stmt
);

// SELECTED APPOINTMENT

$appointment_id = 0;
$appointment = null;
$message = "";
$message_type = "info";

// GET APPOINTMENT FROM URL

if (isset($_GET['appointment_id']) && ctype_digit((string)$_GET['appointment_id'])) {

    $appointment_id = intval($_GET['appointment_id']);

    /*
     * IMPORTANT:
     * We check doctor_id here.
     * This prevents a doctor from opening another doctor's
     * appointment by changing the appointment_id in the URL.
     */

    $appointment_stmt = mysqli_prepare(
        $conn,
        "SELECT
            a.appointment_id,
            a.patient_id,
            a.doctor_id,
            a.appointment_date,
            a.status,
            a.queue_number,
            p.patient_token,
            p.name AS patient_name,
            p.phone AS patient_phone
         FROM appointments a
         INNER JOIN patients p
            ON a.patient_id = p.patient_id
         WHERE a.appointment_id = ?
         AND a.doctor_id = ?"
    );

    mysqli_stmt_bind_param(
        $appointment_stmt,
        "ii",
        $appointment_id,
        $doctor_id
    );

    mysqli_stmt_execute($appointment_stmt);

    $appointment = mysqli_fetch_assoc(
        mysqli_stmt_get_result($appointment_stmt)
    );

    if (!$appointment) {

        $message = "Appointment not found or this appointment does not belong to you.";
        $message_type = "danger";

        $appointment_id = 0;
    }
}

// SAVE PRESCRIPTION

if (isset($_POST['save'])) {

    $appointment_id = intval($_POST['appointment_id']);

    $diagnosis = trim($_POST['diagnosis']);
    $notes = trim($_POST['notes']);
    $prescription_date = $_POST['prescription_date'];

    $medicine_ids = $_POST['medicine_id'] ?? [];
    $dosages = $_POST['dosage'] ?? [];
    $quantities = $_POST['quantity'] ?? [];


    // -------------------------------------------------
    // VERIFY APPOINTMENT BELONGS TO LOGGED-IN DOCTOR
    // -------------------------------------------------

    $verify_stmt = mysqli_prepare(
        $conn,
        "SELECT
            a.appointment_id,
            a.patient_id,
            a.doctor_id,
            a.status,
            p.patient_token,
            p.name AS patient_name
         FROM appointments a
         INNER JOIN patients p
            ON a.patient_id = p.patient_id
         WHERE a.appointment_id = ?
         AND a.doctor_id = ?"
    );

    mysqli_stmt_bind_param(
        $verify_stmt,
        "ii",
        $appointment_id,
        $doctor_id
    );

    mysqli_stmt_execute($verify_stmt);

    $appointment = mysqli_fetch_assoc(
        mysqli_stmt_get_result($verify_stmt)
    );


    if (!$appointment) {

        $message = "Invalid appointment.";
        $message_type = "danger";

    } elseif ($appointment['status'] == 'Completed') {

        $message = "This appointment has already been completed.";
        $message_type = "warning";

    } elseif ($diagnosis === "") {

        $message = "Please enter the diagnosis.";
        $message_type = "danger";

    } elseif (empty($medicine_ids)) {

        $message = "Please add at least one medicine.";
        $message_type = "danger";

    } else {


        // -------------------------------------------------
        // DATE VALIDATION
        // -------------------------------------------------

        $date_obj = DateTime::createFromFormat(
            'Y-m-d',
            $prescription_date
        );

        $valid_date =
            $date_obj &&
            $date_obj->format('Y-m-d') === $prescription_date;


        if (!$valid_date) {

            $message = "Please provide a valid prescription date.";
            $message_type = "danger";

        } else {


            // -------------------------------------------------
            // CHECK DUPLICATE PRESCRIPTION
            // -------------------------------------------------

            $duplicate = false;

            $check_stmt = mysqli_prepare(
                $conn,
                "SELECT prescription_id
                 FROM prescriptions
                 WHERE appointment_id = ?
                 LIMIT 1"
            );

            mysqli_stmt_bind_param(
                $check_stmt,
                "i",
                $appointment_id
            );

            mysqli_stmt_execute($check_stmt);

            $check_result = mysqli_stmt_get_result(
                $check_stmt
            );

            if (mysqli_num_rows($check_result) > 0) {
                $duplicate = true;
            }


            if ($duplicate) {

                $message =
                    "A prescription already exists for this appointment.";

                $message_type = "warning";

            } else {


                // -------------------------------------------------
                // INSERT PRESCRIPTION
                // -------------------------------------------------

                $patient_id = intval(
                    $appointment['patient_id']
                );

                $insert_stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO prescriptions
                    (
                        patient_id,
                        doctor_id,
                        appointment_id,
                        diagnosis,
                        notes,
                        prescription_date
                    )
                    VALUES (?, ?, ?, ?, ?, ?)"
                );

                mysqli_stmt_bind_param(
                    $insert_stmt,
                    "iiisss",
                    $patient_id,
                    $doctor_id,
                    $appointment_id,
                    $diagnosis,
                    $notes,
                    $prescription_date
                );


                if (mysqli_stmt_execute($insert_stmt)) {

                    $prescription_id =
                        mysqli_insert_id($conn);


                    // -------------------------------------------------
                    // INSERT MEDICINES
                    // -------------------------------------------------

                    $med_stmt = mysqli_prepare(
                        $conn,
                        "INSERT INTO prescription_medicines
                        (
                            prescription_id,
                            medicine_id,
                            dosage,
                            quantity
                        )
                        VALUES (?, ?, ?, ?)"
                    );


                    for (
                        $i = 0;
                        $i < count($medicine_ids);
                        $i++
                    ) {

                        $medicine_id =
                            intval($medicine_ids[$i]);

                        $dosage =
                            trim($dosages[$i] ?? '');

                        $quantity =
                            intval($quantities[$i] ?? 0);


                        if (
                            $medicine_id > 0 &&
                            $dosage !== "" &&
                            $quantity > 0
                        ) {

                            mysqli_stmt_bind_param(
                                $med_stmt,
                                "iisi",
                                $prescription_id,
                                $medicine_id,
                                $dosage,
                                $quantity
                            );

                            mysqli_stmt_execute(
                                $med_stmt
                            );
                        }
                    }


                    // -------------------------------------------------
                    // COMPLETE APPOINTMENT
                    // -------------------------------------------------

                    $complete_stmt = mysqli_prepare(
                        $conn,
                        "UPDATE appointments
                         SET status = 'Completed'
                         WHERE appointment_id = ?
                         AND doctor_id = ?"
                    );

                    mysqli_stmt_bind_param(
                        $complete_stmt,
                        "ii",
                        $appointment_id,
                        $doctor_id
                    );

                    mysqli_stmt_execute(
                        $complete_stmt
                    );


                    // -------------------------------------------------
                    // CREATE PHARMACY QUEUE
                    // -------------------------------------------------

                    $queue_number =
                        "PQ" .
                        str_pad(
                            $prescription_id,
                            5,
                            "0",
                            STR_PAD_LEFT
                        );


                    $queue_stmt = mysqli_prepare(
                        $conn,
                        "INSERT INTO pharmacy_queue
                        (
                            patient_id,
                            prescription_id,
                            queue_number,
                            status
                        )
                        VALUES (?, ?, ?, 'Waiting')"
                    );

                    mysqli_stmt_bind_param(
                        $queue_stmt,
                        "iis",
                        $patient_id,
                        $prescription_id,
                        $queue_number
                    );

                    mysqli_stmt_execute(
                        $queue_stmt
                    );


                    // -------------------------------------------------
                    // NOTIFICATION
                    // -------------------------------------------------

                    addNotification(
                        $conn,
                        $patient_id,
                        "Prescription Created",
                        "Your prescription has been created.\nPharmacy Queue Number: "
                        . $queue_number
                    );


                    // -------------------------------------------------
                    // SMS
                    // -------------------------------------------------

                    if (function_exists('sendSMS')) {

                        $phone_stmt = mysqli_prepare(
                            $conn,
                            "SELECT phone
                             FROM patients
                             WHERE patient_id = ?"
                        );

                        mysqli_stmt_bind_param(
                            $phone_stmt,
                            "i",
                            $patient_id
                        );

                        mysqli_stmt_execute(
                            $phone_stmt
                        );

                        $patient_phone =
                            mysqli_fetch_assoc(
                                mysqli_stmt_get_result(
                                    $phone_stmt
                                )
                            );


                        $count_result = mysqli_query(
                            $conn,
                            "SELECT COUNT(*) AS total
                             FROM pharmacy_queue
                             WHERE status = 'Waiting'"
                        );

                        $current_queue =
                            mysqli_fetch_assoc(
                                $count_result
                            )['total'];


                        if (
                            $patient_phone &&
                            !empty($patient_phone['phone'])
                        ) {

                            $sms_text =
                                "Prescription Ready.\n"
                                . "Pharmacy Queue No: "
                                . $queue_number
                                . "\nCurrent Queue: "
                                . $current_queue;

                            sendSMS(
                                $patient_phone['phone'],
                                $sms_text
                            );
                        }
                    }


                    $message =
                        "Prescription added successfully! Pharmacy Queue No: "
                        . $queue_number;

                    $message_type = "success";


                    // Reload appointment so it shows Completed
                    $reload_stmt = mysqli_prepare(
                        $conn,
                        "SELECT
                            a.appointment_id,
                            a.patient_id,
                            a.doctor_id,
                            a.appointment_date,
                            a.status,
                            a.queue_number,
                            p.patient_token,
                            p.name AS patient_name,
                            p.phone AS patient_phone
                         FROM appointments a
                         INNER JOIN patients p
                            ON a.patient_id = p.patient_id
                         WHERE a.appointment_id = ?
                         AND a.doctor_id = ?"
                    );

                    mysqli_stmt_bind_param(
                        $reload_stmt,
                        "ii",
                        $appointment_id,
                        $doctor_id
                    );

                    mysqli_stmt_execute(
                        $reload_stmt
                    );

                    $appointment =
                        mysqli_fetch_assoc(
                            mysqli_stmt_get_result(
                                $reload_stmt
                            )
                        );

                } else {

                    $message =
                        "Error creating prescription: "
                        . mysqli_error($conn);

                    $message_type = "danger";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Doctor Appointments |
        Smart Hospital & Pharmacy Management System
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet">


    <style>

        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Roboto, sans-serif;
        }

        .navbar-brand {
            font-weight: 700;
        }

        .navbar-brand i {
            margin-right: 8px;
            color: #4dabf7;
        }

        .main-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            padding: 2rem;
            margin: 2rem auto;
        }

        .main-card h3 {
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1.25rem;
        }

        .section-title {
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1rem;
        }

        .form-label {
            font-weight: 600;
            color: #334155;
            font-size: 0.9rem;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
        }

        .btn-save {
            background: linear-gradient(
                135deg,
                #4dabf7,
                #1971c2
            );

            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 10px;
        }

        .appointment-card {
            border: 1px solid #dee2e6;
            border-radius: 12px;
            transition: 0.2s;
        }

        .appointment-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transform: translateY(-1px);
        }

        .appointment-number {
            font-size: 1.05rem;
            font-weight: 700;
        }

        .patient-token {
            color: #1971c2;
            font-weight: 700;
        }

        .patient-details {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .detail-label {
            font-size: 0.8rem;
            color: #64748b;
            font-weight: 600;
        }

        .detail-value {
            font-size: 1rem;
            color: #1e293b;
            font-weight: 600;
        }

        thead th {
            background: #1e293b;
            color: #fff;
        }

        footer {
            color: #94a3b8;
            font-size: 0.9rem;
        }

    </style>

</head>


<body>


<!-- =====================================================
     NAVIGATION
====================================================== -->

<nav class="navbar navbar-dark bg-dark shadow-sm">

    <div class="container-fluid">

        <span class="navbar-brand">
            <i class="bi bi-hospital"></i>
            Smart Hospital & Pharmacy Management System
        </span>

        <div class="d-flex align-items-center gap-2">

            <span class="text-white">
                Dr. <?php echo htmlspecialchars($doctor['name']); ?>
            </span>

            <a
                href="../logout.php"
                class="btn btn-outline-light btn-sm">

                <i class="bi bi-box-arrow-right"></i>
                Logout

            </a>

        </div>

    </div>

</nav>


<div class="container">


<!-- =====================================================
     MAIN CARD
====================================================== -->

<div class="main-card">


<?php if ($message != ""): ?>

    <div class="alert alert-<?php echo $message_type; ?>">

        <?php echo htmlspecialchars($message); ?>

    </div>

<?php endif; ?>


<!-- =====================================================
     APPOINTMENT LIST
====================================================== -->

<?php if ($appointment_id == 0): ?>

    <h3>
        <i class="bi bi-calendar-check me-2"></i>
        My Appointments
    </h3>
<a href="dashboard.php" class="btn btn-secondary">Back</a>
    <p class="text-muted">
        Select an appointment number to view the patient
        details and create a prescription.
    </p>


    <?php if (mysqli_num_rows($appointments_result) > 0): ?>

        <div class="row g-3">

        <?php while ($a = mysqli_fetch_assoc($appointments_result)): ?>

            <div class="col-md-6">

                <div class="appointment-card p-3">

                    <div class="d-flex justify-content-between
                                align-items-center mb-2">

                        <span class="appointment-number">

                            Appointment #
                            <?php echo (int)$a['appointment_id']; ?>

                        </span>


                        <?php if ($a['status'] == 'Completed'): ?>

                            <span class="badge bg-success">
                                Completed
                            </span>

                        <?php else: ?>

                            <span class="badge bg-warning text-dark">
                                <?php echo htmlspecialchars($a['status']); ?>
                            </span>

                        <?php endif; ?>

                    </div>


                    <div class="mb-2">

                        <strong>
                            Patient:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $a['patient_name']
                        );
                        ?>

                    </div>


                    <div class="mb-2">

                        <strong>
                            Patient Token:
                        </strong>

                        <span class="patient-token">

                            <?php
                            echo htmlspecialchars(
                                $a['patient_token']
                            );
                            ?>

                        </span>

                    </div>


                    <div class="mb-2">

                        <strong>
                            Queue No:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $a['queue_number']
                        );
                        ?>

                    </div>


                    <div class="mb-3">

                        <strong>
                            Date:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $a['appointment_date']
                        );
                        ?>

                    </div>


                    <a
                        href="add_prescription.php?appointment_id=<?php echo (int)$a['appointment_id']; ?>"
                        class="btn btn-primary btn-sm">

                        <i class="bi bi-eye"></i>

                        View Appointment

                    </a>

                </div>

            </div>

        <?php endwhile; ?>

        </div>


    <?php else: ?>

        <div class="alert alert-info">

            <i class="bi bi-info-circle me-2"></i>

            You currently have no appointments.

        </div>

    <?php endif; ?>


<?php else: ?>


<!-- =====================================================
     SELECTED APPOINTMENT
====================================================== -->

    <h3>

        <i class="bi bi-person-vcard me-2"></i>

        Patient Details

    </h3>


    <?php if ($appointment): ?>


        <div class="patient-details">


            <div class="row g-4">


                <div class="col-md-4">

                    <div class="detail-label">
                        Appointment Number
                    </div>

                    <div class="detail-value">

                        #
                        <?php
                        echo (int)$appointment['appointment_id'];
                        ?>

                    </div>

                </div>


                <div class="col-md-4">

                    <div class="detail-label">
                        Patient Token
                    </div>

                    <div class="detail-value text-primary">

                        <?php
                        echo htmlspecialchars(
                            $appointment['patient_token']
                        );
                        ?>

                    </div>

                </div>


                <div class="col-md-4">

                    <div class="detail-label">
                        Patient Name
                    </div>

                    <div class="detail-value">

                        <?php
                        echo htmlspecialchars(
                            $appointment['patient_name']
                        );
                        ?>

                    </div>

                </div>


                <div class="col-md-4">

                    <div class="detail-label">
                        Queue Number
                    </div>

                    <div class="detail-value">

                        <?php
                        echo htmlspecialchars(
                            $appointment['queue_number']
                        );
                        ?>

                    </div>

                </div>


                <div class="col-md-4">

                    <div class="detail-label">
                        Appointment Date
                    </div>

                    <div class="detail-value">

                        <?php
                        echo htmlspecialchars(
                            $appointment['appointment_date']
                        );
                        ?>

                    </div>

                </div>


                <div class="col-md-4">

                    <div class="detail-label">
                        Status
                    </div>

                    <div class="detail-value">

                        <?php if ($appointment['status'] == 'Completed'): ?>

                            <span class="badge bg-success">
                                Completed
                            </span>

                        <?php else: ?>

                            <span class="badge bg-warning text-dark">

                                <?php
                                echo htmlspecialchars(
                                    $appointment['status']
                                );
                                ?>

                            </span>

                        <?php endif; ?>

                    </div>

                </div>


            </div>


            <!-- =================================================
                 PATIENT HISTORY BUTTON
            ================================================== -->

            <div class="mt-4 pt-3 border-top">

                <a href="../receptionist/patient_history.php?patient_id=<?php echo (int)$appointment['patient_id']; ?>"
                    class="btn btn-info text-white">
                        <i class="bi bi-clock-history"></i>
                        View Patient History
                    </a>
                <a
                    href="add_prescription.php"
                    class="btn btn-secondary">

                    <i class="bi bi-arrow-left"></i>

                    Back to Appointments

                </a>

            </div>

        </div>


        <!-- =================================================
             PRESCRIPTION FORM
        ================================================== -->

        <?php if ($appointment['status'] != 'Completed'): ?>


            <h4 class="section-title mt-4">

                <i class="bi bi-file-earmark-medical me-2"></i>

                Create Prescription

            </h4>


            <form method="POST">


                <input
                    type="hidden"
                    name="appointment_id"
                    value="<?php echo (int)$appointment['appointment_id']; ?>">


                <!-- DOCTOR -->

                <div class="mb-3">

                    <label class="form-label">
                        Doctor
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        value="Dr. <?php echo htmlspecialchars($doctor['name']); ?>"
                        readonly>

                </div>


                <!-- DIAGNOSIS -->

                <div class="mb-3">

                    <label class="form-label">
                        Diagnosis
                    </label>

                    <textarea
                        name="diagnosis"
                        class="form-control"
                        rows="3"
                        required></textarea>

                </div>


                <!-- NOTES -->

                <div class="mb-3">

                    <label class="form-label">
                        Notes
                    </label>

                    <textarea
                        name="notes"
                        class="form-control"
                        rows="3"></textarea>

                </div>


                <!-- MEDICINES -->

                <h5 class="mt-4 mb-3">
                    Medicines
                </h5>


                <table
                    class="table table-bordered"
                    id="medicineTable">

                    <thead>

                        <tr>

                            <th>
                                Medicine
                            </th>

                            <th>
                                Dosage
                            </th>

                            <th>
                                Quantity
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <tr>

                            <td>

                                <select
                                    name="medicine_id[]"
                                    class="form-select"
                                    required>

                                    <option value="">
                                        Select Medicine
                                    </option>


                                    <?php

                                    $med = mysqli_query(
                                        $conn,
                                        "SELECT medicine_id, medicine_name
                                         FROM medicines
                                         ORDER BY medicine_name"
                                    );

                                    while (
                                        $m = mysqli_fetch_assoc($med)
                                    ):

                                    ?>

                                        <option
                                            value="<?php echo (int)$m['medicine_id']; ?>">

                                            <?php
                                            echo htmlspecialchars(
                                                $m['medicine_name']
                                            );
                                            ?>

                                        </option>

                                    <?php endwhile; ?>

                                </select>

                            </td>


                            <td>

                                <input
                                    type="text"
                                    name="dosage[]"
                                    class="form-control"
                                    placeholder="e.g. 1 tablet twice daily"
                                    required>

                            </td>


                            <td>

                                <input
                                    type="number"
                                    name="quantity[]"
                                    class="form-control"
                                    min="1"
                                    required>

                            </td>


                            <td>

                                <button
                                    type="button"
                                    class="btn btn-danger btn-sm"
                                    onclick="removeRow(this)">

                                    <i class="bi bi-trash"></i>

                                </button>

                            </td>

                        </tr>

                    </tbody>

                </table>


                <button
                    type="button"
                    class="btn btn-outline-primary mb-3"
                    onclick="addMedicine()">

                    <i class="bi bi-plus-circle"></i>

                    Add Medicine

                </button>


                <!-- DATE -->

                <div class="mb-3">

                    <label class="form-label">
                        Prescription Date
                    </label>

                    <input
                        type="date"
                        name="prescription_date"
                        class="form-control"
                        value="<?php echo date('Y-m-d'); ?>"
                        required>

                </div>


                <!-- SAVE -->

                <button
                    type="submit"
                    name="save"
                    class="btn btn-save">

                    <i class="bi bi-check-circle"></i>

                    Save Prescription

                </button>


            </form>


        <?php else: ?>


            <div class="alert alert-success mt-4">

                <i class="bi bi-check-circle me-2"></i>

                This appointment has already been completed
                and a prescription has been created.

            </div>


        <?php endif; ?>


    <?php endif; ?>


<?php endif; ?>


</div>

</div>


<!-- =====================================================
     FOOTER
====================================================== -->

<footer class="text-center py-3">

    <hr>

    <p>
        Smart Hospital Management System &copy; 2026
    </p>

</footer>


<!-- =====================================================
     JAVASCRIPT
====================================================== -->

<script>

function addMedicine() {

    var table =
        document.getElementById("medicineTable");

    var tbody =
        table.querySelector("tbody");

    var row =
        tbody.rows[0].cloneNode(true);


    row.querySelectorAll("input").forEach(
        function(input) {

            input.value = "";

        }
    );


    row.querySelector("select").selectedIndex = 0;


    tbody.appendChild(row);
}


function removeRow(btn) {

    var tbody =
        document.querySelector(
            "#medicineTable tbody"
        );


    if (tbody.rows.length > 1) {

        btn.closest("tr").remove();

    }

}

</script>


</body>

</html>