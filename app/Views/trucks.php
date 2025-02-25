<?= $this->extend('templates/layout.php') ?>

<?= $this->section('content') ?>
<link href="<?= base_url('public/assets/css/style.css'); ?>" rel="stylesheet">
<title>Truck Record and Maintenance Management</title>
<h1>Truck Record and Maintenance Management</h1>

<div class="content">
    <div class="table-container">
        <h2>Truck List</h2>
        <table id="trucksTable" class="table">
            <thead>
                <tr>
                    <th>Truck ID</th>
                    <th>Plate Number</th>
                    <th>Name</th>
                    <th>Fuel Type</th>
                    <th>Registration Expiry</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
             <tbody>
                <?php foreach ($trucks as $row): ?>
                    <tr>
                        <td><?= esc($row['truckId']) ?></td>
                        <td><?= esc($row['plate_number']) ?></td>
                        <td><?= esc($row['name']) ?></td>
                        <td><?= esc($row['fuel_type']) ?></td>
                        <td><?= esc($row['registration_expiry']) ?></td>
                        <td><?= esc($row['type']) ?></td>
                        <td>                   
                            <!-- Button to show full truck details in offcanvas -->
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">
                                <a href="#" class="view-truck" onclick="getTruckdetails(<?= htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') ?>)">
                                    View
                                </a>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Offcanvas for Truck Details -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasRightLabel">Truck Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <table id="truck-info" class="table">
                <tr>
                    <th>Truck ID:</th>
                    <td id="truckIdPlaceholder"></td>
                </tr>
                <tr>
                    <th>Truck Model:</th>
                    <td id="truckModelPlaceholder"></td>
                </tr>
                <tr>
                    <th>Plate Number:</th>
                    <td id="plateNumberPlaceholder"></td>
                </tr>
                <tr>
                    <th>Engine Number:</th>
                    <td id="engineNumberPlaceholder"></td>
                </tr>
                <tr>
                    <th>Chassis Number:</th>
                    <td id="chassisNumberPlaceholder"></td>
                </tr>
                <tr>
                    <th>Color:</th>
                    <td id="colorPlaceholder"></td>
                </tr>
                <tr>
                    <th>Certificate of Registration:</th>
                    <td id="corPlaceholder"></td>
                </tr>
                <tr>
                    <th>Insurance Details:</th>
                    <td id="insurancePlaceholder"></td>
                </tr>
                <tr>
                    <th>License Plate Expiry:</th>
                    <td id="licenseExpiryPlaceholder"></td>
                </tr>
                <tr>
                    <th>Registration Expiry Date:</th>
                    <td id="registrationExpiryPlaceholder"></td>
                </tr>
                <tr>
                    <th>Truck Type:</th>
                    <td id="truckTypePlaceholder"></td>
                </tr>
                <tr>
                    <th>Fuel Type:</th>
                    <td id="fuelTypePlaceholder"></td>
                </tr>
                <tr>
                    <th>Truck Length:</th>
                    <td id="truckLengthPlaceholder"></td>
                </tr>
                <tr>
                    <th>Load Capacity:</th>
                    <td id="loadCapacityPlaceholder"></td>
                </tr>
                <tr>
                    <th>Maintenance Technician:</th>
                    <td id="maintenanceTechnicianPlaceholder"></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Key Indicators Container -->
    <div id="keyIndicatorsContainer" style="display:none;">
        <div id="keyIndicatorsIcons"></div>
    </div>
</div>

<!-- JavaScript Section -->
<script>
    // Pass trucks data from PHP to JavaScript
    let trucksData = <?= json_encode($trucks) ?>;
    // Function to hide the key indicators container
    function hideKeyIndicators() {
        document.getElementById("keyIndicatorsContainer").style.display = "none";
    }

    // Function to load truck details into the offcanvas
    function getTruckdetails(truckData) {
        // Populate offcanvas details using truckData
        document.getElementById("truckIdPlaceholder").innerText = truckData.truckId                         || 'N/A';
        document.getElementById("truckModelPlaceholder").innerText = truckData.tmodel                       || 'N/A';
        document.getElementById("plateNumberPlaceholder").innerText = truckData.plate_number                || 'N/A';
        document.getElementById("engineNumberPlaceholder").innerText = truckData.enginenumber               || 'N/A';
        document.getElementById("chassisNumberPlaceholder").innerText = truckData.chassis_number            || 'N/A';
        document.getElementById("colorPlaceholder").innerText = truckData.color                             || 'N/A';
        document.getElementById("corPlaceholder").innerText = truckData.cor                                 || 'N/A';
        document.getElementById("insurancePlaceholder").innerText = truckData.insurance                     || 'N/A';
        document.getElementById("licenseExpiryPlaceholder").innerText = truckData.license_expiry            || 'N/A';
        document.getElementById("registrationExpiryPlaceholder").innerText = truckData.registration_expiry  || 'N/A';
        document.getElementById("truckTypePlaceholder").innerText = truckData.type                          || 'N/A';
        document.getElementById("fuelTypePlaceholder").innerText = truckData.fuel_type                      || 'N/A';
        document.getElementById("truckLengthPlaceholder").innerText = truckData.length                      || 'N/A';
        document.getElementById("loadCapacityPlaceholder").innerText = truckData.capacity                   || 'N/A';
        document.getElementById("maintenanceTechnicianPlaceholder").innerText = truckData.technician        || 'N/A';
        console.log(truckData);
    }
</script>
<?= $this->endSection() ?>
