<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="<?= base_url('/assets/css/style.css') ?>">

</head>
<body>
    <div>
        <header class="header">
            <img src="./public/images/icons/logo.png" alt="Juby-An Trucking Services Logo">
        </header>
    </div>
    
    <div class="container">
        <!-- Main Content -->
        <div class="sidebar">
            <div class="profile">
                <img src="public/images/luffy.jpg" alt="Profile Picture">
                <h4>Jamaeca Quizon</h4>
                <p>Admin</p>
            </div>
            <nav>
    <a href="<?= base_url('dashboard') ?>" class="<?= (current_url() == base_url('dashboard')) ? 'active' : '' ?>">
        <img src="public/images/icons/sidebar/dashboard.png" alt=""> Dashboard
    </a>
    <a href="<?= base_url('user') ?>" class="<?= (current_url() == base_url('user')) ? 'active' : '' ?>">
        <img src="public/images/icons/sidebar/user.png" alt=""> User Account
    </a>
    <a href="<?= base_url('clients') ?>" class="<?= (current_url() == base_url('clients')) ? 'active' : '' ?>">
        <img src="public/images/icons/sidebar/client.png" alt=""> Client Management
    </a>
    <a href="<?= base_url('driver') ?>" class="<?= (current_url() == base_url('driver')) ? 'active' : '' ?>">
        <img src="public/images/icons/sidebar/driver.png" alt=""> Driver and Conductor Management
    </a>
    <a href="<?= base_url('booking') ?>" class="<?= (current_url() == base_url('booking')) ? 'active' : '' ?>">
        <img src="public/images/icons/sidebar/booking.png" alt=""> Booking Management
    </a>
    <a href="<?= base_url('truckrecord') ?>" class="<?= (current_url() == base_url('truckrecord')) ? 'active' : '' ?>">
        <img src="public/images/icons/sidebar/truck.png" alt=""> Truck Record and Monitoring Maintenance
    </a>
    <a href="<?= base_url('reports') ?>" class="<?= (current_url() == base_url('reports')) ? 'active' : '' ?>">
        <img src="public/images/icons/sidebar/report.png" alt=""> Report Management
    </a>
</nav>
            <a href="#" class="logout"><img src="public/images/icons/logout.png" alt="">Logout</a>
        </div>

        <div class="main-content"> 
            <?= $this->renderSection('content') ?>
        </div>
    </div>
</body>
</html>