<?php
session_start();

// If already logged in, skip the landing page and go straight to the right dashboard.
if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: admin/dashboard.php");
            break;
        case 'doctor':
            header("Location: doctor/dashboard.php");
            break;
        case 'receptionist':
            header("Location: receptionist/dashboard.php");
            break;
        case 'pharmacist':
            header("Location: pharmacist/dashboard.php");
            break;
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Hospital & Pharmacy Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        html, body{ height:100%; }

        body{
            background: linear-gradient(135deg, #1971c2, #0b3d91);
            font-family: 'Segoe UI', Roboto, sans-serif;
            display:flex;
            flex-direction:column;
            min-height:100vh;
            color:#fff;
        }

        nav{
            padding:1.25rem 2rem;
        }

        .brand{
            font-weight:700;
            font-size:1.1rem;
            display:flex;
            align-items:center;
            gap:0.5rem;
        }

        .hero{
            flex:1;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:2rem 1rem;
        }

        .hero-inner{
            max-width:700px;
            text-align:center;
        }

        .hero-icon{
            width:88px;
            height:88px;
            border-radius:22px;
            background:rgba(255,255,255,0.12);
            display:flex;
            align-items:center;
            justify-content:center;
            margin:0 auto 1.5rem;
            font-size:2.5rem;
            backdrop-filter: blur(4px);
        }

        .hero h1{
            font-weight:700;
            font-size:2.25rem;
            margin-bottom:0.75rem;
        }

        .hero p{
            color:rgba(255,255,255,0.85);
            font-size:1.05rem;
            margin-bottom:2rem;
        }

        .btn-cta{
            background:#fff;
            color:#1971c2;
            border:none;
            border-radius:10px;
            padding:0.75rem 2rem;
            font-weight:700;
            transition: all .15s ease;
        }

        .btn-cta:hover{
            transform: translateY(-2px);
            box-shadow:0 8px 20px rgba(0,0,0,0.25);
            color:#1971c2;
        }

        .feature-row{
            margin-top:3rem;
            display:flex;
            justify-content:center;
            gap:1.5rem;
            flex-wrap:wrap;
        }

        .feature{
            background:rgba(255,255,255,0.1);
            border-radius:14px;
            padding:1.25rem 1.5rem;
            width:180px;
            backdrop-filter: blur(4px);
        }

        .feature i{
            font-size:1.5rem;
            margin-bottom:0.5rem;
            display:block;
        }

        .feature span{
            font-size:0.9rem;
            font-weight:600;
        }

        footer{
            color:rgba(255,255,255,0.75);
            font-size:0.85rem;
            text-align:center;
            padding:1.25rem 0;
        }

        footer hr{
            border-color:rgba(255,255,255,0.2);
        }
    </style>
</head>
<body>

<nav class="d-flex justify-content-between align-items-center">
    <div class="brand">
        <i class="bi bi-hospital"></i>
        Smart Hospital & Pharmacy
    </div>
    <a href="login.php" class="btn btn-outline-light btn-sm">
        <i class="bi bi-box-arrow-in-right me-1"></i>Login
    </a>
</nav>

<div class="hero">
    <div class="hero-inner">
        <div class="hero-icon">
            <i class="bi bi-heart-pulse"></i>
        </div>
        <h1>Smart Hospital & Pharmacy Management System</h1>
        <p>One platform for patient records, appointments, prescriptions, and pharmacy inventory &mdash; built to keep every department in sync.</p>

        <a href="login.php" class="btn btn-cta">
            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In to Continue
        </a>

        <div class="feature-row">
            <div class="feature">
                <i class="bi bi-person-badge"></i>
                <span>Doctors</span>
            </div>
            <div class="feature">
                <i class="bi bi-people"></i>
                <span>Patients</span>
            </div>
            <div class="feature">
                <i class="bi bi-calendar-check"></i>
                <span>Appointments</span>
            </div>
            <div class="feature">
                <i class="bi bi-capsule"></i>
                <span>Pharmacy</span>
            </div>
        </div>
    </div>
</div>

<footer>
    <hr>
    <p class="mb-0">Smart Hospital Management System &copy; 2026</p>
</footer>

</body>
</html>