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
                    <th>Truck ID</th>
                    <th>License Plate</th>
                    <th>Truck Name</th>
                    <th>Fuel Type</th>
                    <th>Registration Expiry</th>
                    <th>Truck Type</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($trucks as $row):?>
                    <tr>
                        <? echo esc($row['tmodel'])?>
                        <td><?= esc($row['truckId'])?></td>
                        <td><?= esc($row['plate_number']) ?></td>
                        <td><?= esc($row['name']) ?></td>
                        <td><?= esc($row['fuel_type']) ?></td>
                        <td><?= esc($row['registration_expiry']) ?></td>
                        <td><?= esc($row['type']) ?></td>
                        <td><button type="button" class="btn btn-secondary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight"><a href="#" class="view-truck" onclick="getTruckdetails(<?= $trucks('truckId') ?>)">View</a></button>
                            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
                                <div class="offcanvas-header">
                                    <h5 class="offcanvas-title" id="offcanvasRightLabel">Truck Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                                </div>
                                <div class="offcanvas-body">
                                    <!-- Truck Details Panel -->
                                    <table id="truck-info">
                                        <tr><th>Truck ID:</th>                      <td><?= esc($row['truckId'])?></td>
                                        <tr><th>Truck Model:</th>                   <td><?= esc($row['tmodel'])?></td>
                                        <tr><th>Plate Number:</th>                  <td><?= esc($row['plate_number']) ?></td>
                                        <tr><th>Engine Number:</th>                 <td><?= esc($row['enginenumber'])?></td> 
                                        <tr><th>Chassis Number:</th>                <td><?= esc($row['chassis_number'])?></td> 
                                        <tr><th>Color:</th>                         <td><?= esc($row['color'])?></td>
                                        <tr><th>Certificate of Registration:</th>   <td><?= esc($row['cor'])?></td>
                                        <tr><th>Insurance Details:</th>             <td><?= esc($row['insurance'])?></td>
                                        <tr><th>License Plate Expiry:</th>          <td><?= esc($row['license_expiry'])?></td>
                                        <tr><th>Registration Expiry Date:</th>      <td><?= esc($row['registration_expiry'])?></td>
                                        <tr><th>Truck Type:</th>                    <td><?= esc($row['type'])?></td>
                                        <tr><th>Fuel Type:</th>                     <td><?= esc($row['fuel_type'])?></td>
                                        <tr><th>Truck Length:</th>                  <td><?= esc($row['length'])?></td>
                                        <tr><th>Load Capacity:</th>                 <td><?= esc($row['capacity'])?></td>
                                        <tr><th>Maintenance Technician:</th>        <td><?= esc($row['technician']) ?></td>
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