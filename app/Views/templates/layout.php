<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= base_url('/assets/css/style.css') ?>">
    <link href="<?= base_url('/public/assets/css/bootstrap.min.css');?>" rel="stylesheet"> 
    <link href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.css" rel="stylesheet"> 

</head>
<body> 
    <!-- sidebar -->
     <div class="sidebar">
        <nav class="nav flex-column">
            <span >
                <img class="rounded mx-auto d-block img-header" src="./public/images/icons/logo.png" alt="Juby-An Trucking Services Logo">
            </span>
            <hr class="text-black">
            <a href="<?= base_url('dashboard') ?>" class="nav-link <?= (current_url() == base_url('dashboard')) ? 'active' : '' ?>">
                <span class="icon">
                    <img src="public/images/icons/sidebar/dashboard.png" alt="">
                </span>
                <span class="description">Dashboard</span>
            </a>
            <a href="<?= base_url('user') ?>" class="nav-link <?= (current_url() == base_url('user')) ? 'active' : '' ?>">
                <span class="icon">
                    <img src="public/images/icons/sidebar/user.png" alt="">
                </span>
                <span class="description">User Account</span>
            </a>
            <a href="<?= base_url('clients') ?>" class="nav-link <?= (current_url() == base_url('user')) ? 'active' : '' ?>">
                <span class="icon">
                    <img src="public/images/icons/sidebar/client.png" alt="">
                </span>
                <span class="description">Client Management</span>
            </a>
            <a href="<?= base_url('drivers') ?>" class="nav-link <?= (current_url() == base_url('user')) ? 'active' : '' ?>">
                <span class="icon">
                    <img src="public/images/icons/sidebar/driver.png" alt="">
                </span>
                <span class="description">Driver and Conductor Management</span>
            </a>
            <a href="<?= base_url('bookings') ?>" class="nav-link <?= (current_url() == base_url('user')) ? 'active' : '' ?>">
                <span class="icon">
                    <img src="public/images/icons/sidebar/booking.png" alt="">
                </span>
                <span class="description">Booking Management</span>
            </a>
            <a href="<?= base_url('trucks') ?>" class="nav-link <?= (current_url() == base_url('user')) ? 'active' : '' ?>">
                <span class="icon">
                    <img src="public/images/icons/sidebar/truck.png" alt="">
                </span>
                <span class="description">Truck Record and Monitoring Maintenance</span>
            </a>
            <a href="<?= base_url('maintenance') ?>" class="nav-link <?= (current_url() == base_url('user')) ? 'active' : '' ?>">
                <span class="icon">
                    <img src="public/images/icons/sidebar/report.png" alt="">
                </span>
                <span class="description">Report Management</span>
            </a>
            <a href="" class="logout">
                <span class="icon">
                    <img src="public/images/icons/logout.png" alt="">
                </span>
                <span class="description">Log Out</span>
            </a>
        </nav>
     </div>
     <!-- main content -->
    <main class="main-content"> 
        <?= $this->renderSection('content') ?>
    </main>



    <script src="<?php echo base_url('/public/assets/js/jquery-3.7.1.js'); ?>"></script> 
    <script src="<?php echo base_url('/public/assets/js/popper.min.js'); ?>"></script> 
    <script src="<?php echo base_url('/public/assets/js/bootstrap.bundle.min.js'); ?>"></script>
</body>
</html>