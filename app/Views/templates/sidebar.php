<div class="sidebar" style="width: 250px; background-color: #003366; height: 100vh; color: white; position: fixed;">
    <div class="profile" style="text-align: center; padding: 20px;">
        <img src="<?= base_url('assets/images/defaultpfp.jpg') ?>" alt="Profile Picture" style="width: 80px; border-radius: 50%; margin-bottom: 10px;">
        <h4 style="margin: 0; font-size: 18px;">Jamaeca Quizon</h4>
        <p style="margin: 0; font-size: 14px; color: #ccc;">Admin</p>
    </div>
    <nav style="display: flex; flex-direction: column; margin-top: 20px;">
        <a href="<?= base_url('dashboard') ?>" class="active" style="padding: 10px 20px; color: white; text-decoration: none; display: flex; align-items: center;">
            <img src="<?= base_url('assets/icons/dashboard-icon.png') ?>" alt="" style="width: 20px; margin-right: 10px;"> DASHBOARD
        </a>
        <a href="<?= base_url('profile') ?>" style="padding: 10px 20px; color: white; text-decoration: none; display: flex; align-items: center;">
            <img src="<?= base_url('assets/icons/user-icon.png') ?>" alt="" style="width: 20px; margin-right: 10px;"> USER ACCOUNT
        </a>
        <a href="<?= base_url('clients') ?>" style="padding: 10px 20px; color: white; text-decoration: none; display: flex; align-items: center;">
            <img src="<?= base_url('assets/icons/client-icon.png') ?>" alt="" style="width: 20px; margin-right: 10px;"> CLIENT MANAGEMENT
        </a>
        <a href="#" style="padding: 10px 20px; color: white; text-decoration: none; display: flex; align-items: center;">
            <img src="<?= base_url('assets/icons/driver-icon.png') ?>" alt="" style="width: 20px; margin-right: 10px;"> DRIVER AND CONDUCTOR
        </a>
        <a href="#" style="padding: 10px 20px; color: white; text-decoration: none; display: flex; align-items: center;">
            <img src="<?= base_url('assets/icons/booking-icon.png') ?>" alt="" style="width: 20px; margin-right: 10px;"> BOOKING MANAGEMENT
        </a>
        <a href="#" style="padding: 10px 20px; color: white; text-decoration: none; display: flex; align-items: center;">
            <img src="<?= base_url('assets/icons/truck-icon.png') ?>" alt="" style="width: 20px; margin-right: 10px;"> TRUCK RECORD AND MAINTENANCE MANAGEMENT
        </a>
        <a href="#" style="padding: 10px 20px; color: white; text-decoration: none; display: flex; align-items: center;">
            <img src="<?= base_url('assets/icons/report-icon.png') ?>" alt="" style="width: 20px; margin-right: 10px;"> REPORT
        </a>
    </nav>
    <a href="#" class="logout" style="padding: 10px 20px; color: white; text-decoration: none; display: block; margin-top: auto; text-align: center; background-color: #FF0000;">Logout</a>
</div>


<style>
    .sidebar nav a {
        padding: 10px 20px;
        color: white;
        text-decoration: none;
        display: flex;
        align-items: center;
        transition: background-color 0.3s ease;
    }

    .sidebar nav a:hover {
        background-color: #005b99; /* Highlight color */
        color: white; /* Keep text white */
    }

    .sidebar .logout:hover {
        background-color: #cc0000; /* Slightly darker red on hover */
    }
</style>

