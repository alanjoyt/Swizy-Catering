<?php
require_once('../config/autoload.php');
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Swizy</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-gold: #d4af37;
            --dark-bg: #121212;
            --sidebar-bg: #0a0a0a;
            --card-bg: #1e1e1e;
            --text-light: #ffffff;
            --text-muted: #aaaaaa;
        }
        body { background-color: var(--dark-bg); color: var(--text-light); font-family: 'Poppins', sans-serif; overflow-x: hidden; }
        .brand-font { font-family: 'Playfair Display', serif; }
        .text-muted { color: var(--text-muted) !important; }
        
        .sidebar {
            width: 250px; background: var(--sidebar-bg); height: 100vh; position: fixed; top: 0; left: 0;
            border-right: 1px solid rgba(212, 175, 55, 0.2); transition: 0.3s; z-index: 1000;
        }
        .sidebar-brand {
            padding: 20px; font-size: 1.5rem; text-align: center; border-bottom: 1px solid rgba(212, 175, 55, 0.2);
            color: var(--primary-gold); font-weight: bold;
        }
        .nav-link { color: var(--text-muted); padding: 15px 20px; font-size: 0.95rem; display: flex; align-items: center; }
        .nav-link i { margin-right: 10px; width: 20px; text-align: center; }
        .nav-link:hover, .nav-link.active { color: var(--primary-gold); background: rgba(212, 175, 55, 0.05); }
        
        .main-content { margin-left: 250px; padding: 20px; transition: 0.3s; }
        .topbar {
            background: var(--sidebar-bg); padding: 15px 20px; display: flex; justify-content: space-between;
            align-items: center; border-bottom: 1px solid rgba(212, 175, 55, 0.2); border-radius: 10px; margin-bottom: 20px;
        }
        .card { background: var(--card-bg); border: 1px solid rgba(212, 175, 55, 0.1); border-radius: 10px; color: var(--text-light); }
        .card-header { background: rgba(0,0,0,0.2); border-bottom: 1px solid rgba(212, 175, 55, 0.1); color: var(--primary-gold); }
        
        .btn-gold {
            background: linear-gradient(45deg, #b8860b, #d4af37); color: #000; font-weight: 600; border: none;
            padding: 8px 20px; border-radius: 5px; transition: 0.3s;
        }
        .btn-gold:hover { background: linear-gradient(45deg, #d4af37, #f3e5ab); transform: translateY(-2px); }
        .table { color: var(--text-light); }
        .table th { border-bottom: 1px solid rgba(212, 175, 55, 0.3); color: var(--primary-gold); font-weight: 500; }
        .table td { border-bottom: 1px solid rgba(255, 255, 255, 0.05); vertical-align: middle; }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand brand-font">SWIZY ADMIN</div>
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php"><i class="fa fa-tachometer-alt"></i> Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="reports.php"><i class="fa fa-chart-pie"></i> Reports Engine</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="bookings.php"><i class="fa fa-calendar-alt"></i> Bookings</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="calendar.php"><i class="fa fa-calendar"></i> Event Calendar</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="menu.php"><i class="fa fa-utensils"></i> Menus & Packages</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="items.php"><i class="fa fa-list"></i> Manage Items</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="staff.php"><i class="fa fa-users"></i> Staff Management</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="manage_leaves.php"><i class="fa fa-calendar-times"></i> Leave Management</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="gallery.php"><i class="fa fa-images"></i> Manage Gallery</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="feedback_view.php"><i class="fa fa-star"></i> View Feedback</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="customers.php"><i class="fa fa-user-friends"></i> Customers</a>
            </li>
            <li class="nav-item mt-5">
                <a class="nav-link text-danger" href="../logout.php" onclick="return confirm('Are you sure you want to logout?');"><i class="fa fa-sign-out-alt"></i> Logout</a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <h4 class="mb-0 brand-font text-gold" style="color: var(--primary-gold);">Dashboard</h4>
            <div class="d-flex align-items-center">
                <span class="me-3">Welcome, <?= htmlspecialchars($_SESSION['name']) ?></span>
                <img src="../assets/images/default.png" alt="Profile" class="rounded-circle me-3" width="40" height="40" style="object-fit: cover; border: 2px solid var(--primary-gold);" onerror="this.src='https://ui-avatars.com/api/?name=Admin&background=d4af37&color=000'">
                <a href="../logout.php" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to logout?');"><i class="fa fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
