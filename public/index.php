<?php
/**
 * Fashion Management System
 * Main Entry Point
 */

// Start session
session_start();

// Include database configuration
require_once __DIR__ . '/../config/database.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fashion Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Welcome to Fashion Management System</h1>
        </header>
        
        <main>
            <div class="login-options">
                <div class="option-card">
                    <h2>Admin Login</h2>
                    <p>Access administrative features</p>
                    <a href="modules/admin/login.html" class="btn">Admin Login</a>
                </div>
                
                <div class="option-card">
                    <h2>Customer Portal</h2>
                    <p>Manage your account and orders</p>
                    <a href="modules/customer/login.html" class="btn">Customer Login</a>
                    <a href="modules/customer/register.html" class="btn-secondary">Register</a>
                </div>
                
                <div class="option-card">
                    <h2>Supplier Portal</h2>
                    <p>Supplier management and inventory</p>
                    <a href="modules/supplier/login.html" class="btn">Supplier Login</a>
                    <a href="modules/supplier/register.html" class="btn-secondary">Register</a>
                </div>
            </div>
            
            <div class="info-section">
                <a href="about.html">About Us</a>
            </div>
        </main>
        
        <footer>
            <p>&copy; 2026 Fashion Management System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
