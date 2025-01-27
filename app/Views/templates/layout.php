<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">

</head>
<body>
    <header class="header">
        <img src="public/images/logo.png" alt="Juby-An Trucking Services Logo">
    </header>
    <div class="container">
        <!-- Main Content -->
        <div class="sidebar">
            <div class="profile">
                <img src="public/images/strategychae.jpeg" alt="Profile Picture">
                <h4>Jamaeca Quizon</h4>
                <p>Admin</p>
            </div>
            <nav>
                <a href="<?= base_url('dashboard') ?>" class="active"><img src="public/assets/icons/sidebar/dashboard.png" alt=""> Dashboard</a>
                <a href="<?= base_url('user') ?>" ><img src="public/assets/icons/sidebar/user.png" alt=""> User Account</a>
                <a href="<?= base_url('clients') ?>"><img src="public/assets/icons/sidebar/client.png" alt=""> Client Management</a>
                <a href="<?= base_url('driver') ?>"><img src="public/assets/icons/sidebar/driver.png" alt=""> Driver and Conductor Management</a>
                <a href="<?= base_url('') ?>"><img src="public/assets/icons/sidebar/booking.png" alt=""> Booking Management</a>
                <a href="<?= base_url('') ?>"><img src="public/assets/icons/sidebar/truck.png" alt=""> Truck Record and Monitoring Maintenance</a>
                <a href="<?= base_url('') ?>"><img src="public/assets/icons/sidebar/report.png" alt=""> Report Management</a>
            </nav>
            <a href="#" class="logout"><img src="public/assets/icons/logout.png" alt="">Logout</a>
        </div>

        <div class="main-content" style="margin-left: 10px; padding: 20px;"> 
            <?= $this->renderSection('content') ?>
        </div>
    </div>
</body>
</html>