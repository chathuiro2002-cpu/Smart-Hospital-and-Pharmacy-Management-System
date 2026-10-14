<?php
include 'config/database.php';

$message = "";
$message_type = "info";

if(isset($_POST['reset']))
{
    $email = trim($_POST['email']);
    $new_password = $_POST['new_password'];

    if(strlen($new_password) < 6)
    {
        $message = "Password must be at least 6 characters long.";
        $message_type = "danger";
    }
    else
    {
        // Prepared statement - prevents SQL injection
        $check = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        $result = mysqli_stmt_get_result($check);

        if(mysqli_num_rows($result) > 0)
        {
            // Hash the password before storing it
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $update = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE email = ?");
            mysqli_stmt_bind_param($update, "ss", $hashed_password, $email);
            mysqli_stmt_execute($update);

            $message = "Password reset successfully! You can now log in.";
            $message_type = "success";
        }
        else
        {
            // Same generic message either way — don't reveal whether an email exists
            $message = "If that email exists in our system, the password has been processed.";
            $message_type = "info";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Smart Hospital & Pharmacy Management System</title>

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

        .wrapper{
            flex:1;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:2rem 1rem;
        }

        .reset-card{
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

        .title{
            text-align:center;
            font-weight:700;
            color:#1e293b;
            font-size:1.15rem;
            margin-bottom:0.25rem;
        }

        .subtitle{
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

        .btn-reset{
            background:linear-gradient(135deg,#4dabf7,#1971c2);
            border:none;
            border-radius:10px;
            padding:0.7rem;
            font-weight:600;
            color:#fff;
            width:100%;
            transition: all .15s ease;
        }

        .btn-reset:hover{
            box-shadow:0 6px 16px rgba(25,113,194,0.35);
            transform: translateY(-1px);
            color:#fff;
        }

        .btn-back{
            display:block;
            text-align:center;
            margin-top:0.75rem;
            border-radius:10px;
            padding:0.6rem;
            font-weight:600;
        }

        .alert-box{
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

<div class="wrapper">
    <div class="reset-card">

        <div class="brand-icon">
            <i class="bi bi-key"></i>
        </div>
        <div class="title">Reset Your Password</div>
        <div class="subtitle">Smart Hospital & Pharmacy Management System</div>

        <?php if($message != ""): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-box py-2 px-3 mb-3">
                <i class="bi bi-info-circle me-1"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" novalidate>

            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="you@hospital.com" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">New Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="new_password" class="form-control" placeholder="At least 6 characters" required minlength="6">
                </div>
            </div>

            <button type="submit" name="reset" class="btn-reset">
                <i class="bi bi-arrow-repeat me-1"></i> Reset Password
            </button>

            <a href="login.php" class="btn btn-outline-secondary btn-back">
                <i class="bi bi-arrow-left me-1"></i> Back to Login
            </a>

        </form>

    </div>
</div>

<footer>
    <hr>
    <p class="mb-0">Smart Hospital Management System @ 2026</p>
</footer>

</body>
</html>