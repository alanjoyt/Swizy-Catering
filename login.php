<?php
require_once('config/autoload.php');
$dao = new DataAccess();
$elements = array(
    "email" => "",
    "password" => ""
);
$form = new FormAssist($elements, $_POST);
$errors = array();

if(isset($_POST['btnLogin'])) {
    $rules = array(
        "email" => array("required"=>true, "email"=>true),
        "password" => array("required"=>true)
    );
    $validator = new FormValidator($rules, array());
    if($validator->validate($_POST)) {
        $email = $dao->escape(trim($_POST['email']));
        $password = trim($_POST['password']);
        
        $users = $dao->getData("*", "users", "email='$email' AND active_status=1");
        if(!empty($users)) {
            $user = $users[0];
            if(password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];
                
                // Role-based redirection
                if($user['role'] == 'admin') {
                    header("Location: admin/dashboard.php");
                } elseif($user['role'] == 'customer') {
                    header("Location: customer/index.php");
                } else {
                    header("Location: staff/dashboard.php");
                }
                exit();
            } else {
                $errors[] = "Invalid password.";
            }
        } else {
            $errors[] = "User not found or inactive.";
        }
    } else {
        $errors = $validator->getErrors();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Swizy Catering</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('assets/images/hero-bg.jpg') center/cover no-repeat;
            background-attachment: fixed;
            color: var(--text-light);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .brand-font { font-family: 'Playfair Display', serif; }
        .text-muted { color: var(--text-muted) !important; }
        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            padding: 40px;
        }
        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(212, 175, 55, 0.3);
            color: white;
        }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--primary-gold);
            color: white;
            box-shadow: 0 0 10px rgba(212, 175, 55, 0.2);
        }
        .btn-gold {
            background: linear-gradient(45deg, #b8860b, #d4af37);
            color: #000;
            font-weight: 600;
            border: none;
            border-radius: 30px;
            padding: 10px 30px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
        }
        .btn-gold:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.4);
        }
        .back-link {
            color: var(--primary-gold);
            text-decoration: none;
            font-size: 0.9rem;
            transition: 0.3s;
        }
        .back-link:hover { color: #fff; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="glass-card">
                    <div class="text-center mb-4">
                        <h1 class="brand-font text-gold" style="color: var(--primary-gold);">SWIZY</h1>
                        <p class="text-muted">Welcome back. Please login.</p>
                    </div>

                    <?php if(!empty($errors)): ?>
                        <div class="alert alert-danger" style="background: rgba(220,53,69,0.1); border-color: #dc3545; color: #ff6b6b;">
                            <ul class="mb-0">
                                <?php foreach($errors as $err): ?>
                                    <li><?= $err ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label text-muted">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text" style="background: transparent; border-color: rgba(212, 175, 55, 0.3); color: var(--primary-gold);">
                                    <i class="fa fa-envelope"></i>
                                </span>
                                <?= $form->textBox('email', array('class'=>'form-control', 'placeholder'=>'Enter your email')) ?>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted">Password</label>
                            <div class="input-group">
                                <span class="input-group-text" style="background: transparent; border-color: rgba(212, 175, 55, 0.3); color: var(--primary-gold);">
                                    <i class="fa fa-lock"></i>
                                </span>
                                <?= $form->passwordBox('password', array('class'=>'form-control', 'placeholder'=>'Enter your password')) ?>
                            </div>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" name="btnLogin" class="btn btn-gold">Secure Login</button>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="index.php" class="back-link"><i class="fa fa-arrow-left me-1"></i> Back to Home</a>
                            <a href="register.php" class="back-link">Create Account <i class="fa fa-arrow-right ms-1"></i></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
