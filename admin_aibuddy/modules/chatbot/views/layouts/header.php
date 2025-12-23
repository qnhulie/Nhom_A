<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Buddy - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #124559; color: white; }
        .sidebar a { color: #aec3b0; text-decoration: none; padding: 10px 15px; display: block; }
        .sidebar a:hover, .sidebar a.active { background-color: #01161e; color: white; }
        .sidebar i { width: 25px; }
        .card-stat { border: none; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .card-stat h3 { font-size: 2.5rem; font-weight: bold; color: #124559; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse px-0">
            <div class="position-sticky pt-3">
                <h4 class="text-center mb-4">🤖 AI Buddy Admin</h4>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $path; ?>dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $path; ?>personas/index.php">
                            <i class="fas fa-users-cog"></i> Manage Personas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $path; ?>topics/index.php">
                            <i class="fas fa-list-alt"></i> Manage Topics
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $path; ?>../../../AIBuddy_Homepage.html" target="_blank">
                            <i class="fas fa-external-link-alt"></i> View Website
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">