<?php
require_once('config/autoload.php');
$dao = new DataAccess();

$elements = array(
    "name" => "",
    "email" => "",
    "phone" => "",
    "password" => "",
    "confirm_password" => ""
);
$form = new FormAssist($elements, $_POST);
$errors = array();

if(isset($_POST['btnRegister'])) {
    $rules = array(
        "name" => array("required"=>true, "minlength"=>3, "alphaspaceonly"=>true),
        "email" => array("required"=>true, "email"=>true, "unique"=>array("field"=>"email", "table"=>"users")),
        "phone" => array("required"=>true, "minlength"=>10, "maxlength"=>15, "integeronly"=>true),
        "password" => array("required"=>true, "minlength"=>6),
        "confirm_password" => array("required"=>true, "compare"=>array("comparewith"=>"password", "operator"=>"="))
    );
    $validator = new FormValidator($rules, array());
    if($validator->validate($_POST)) {
        $data = array(
            "role" => "customer",
            "name" => trim($_POST['name']),
            "email" => trim($_POST['email']),
            "phone" => trim($_POST['phone']),
            "password" => password_hash($_POST['password'], PASSWORD_DEFAULT),
            "profile_image" => "default.png",
            "availability_status" => 1,
            "active_status" => 1
        );
        
        if($dao->insert($data, "users")) {
            swalRedirect("Registration Successful! Please login.", "login.php", "success", "Success");
        } else {
            $errors[] = "Registration failed. Please try again.";
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
    <title>Register - Swizy Catering</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.8)), url('assets/images/hero-bg.jpg') center/cover no-repeat;
            background-attachment: fixed;
            color: var(--text-light);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 40px 0;
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
            <div class="col-md-6">
                <div class="glass-card">
                    <div class="text-center mb-4">
                        <h1 class="brand-font text-gold" style="color: var(--primary-gold);">Join SWIZY</h1>
                        <p class="text-muted">Create an account to book your next exquisite event.</p>
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
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label text-muted">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background: transparent; border-color: rgba(212, 175, 55, 0.3); color: var(--primary-gold);">
                                        <i class="fa fa-user"></i>
                                    </span>
                                    <?= $form->textBox('name', array('class'=>'form-control', 'placeholder'=>'John Doe', 'required'=>'required', 'minlength'=>'3')) ?>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background: transparent; border-color: rgba(212, 175, 55, 0.3); color: var(--primary-gold);">
                                        <i class="fa fa-envelope"></i>
                                    </span>
                                    <?= $form->inputBox('email', array('class'=>'form-control', 'placeholder'=>'email@domain.com', 'required'=>'required'), 'email') ?>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label text-muted">Phone Number</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background: transparent; border-color: rgba(212, 175, 55, 0.3); color: var(--primary-gold);">
                                        <i class="fa fa-phone"></i>
                                    </span>
                                    <?= $form->inputBox('phone', array('class'=>'form-control', 'placeholder'=>'10-digit number', 'pattern'=>'[0-9]{10}', 'maxlength'=>'10', 'minlength'=>'10', 'required'=>'required'), 'tel') ?>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label text-muted">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background: transparent; border-color: rgba(212, 175, 55, 0.3); color: var(--primary-gold);">
                                        <i class="fa fa-lock"></i>
                                    </span>
                                    <?= $form->passwordBox('password', array('class'=>'form-control', 'placeholder'=>'Min 6 chars', 'minlength'=>'6', 'required'=>'required')) ?>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <label class="form-label text-muted">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background: transparent; border-color: rgba(212, 175, 55, 0.3); color: var(--primary-gold);">
                                        <i class="fa fa-lock"></i>
                                    </span>
                                    <?= $form->passwordBox('confirm_password', array('class'=>'form-control', 'placeholder'=>'Re-enter password', 'minlength'=>'6', 'required'=>'required')) ?>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" name="btnRegister" class="btn btn-gold">Create Account</button>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="index.php" class="back-link"><i class="fa fa-arrow-left me-1"></i> Back to Home</a>
                            <a href="login.php" class="back-link">Already have an account? Login <i class="fa fa-arrow-right ms-1"></i></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
