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
                        <td><button type="button" class="btn btn-secondary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight"><a href="<?= base_url('truck/view/' . $truck['license_plate']) ?>" class="view-truck" onclick="view(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>)">View</a></button>
                            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
                                <div class="offcanvas-header">
                                    <h5 class="offcanvas-title" id="offcanvasRightLabel">Truck Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                </div>
                                <div class="offcanvas-body">
                                    <!-- Truck Details Panel -->
                                    <table border="1">
                                        <tr>
                                            <th>Truck Model</th>
                                            <td><?= esc($truckData['tmodel']) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Plate Number</th>
                                            <td><?= esc($truckData['plate_number']) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Engine Number</th>
                                            <td><?= esc($truckData['enginenumber']) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Chassis Number</th>
                                            <td><?= esc($truckData['chassis_number']) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Color</th>
                                            <td><?= esc($truckData['Truck_color'] ?? 'N/A') ?></td>
                                        </tr>
                                        <tr>
                                            <th>Certificate of Registration</th>
                                            <td><?= esc($truckData['cor']) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Insurance Details</th>
                                            <td><?= esc($truckData['insurance']) ?></td>
                                        </tr>
                                        <tr>
                                            <th>License Plate Expiry</th>
                                            <td><?= esc($truckData['license_expiry']) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Registration Expiry</th>
                                            <td><?= esc($truckData['registration_expiry']) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Truck Type</th>
                                            <td><?= esc($truckData['type']) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Fuel Type</th>
                                            <td><?= esc($truckData['fuel_type']) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Truck Length</th>
                                            <td><?= esc($truckData['length']) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Load Capacity</th>
                                            <td><?= esc($truckData['capacity']) ?></td>
                                        </tr>
                                        <tr>
                                            <th>Maintenance Technician</th>
                                            <td><?= esc($truckData['technician']) ?></td>
                                        </tr>
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

<!-- Styling -->
<!--
<style>
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
                -->
<?= $this->endSection() ?>