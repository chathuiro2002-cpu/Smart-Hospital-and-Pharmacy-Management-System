<?php
session_start();
include 'config/database.php';

$error = "";

if (isset($_SESSION['role'])) {

    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: admin/dashboard.php");
            exit();

        case 'doctor':
            header("Location: doctor/dashboard.php");
            exit();

        case 'receptionist':
            header("Location: receptionist/dashboard.php");
            exit();

        case 'pharmacist':
            header("Location: pharmacist/dashboard.php");
            exit();

        default:
            header("Location: index.php");
            exit();
    }
}

if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {

        $error = "Please enter your email and password.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        $query = "SELECT * FROM users WHERE email = ? LIMIT 1";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {

            $user = mysqli_fetch_assoc($result);

           $password_ok = false;

                    if (password_verify($password, $user['password'])) {
                        $password_ok = true;
                    } elseif ($password === $user['password']) {
                        $password_ok = true;
                    }

                    if ($password_ok) {

                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] == 'doctor') {

                    $doc_stmt = mysqli_prepare($conn, "SELECT doctor_id FROM doctors WHERE user_id = ?");
                    mysqli_stmt_bind_param($doc_stmt, "i", $user['id']);
                    mysqli_stmt_execute($doc_stmt);
                    $doc_result = mysqli_stmt_get_result($doc_stmt);

                    if ($doc = mysqli_fetch_assoc($doc_result)) {

                        $_SESSION['doctor_id'] = $doc['doctor_id'];

                        $avail_stmt = mysqli_prepare($conn,
                            "UPDATE doctors
                             SET availability='Available'
                             WHERE doctor_id=?");
                        mysqli_stmt_bind_param($avail_stmt, "i", $doc['doctor_id']);
                        mysqli_stmt_execute($avail_stmt);
                    }
                }

                switch ($user['role']) {

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

                    default:
                        header("Location: index.php");
                        break;
                }

                exit();

            } else {

                $error = "Invalid Email or Password.";

            }

        } else {

            $error = "Invalid Email or Password.";

        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Smart Hospital & Pharmacy Management System</title>

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
        }

        .login-wrapper{
            flex:1;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:2rem 1rem;
        }

        .login-card{
            background:#fff;
            border-radius:16px;
            box-shadow:0 15px 35px rgba(0,0,0,0.25);
            max-width:420px;
            width:100%;
            padding:2.5rem 2.25rem;
        }

        .brand-icon{
            width:64px;
            height:64px;
            border-radius:16px;
            background:linear-gradient(135deg,#4dabf7,#1971c2);
            display:flex;
            align-items:center;
            justify-content:center;
            margin:0 auto 1rem;
            font-size:1.8rem;
            color:#fff;
            box-shadow:0 6px 16px rgba(25,113,194,0.35);
        }

        .brand-title{
            text-align:center;
            font-weight:700;
            color:#1e293b;
            font-size:1.15rem;
            margin-bottom:0.25rem;
        }

        .brand-subtitle{
            text-align:center;
            color:#64748b;
            font-size:0.9rem;
            margin-bottom:1.75rem;
        }

        .form-label{
            font-weight:600;
            color:#334155;
            font-size:0.9rem;
        }

        .form-control{
            border-radius:10px;
            padding:0.65rem 0.9rem;
            border:1px solid #e2e8f0;
        }

        .form-control:focus{
            border-color:#4dabf7;
            box-shadow:0 0 0 0.2rem rgba(77,171,247,0.2);
        }

        .input-group-text{
            background:#f8fafc;
            border:1px solid #e2e8f0;
            border-right:none;
            border-radius:10px 0 0 10px;
        }

        .input-group .form-control{
            border-radius:0 10px 10px 0;
        }

        .btn-login{
            background:linear-gradient(135deg,#4dabf7,#1971c2);
            border:none;
            border-radius:10px;
            padding:0.7rem;
            font-weight:600;
            color:#fff;
            width:100%;
            transition: all .15s ease;
        }

        .btn-login:hover{
            box-shadow:0 6px 16px rgba(25,113,194,0.35);
            transform: translateY(-1px);
            color:#fff;
        }

        .forgot-link{
            display:block;
            text-align:center;
            margin-top:1rem;
            color:#1971c2;
            text-decoration:none;
            font-size:0.9rem;
        }

        .forgot-link:hover{
            text-decoration:underline;
        }

        .alert-error{
            border-radius:10px;
            font-size:0.9rem;
        }

        footer{
            color:rgba(255,255,255,0.75);
            font-size:0.85rem;
            text-align:center;
            padding:1rem 0;
        }

        footer hr{
            border-color:rgba(255,255,255,0.2);
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">

        <div class="brand-icon">
            <i class="bi bi-hospital"></i>
        </div>
        <div class="brand-title">Smart Hospital & Pharmacy Management System</div>
        <div class="brand-subtitle">Sign in to continue</div>

        <?php if(!empty($error)): ?>
            <div class="alert alert-danger alert-error py-2 px-3 mb-3">
                <i class="bi bi-exclamation-circle me-1"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off" >

            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="you@hospital.com" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Enter your password" autocomplete="new-password" required>
                </div>
            </div>

            <button type="submit" name="login" class="btn-login mt-2">
                <i class="bi bi-box-arrow-in-right me-1"></i> Login
            </button>

            <a href="forgot_password.php" class="forgot-link">Forgot Password?</a>

        </form>

    </div>
</div>

<footer>
    <hr>
    <p class="mb-0">Smart Hospital Management System &copy; 2026</p>
</footer>

</body>
</html>