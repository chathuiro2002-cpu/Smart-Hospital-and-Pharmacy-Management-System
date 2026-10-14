<?php
session_start();
include '../config/database.php';


// =====================================================
// LOGIN CHECK
// =====================================================

if (!isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}


// Allow only admin and doctor
if (
    $_SESSION['role'] != 'admin' &&
    $_SESSION['role'] != 'doctor'
) {
    header("Location: ../login.php");
    exit();
}


// =====================================================
// LOAD PATIENTS
// =====================================================

$patients = mysqli_query(
    $conn,
    "SELECT patient_id, patient_token, name
     FROM patients
     ORDER BY patient_token"
);


// =====================================================
// VARIABLES
// =====================================================

$history = null;
$selected_patient_id = 0;
$selected_patient = null;


// =====================================================
// GET PATIENT FROM URL
// =====================================================

if (
    isset($_GET['patient_id']) &&
    ctype_digit((string)$_GET['patient_id'])
) {

    $selected_patient_id = (int)$_GET['patient_id'];
}


// =====================================================
// GET PATIENT FROM FORM
// =====================================================

if (isset($_POST['search'])) {

    if (
        isset($_POST['patient_id']) &&
        ctype_digit((string)$_POST['patient_id'])
    ) {

        $selected_patient_id =
            (int)$_POST['patient_id'];
    }
}


// =====================================================
// LOAD SELECTED PATIENT DETAILS
// =====================================================

if ($selected_patient_id > 0) {

    $patient_stmt = mysqli_prepare(
        $conn,
        "SELECT
            patient_id,
            patient_token,
            name,
            phone
         FROM patients
         WHERE patient_id = ?"
    );

    mysqli_stmt_bind_param(
        $patient_stmt,
        "i",
        $selected_patient_id
    );

    mysqli_stmt_execute($patient_stmt);

    $patient_result =
        mysqli_stmt_get_result($patient_stmt);

    $selected_patient =
        mysqli_fetch_assoc($patient_result);


    if (!$selected_patient) {

        $selected_patient_id = 0;

    } else {


        // =====================================================
        // GET PATIENT HISTORY
        // =====================================================

        $stmt = mysqli_prepare(
            $conn,
            "
            SELECT

                p.prescription_id,

                a.appointment_id,

                a.appointment_date,

                a.queue_number,

                d.name AS doctor_name,

                p.diagnosis,

                p.notes,

                p.prescription_date,

                GROUP_CONCAT(
                    CONCAT(
                        m.medicine_name,
                        ' (',
                        pm.dosage,
                        ', Qty: ',
                        pm.quantity,
                        ')'
                    )
                    SEPARATOR '||'
                ) AS medicines

            FROM prescriptions p

            LEFT JOIN appointments a
                ON p.appointment_id = a.appointment_id

            LEFT JOIN doctors d
                ON p.doctor_id = d.doctor_id

            LEFT JOIN prescription_medicines pm
                ON p.prescription_id = pm.prescription_id

            LEFT JOIN medicines m
                ON pm.medicine_id = m.medicine_id

            WHERE p.patient_id = ?

            GROUP BY
                p.prescription_id,
                a.appointment_id,
                a.appointment_date,
                a.queue_number,
                d.name,
                p.diagnosis,
                p.notes,
                p.prescription_date

            ORDER BY
                p.prescription_date DESC,
                p.prescription_id DESC
            "
        );


        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $selected_patient_id
        );

        mysqli_stmt_execute($stmt);

        $history =
            mysqli_stmt_get_result($stmt);
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Patient History |
        Smart Hospital & Pharmacy Management System
    </title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >


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

        .content-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            padding: 2rem;
            margin: 2rem auto;
            max-width: 1200px;
        }

        .content-card h2 {
            font-weight: 700;
            color: #1e293b;
        }

        .patient-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .detail-label {
            color: #64748b;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .detail-value {
            color: #1e293b;
            font-size: 1rem;
            font-weight: 700;
        }

        .patient-token {
            color: #1971c2;
            font-weight: 700;
        }

        thead th {
            background: #1e293b;
            color: #fff;
            white-space: nowrap;
        }

        footer {
            color: #94a3b8;
            font-size: 0.9rem;
        }

    </style>

</head>


<body>


<!-- =====================================================
     NAVBAR
====================================================== -->

<nav class="navbar navbar-dark bg-dark shadow-sm">

    <div class="container-fluid">

        <span class="navbar-brand">

            <i class="bi bi-hospital"></i>

            Smart Hospital & Pharmacy Management System

        </span>


        <a
            href="../logout.php"
            class="btn btn-outline-light btn-sm"
        >

            <i class="bi bi-box-arrow-right"></i>

            Logout

        </a>

    </div>

</nav>


<div class="container">

    <div class="content-card">


        <!-- =================================================
             PAGE TITLE
        ================================================== -->

        <h2>

            <i class="bi bi-clock-history me-2"></i>

            Patient History

        </h2>


        <p class="text-muted">

            View previous appointments, diagnoses,
            prescriptions and medicines.

        </p>


        <!-- =================================================
             BACK BUTTON
        ================================================== -->

       <?php if ($_SESSION['role'] == 'doctor'): ?>

    <a href="../doctor/add_prescription.php"
       class="btn btn-secondary mb-3">
        <i class="bi bi-arrow-left"></i>
        Back to Appointments
    </a>

<?php elseif ($_SESSION['role'] == 'admin'): ?>

    <a href="../admin/dashboard.php"
       class="btn btn-secondary mb-3">
        <i class="bi bi-arrow-left"></i>
        Back to Dashboard
    </a>

<?php else: ?>

    <a href="dashboard.php"
       class="btn btn-secondary mb-3">
        <i class="bi bi-arrow-left"></i>
        Back
    </a>

<?php endif; ?>


        <!-- =================================================
             PATIENT SEARCH
        ================================================== -->

        <form
            method="POST"
            class="row g-2 align-items-end mb-3"
        >

            <div class="col-md-9">

                <label class="form-label">

                    Select Patient

                </label>


                <select
                    name="patient_id"
                    class="form-select"
                    required
                >

                    <option value="">

                        Select Patient Token

                    </option>


                    <?php while (
                        $patient = mysqli_fetch_assoc($patients)
                    ): ?>

                        <option
                            value="<?php echo (int)$patient['patient_id']; ?>"
                            <?php
                            echo (
                                $patient['patient_id']
                                == $selected_patient_id
                            )
                            ? 'selected'
                            : '';
                            ?>
                        >

                            <?php
                            echo htmlspecialchars(
                                $patient['patient_token']
                            );
                            ?>

                            -

                            <?php
                            echo htmlspecialchars(
                                $patient['name']
                            );
                            ?>

                        </option>

                    <?php endwhile; ?>

                </select>

            </div>


            <div class="col-md-3 d-grid">

                <button
                    type="submit"
                    name="search"
                    class="btn btn-primary"
                >

                    <i class="bi bi-search"></i>

                    View History

                </button>

            </div>

        </form>


        <hr>


        <!-- =================================================
             SELECTED PATIENT DETAILS
        ================================================== -->

        <?php if ($selected_patient): ?>


            <div class="patient-card">

                <h5 class="mb-4">

                    <i class="bi bi-person-vcard me-2"></i>

                    Patient Information

                </h5>


                <div class="row g-4">


                    <div class="col-md-3">

                        <div class="detail-label">

                            Patient Token

                        </div>

                        <div class="detail-value patient-token">

                            <?php
                            echo htmlspecialchars(
                                $selected_patient['patient_token']
                            );
                            ?>

                        </div>

                    </div>


                    <div class="col-md-3">

                        <div class="detail-label">

                            Patient Name

                        </div>

                        <div class="detail-value">

                            <?php
                            echo htmlspecialchars(
                                $selected_patient['name']
                            );
                            ?>

                        </div>

                    </div>


                    <div class="col-md-3">

                        <div class="detail-label">

                            Phone

                        </div>

                        <div class="detail-value">

                            <?php
                            echo htmlspecialchars(
                                $selected_patient['phone']
                            );
                            ?>

                        </div>

                    </div>


                    <div class="col-md-3">

                        <div class="detail-label">

                            Patient ID

                        </div>

                        <div class="detail-value">

                            <?php
                            echo (int)$selected_patient['patient_id'];
                            ?>

                        </div>

                    </div>


                </div>

            </div>


            <!-- =================================================
                 HISTORY TABLE
            ================================================== -->

            <h5 class="mb-3">

                <i class="bi bi-file-medical me-2"></i>

                Medical History

            </h5>


            <?php if (
                $history !== null &&
                mysqli_num_rows($history) > 0
            ): ?>


                <div class="table-responsive">

                    <table class="table table-bordered table-hover">

                        <thead>

                            <tr>

                                <th>
                                    Appointment
                                </th>

                                <th>
                                    Appointment Date
                                </th>

                                <th>
                                    Queue No
                                </th>

                                <th>
                                    Doctor
                                </th>

                                <th>
                                    Diagnosis
                                </th>

                                <th>
                                    Medicines
                                </th>

                                <th>
                                    Notes
                                </th>

                                <th>
                                    Prescription Date
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php while (
                            $row = mysqli_fetch_assoc($history)
                        ): ?>


                            <tr>

                                <td>

                                    #
                                    <?php
                                    echo htmlspecialchars(
                                        $row['appointment_id']
                                        ?? 'N/A'
                                    );
                                    ?>

                                </td>


                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $row['appointment_date']
                                        ?? 'N/A'
                                    );
                                    ?>

                                </td>


                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $row['queue_number']
                                        ?? 'N/A'
                                    );
                                    ?>

                                </td>


                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $row['doctor_name']
                                        ?? 'N/A'
                                    );
                                    ?>

                                </td>


                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $row['diagnosis']
                                        ?? ''
                                    );
                                    ?>

                                </td>


                                <td>

                                    <?php

                                    if (
                                        !empty(
                                            $row['medicines']
                                        )
                                    ) {

                                        $medicine_list =
                                            explode(
                                                '||',
                                                $row['medicines']
                                            );

                                        foreach (
                                            $medicine_list
                                            as $medicine
                                        ) {

                                            echo htmlspecialchars(
                                                $medicine
                                            );

                                            echo '<br>';
                                        }

                                    } else {

                                        echo
                                            '<span class="text-muted">
                                                None
                                            </span>';
                                    }

                                    ?>

                                </td>


                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $row['notes']
                                        ?? ''
                                    );
                                    ?>

                                </td>


                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $row['prescription_date']
                                        ?? ''
                                    );
                                    ?>

                                </td>

                            </tr>


                        <?php endwhile; ?>


                        </tbody>

                    </table>

                </div>


            <?php else: ?>


                <div class="alert alert-info">

                    <i class="bi bi-info-circle me-2"></i>

                    No medical history found for this patient.

                </div>


            <?php endif; ?>


        <?php endif; ?>


    </div>

</div>


<footer class="text-center py-3">

    <hr>

    <p>

        Smart Hospital Management System &copy; 2026

    </p>

</footer>


</body>

</html>