<?php
require_once('../config/autoload.php');
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - Swizy</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        :root {
            --primary-gold: #d4af37;
            --dark-bg: #121212;
            --navbar-bg: #0a0a0a;
            --card-bg: #1e1e1e;
            --text-light: #ffffff;
            --text-muted: #aaaaaa;
        }
        body { background-color: var(--dark-bg); color: var(--text-light); font-family: 'Poppins', sans-serif; overflow-x: hidden; }
        .brand-font { font-family: 'Playfair Display', serif; }
        .text-muted { color: var(--text-muted) !important; }
        
        .navbar {
            background: var(--navbar-bg); border-bottom: 1px solid rgba(212, 175, 55, 0.2);
        }
        .navbar-brand { color: var(--primary-gold) !important; font-size: 1.5rem; font-weight: bold; }
        .nav-link { color: var(--text-light) !important; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { color: var(--primary-gold) !important; }
        
        .card { background: var(--card-bg); border: 1px solid rgba(212, 175, 55, 0.1); border-radius: 10px; color: var(--text-light); }
        .card-header { background: rgba(0,0,0,0.2); border-bottom: 1px solid rgba(212, 175, 55, 0.1); color: var(--primary-gold); }
        
        .btn-gold {
            background: linear-gradient(45deg, #b8860b, #d4af37); color: #000; font-weight: 600; border: none;
            padding: 8px 20px; border-radius: 5px; transition: 0.3s;
        }
        .btn-gold:hover { background: linear-gradient(45deg, #d4af37, #f3e5ab); transform: translateY(-2px); }
        
        .table { color: var(--text-light); }
        .table th { border-bottom: 1px solid rgba(212, 175, 55, 0.3); color: var(--primary-gold); }
        .table td { border-bottom: 1px solid rgba(255, 255, 255, 0.05); }

        /* Blocked dates styling in Flatpickr */
        .blocked-date-custom, 
        .blocked-date-custom:hover {
            color: #ff5252 !important;
            background: rgba(255, 82, 82, 0.12) !important;
            text-decoration: line-through !important;
            cursor: pointer;
        }
        .flatpickr-calendar {
            background: #1e1e1e !important;
            border: 1px solid rgba(212, 175, 55, 0.3) !important;
            box-shadow: 0 10px 25px rgba(0,0,0,0.6) !important;
        }
        .flatpickr-months .flatpickr-month {
            color: #white !important;
            fill: #white !important;
        }
        .flatpickr-day.selected, .flatpickr-day.selected:hover {
            background: var(--primary-gold) !important;
            color: #000 !important;
            border-color: var(--primary-gold) !important;
        }
        .flatpickr-day:hover {
            background: rgba(212, 175, 55, 0.15) !important;
            color: #fff !important;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand brand-font" href="dashboard.php">SWIZY <span class="fs-6 text-muted font-monospace">Customer</span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="fa fa-bars" style="color: var(--primary-gold);"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="book.php">Book Event</a></li>
                    <li class="nav-item"><a class="nav-link" href="bookings.php">My Bookings</a></li>
                    <li class="nav-item"><a class="nav-link" href="reports.php">Reports</a></li>
                    <li class="nav-item"><a class="nav-link" href="profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="../logout.php" onclick="return confirm('Are you sure you want to logout?');">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container py-4">
