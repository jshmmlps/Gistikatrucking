<?= $this->extend('templates/layout.php') ?>

<?= $this->section('content') ?>
<link href="<?= base_url('public/assets/css/style.css'); ?>" rel="stylesheet">

<div class="container">
    <h1 class="title">Truck Record and Maintenance Management</h1>

    <div class="content-container">
        <!-- Truck Table -->
        <div class="table-container">
            <h2>Truck List</h2>
            <table class="truck-table">
                <thead>
                    <tr>
                        <th>License Plate</th>
                        <th>Truck Name</th>
                        <th>Fuel Type</th>
                        <th>Registration Expiry</th>
                        <th>Truck Type</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trucks as $row): ?>
                        <tr>
                            <td><?= esc($row['plate_number']) ?></td>
                            <td><?= esc($row['name']) ?></td>
                            <td><?= esc($row['fuel_type']) ?></td>
                            <td><?= esc($row['registration_expiry']) ?></td>
                            <td><?= esc($row['type']) ?></td>
                            <td>
                                <a href="#" class="view-button" onclick="showTruckDetails(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>)">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Truck Details Panel -->
        <div class="details-panel">
            <h3 class="details-title">Truck Details</h3>
            <table class="details-table">
                <tr><th>Truck Model:</th> <td id="detail-name">Select a truck</td></tr>
                <tr><th>Plate Number:</th> <td id="detail-plate"></td></tr>
                <tr><th>Engine Number:</th> <td id="detail-engine"></td></tr>
                <tr><th>Chassis Number:</th> <td id="detail-chassis"></td></tr>
                <tr><th>Color:</th> <td id="detail-color"></td></tr>
                <tr><th>Certificate of Registration:</th> <td id="detail-cert"></td></tr>
                <tr><th>Insurance Details:</th> <td id="detail-insurance"></td></tr>
                <tr><th>License Plate Expiry:</th> <td id="detail-license-expiry"></td></tr>
                <tr><th>Registration Expiry Date:</th> <td id="detail-registration-expiry"></td></tr>
                <tr><th>Truck Type:</th> <td id="detail-type"></td></tr>
                <tr><th>Fuel Type:</th> <td id="detail-fuel"></td></tr>
                <tr><th>Truck Length:</th> <td id="detail-length"></td></tr>
                <tr><th>Load Capacity:</th> <td id="detail-load"></td></tr>
                <tr><th>Maintenance Technician:</th> <td id="detail-technician"></td></tr>
            </table>
        </div>
    </div>
</div>

<!-- Styling -->
<style>
.container { text-align: center; padding: 20px; }
.title { font-size: 24px; margin-bottom: 15px; }
.content-container { display: flex; justify-content: space-between; gap: 20px; }
.table-container { width: 60%; }
.details-panel { width: 35%; background: #f5f5f5; padding: 20px; border-radius: 10px; text-align: left; }
.truck-table { width: 100%; border-collapse: collapse; }
.truck-table th, .truck-table td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
.details-table { width: 100%; }
.details-table th, .details-table td { padding: 8px; border-bottom: 1px solid #ddd; text-align: left; }
.view-button { color: blue; text-decoration: none; cursor: pointer; }
.details-title { font-size: 18px; font-weight: bold; margin-bottom: 15px; }
</style>

<?= $this->endSection() ?>
