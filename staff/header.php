<?php
require_once('../config/autoload.php');
if(!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['chef', 'server', 'site_manager'])) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard - Swizy</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root { --primary-gold: #d4af37; --dark-bg: #121212; --navbar-bg: #0a0a0a; --card-bg: #1e1e1e; --text-light: #ffffff; --text-muted: #aaaaaa; }
        body { background-color: var(--dark-bg); color: var(--text-light); font-family: 'Poppins', sans-serif; }
        .brand-font { font-family: 'Playfair Display', serif; }
        .text-muted { color: var(--text-muted) !important; }
        .navbar { background: var(--navbar-bg); border-bottom: 1px solid rgba(212, 175, 55, 0.2); }
        .navbar-brand { color: var(--primary-gold) !important; font-size: 1.5rem; font-weight: bold; }
        .nav-link { color: var(--text-light) !important; }
        .card { background: var(--card-bg); border: 1px solid rgba(212, 175, 55, 0.1); border-radius: 10px; color: var(--text-light); }
        .card-header { background: rgba(0,0,0,0.2); border-bottom: 1px solid rgba(212, 175, 55, 0.1); color: var(--primary-gold); }
        .table { color: var(--text-light); }
        .table th { border-bottom: 1px solid rgba(212, 175, 55, 0.3); color: var(--primary-gold); }
        .table td { border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand brand-font" href="dashboard.php">SWIZY <span class="fs-6 text-muted font-monospace">Staff Panel</span></a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">My Assignments</a></li>
                    <li class="nav-item"><a class="nav-link" href="leave_request.php">Leave Requests</a></li>
                    <li class="nav-item"><a class="nav-link" href="reports.php">Reports & Income</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="../logout.php" onclick="return confirm('Are you sure you want to logout?');">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container py-4">
