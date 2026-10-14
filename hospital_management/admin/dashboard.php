<?php
session_start();
include '../config/database.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin')
{
    header("Location: ../login.php");
    exit();
}

$doctor_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM doctors"));
$patient_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM patients"));
$appointment_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM appointments"));
$medicine_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM medicines"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Hospital Management System | Admin Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body{
            background-color:#f4f6f9;
            font-family: 'Segoe UI', Roboto, sans-serif;
        }

        .navbar-brand{
            font-weight:700;
            letter-spacing:0.3px;
        }

        .navbar-brand i{
            margin-right:8px;
            color:#4dabf7;
        }

        .page-header{
            margin-top:2rem;
            margin-bottom:1.5rem;
        }

        .page-header h2{
            font-weight:700;
            color:#1e293b;
        }

        .page-header p{
            color:#64748b;
            margin-bottom:0;
        }

        /* Stat cards */
        .stat-card{
            border:none;
            border-radius:14px;
            box-shadow:0 2px 10px rgba(0,0,0,0.06);
            transition: transform .15s ease, box-shadow .15s ease;
            overflow:hidden;
        }

        .stat-card:hover{
            transform: translateY(-3px);
            box-shadow:0 8px 20px rgba(0,0,0,0.1);
        }

        .stat-icon{
            width:52px;
            height:52px;
            border-radius:12px;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:1.4rem;
            color:#fff;
        }

        .stat-value{
            font-size:1.8rem;
            font-weight:700;
            color:#1e293b;
        }

        .stat-label{
            color:#64748b;
            font-size:0.85rem;
            text-transform:uppercase;
            letter-spacing:0.5px;
        }

        .bg-doctors{ background:linear-gradient(135deg,#4dabf7,#1971c2); }
        .bg-patients{ background:linear-gradient(135deg,#51cf66,#2b8a3e); }
        .bg-appointments{ background:linear-gradient(135deg,#ff922b,#e8590c); }
        .bg-medicines{ background:linear-gradient(135deg,#cc5de8,#9c36b5); }

        /* Quick action section */
        .section-title{
            font-weight:700;
            color:#1e293b;
            margin-top:2.5rem;
            margin-bottom:1rem;
            font-size:1.1rem;
        }

        .action-card{
            border:none;
            border-radius:12px;
            box-shadow:0 2px 8px rgba(0,0,0,0.06);
            padding:1.1rem 1rem;
            text-decoration:none;
            display:flex;
            align-items:center;
            gap:0.75rem;
            color:#1e293b;
            font-weight:600;
            transition: all .15s ease;
            height:100%;
            background:#fff;
        }

        .action-card:hover{
            box-shadow:0 6px 16px rgba(0,0,0,0.12);
            transform: translateY(-2px);
            color:#1e293b;
        }

        .action-card i{
            font-size:1.3rem;
            width:40px;
            height:40px;
            border-radius:10px;
            display:flex;
            align-items:center;
            justify-content:center;
            color:#fff;
            flex-shrink:0;
        }

        .icon-primary{ background:#4dabf7; }
        .icon-success{ background:#51cf66; }
        .icon-info{ background:#22b8cf; }
        .icon-warning{ background:#fcc419; }
        .icon-secondary{ background:#868e96; }
        .icon-dark{ background:#343a40; }

        footer{
            color:#94a3b8;
            font-size:0.9rem;
        }
    </style>
</head>

<body>

<nav class="navbar navbar-dark bg-dark shadow-sm">
    <div class="container-fluid">
        <span class="navbar-brand">
            <i class="bi bi-hospital"></i>
            Smart Hospital & Pharmacy Management System
        </span>

        <a href="../logout.php" class="btn btn-outline-light btn-sm">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</nav>

<div class="container">

    <div class="page-header">
        <h2>Admin Dashboard</h2>
        <p>Welcome back, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong> — here's what's happening today.</p>
    </div>

    <!-- Stat cards -->
    <div class="row g-3">
        <div class="col-md-3 col-sm-6">
            <div class="card stat-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-doctors"><i class="bi bi-person-badge"></i></div>
                    <div>
                        <div class="stat-value"><?php echo $doctor_count; ?></div>
                        <div class="stat-label">Doctors</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card stat-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-patients"><i class="bi bi-people"></i></div>
                    <div>
                        <div class="stat-value"><?php echo $patient_count; ?></div>
                        <div class="stat-label">Patients</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card stat-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-appointments"><i class="bi bi-calendar-check"></i></div>
                    <div>
                        <div class="stat-value"><?php echo $appointment_count; ?></div>
                        <div class="stat-label">Appointments</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="card stat-card p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="stat-icon bg-medicines"><i class="bi bi-capsule"></i></div>
                    <div>
                        <div class="stat-value"><?php echo $medicine_count; ?></div>
                        <div class="stat-label">Medicines</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Doctors & Patients -->
    <div class="section-title"><i class="bi bi-person-lines-fill"></i> Doctors & Patients</div>
    <div class="row g-3">
        <div class="col-md-3 col-sm-6">
            <a href="add_doctor.php" class="action-card">
                <i class="bi bi-person-plus icon-primary"></i> Add Doctor
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="view_doctors.php" class="action-card">
                <i class="bi bi-list-ul icon-primary"></i> View Doctors
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="../receptionist/add_patient.php" class="action-card">
                <i class="bi bi-person-add icon-success"></i> Add Patient
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="../receptionist/view_patients.php" class="action-card">
                <i class="bi bi-list-ul icon-success"></i> View Patients
            </a>
        </div>
    </div>

    <!-- Appointments -->
    <div class="section-title"><i class="bi bi-calendar3"></i> Appointments & Queue</div>
    <div class="row g-3">
        <div class="col-md-3 col-sm-6">
            <a href="../receptionist/book_appointment.php" class="action-card">
                <i class="bi bi-calendar-plus icon-info"></i> Book Appointment
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="../receptionist/view_appointments.php" class="action-card">
                <i class="bi bi-calendar-week icon-info"></i> View Appointments
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="../queue/view_queue.php" class="action-card">
                <i class="bi bi-hourglass-split icon-dark"></i> View Queue
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
           <a href="../pharmacist/view_pharmacy_queue.php" class="action-card">
                <i class="bi bi-hourglass-split icon-dark"></i> Pharmacy Queue
            </a>
        </div>
    </div>

    <!-- Pharmacy & Prescriptions -->
    <div class="section-title"><i class="bi bi-capsule-pill"></i> Pharmacy & Prescriptions</div>
    <div class="row g-3">
        <div class="col-md-3 col-sm-6">
            <a href="../pharmacist/add_medicine.php" class="action-card">
                <i class="bi bi-plus-circle icon-warning"></i> Add Medicine
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="../pharmacist/view_medicines.php" class="action-card">
                <i class="bi bi-list-ul icon-warning"></i> View Medicines
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="../doctor/view_prescriptions.php" class="action-card">
                <i class="bi bi-file-earmark-text icon-secondary"></i> View Prescriptions
            </a>
        </div>
    </div>
    <!--
        "Add Prescription" intentionally removed from this dashboard.
        Writing a prescription requires a doctor_id tied to a specific
        doctor's login session, which an admin account doesn't have —
        clicking it here would only ever bounce back to this dashboard.
        Prescriptions should be added from the Doctor dashboard instead.
    -->

    <!-- Admin & System -->
    <div class="section-title"><i class="bi bi-gear"></i> Administration</div>
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <a href="add_user.php" class="action-card">
                <i class="bi bi-person-gear icon-dark"></i> Add User
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="reports.php" class="action-card">
                <i class="bi bi-bar-chart-line icon-dark"></i> Reports
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="../receptionist/patient_history.php" class="action-card">
                <i class="bi bi-clock-history icon-dark"></i> Patient History
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="backup.php" class="action-card">
                <i class="bi bi-database-down icon-dark"></i> Backup Database
            </a>
        </div>
        <div class="col-md-3 col-sm-6">
            <a href="system_info.php" class="action-card">
                <i class="bi bi-info-circle icon-primary"></i> System Information
            </a>
        </div>
    </div>

</div>

<footer class="text-center py-3">
    <hr>
    <p>Smart Hospital Management System &copy; 2026</p>
</footer>

</body>
</html>