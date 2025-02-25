<?= $this->extend('templates/layout.php') ?>

<?= $this->section('content') ?>
<link href="<?= base_url('public/assets/css/style.css'); ?>" rel="stylesheet">
<title>Truck Record and Maintenance Management</title>
<h1>Truck Record and Maintenance Management</h1>

<div class="content">
    <div class="table-container">
        <h2>Truck List</h2>
        <table class="table table-striped" style="width:100%">
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
                        <?php if($trucks && is_array($trucks)): ?>
                        <?php foreach($trucks as $key => $truck): ?>
                            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvas<?= esc($key) ?>" aria-labelledby="offcanvasLabel<?= esc($key) ?>">
                                <div class="offcanvas-header">
                                    <h5 id="offcanvasLabel<?= esc($key) ?>">Truck Details: <?= esc($truck['Truck_name'] ?? 'Truck Details') ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                </div>
                                <div class="offcanvas-body">
                                    <!-- Truck Details Panel -->
                                    <table id="truck-info">
                                        <tr><th>Truck Model:</th><td><?= esc($Trucking['tmodel']) ?></td>
                                        <tr><th>Plate Number:</th><td><?= esc($Trucking['plate_number']) ?></td>
                                        <tr><th>Engine Number:</th> 
                                        <tr><th>Chassis Number:</th> 
                                        <tr><th>Color:</th> 
                                        <tr><th>Certificate of Registration:</th>
                                        <tr><th>Insurance Details:</th> 
                                        <tr><th>License Plate Expiry:</th> 
                                        <tr><th>Registration Expiry Date:</th> 
                                        <tr><th>Truck Type:</th> 
                                        <tr><th>Fuel Type:</th> 
                                        <tr><th>Truck Length:</th> 
                                        <tr><th>Load Capacity:</th> 
                                        <tr><th>Maintenance Technician:</th><td><?= esc($Trucking['technician']) ?></td>
                                        <table border="1">
                                    </table>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>