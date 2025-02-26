<?= $this->extend('templates/layout.php') ?>

<?= $this->section('content') ?>
<link href="<?= base_url('public/assets/css/style.css'); ?>" rel="stylesheet">
<title>Truck Record and Maintenance Management</title>
<h1>Truck Record and Maintenance Management</h1>

<div class="content">
    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link active" id="truck-records-tab" data-bs-toggle="tab" href="#truck-records">Truck Records</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="geolocation-tab" data-bs-toggle="tab" href="#geolocation">Geolocation</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="maintenance-tab" data-bs-toggle="tab" href="#maintenance">Maintenance</a>
        </li>
    </ul>

    <!-- Tabs Content -->
    <div class="tab-content mt-3">
        <!-- Truck Records Tab -->
        <div class="tab-pane fade show active" id="truck-records">
            <div class="table-container">
                <h2>Truck List</h2>
                <table id="trucksTable" class="table table-bordered align-middle text-center">
                    <thead class="table-light text-dark">
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
                                    <!-- Button to show full truck details in modal -->
                                    <button type="button" class="btn btn-secondary btn-warning btn-sm fw-bold px-4 view-truck text-dark" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#truckDetailsModal" 
                                    onclick='getTruckdetails(<?= json_encode($row) ?>)'>
                                        View
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Geolocation Tab -->
        <div class="tab-pane fade" id="geolocation">
            <h2>Geolocation Tracking</h2>
            <p>Feature under development.</p>
        </div>

        <!-- Maintenance Tab (Initially Empty) -->
        <div class="tab-pane fade" id="maintenance">
            <div id="maintenance-content"></div> <!-- Keep this empty initially -->
        </div>
    </div>
</div>

<!-- jQuery Script to Load maintenance.php -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $("#maintenance-tab").on("shown.bs.tab", function () {
            // Check if content is already loaded
            if ($("#maintenance-content").is(":empty")) {
                $("#maintenance-content").html("<p>Loading maintenance data...</p>"); // Show a loading message
                $.ajax({
                    url: "maintenance.php",
                    type: "GET",
                    success: function(response) {
                        $("#maintenance-content").html(response);
                    },
                    error: function() {
                        $("#maintenance-content").html("<p class='text-danger'>Failed to load maintenance data.</p>");
                    }
                });
            }
        });
    });
</script>

  <!-- Modal for Truck Details -->
  <div class="modal fade" id="truckDetailsModal" tabindex="-1" aria-labelledby="truckDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="truckDetailsModalLabel">Truck Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <tbody>
                            <tr><th class="fw-bold">Truck ID:</th><td id="truckIdPlaceholder"></td></tr>
                            <tr><th class="fw-bold">Truck Model:</th><td id="truckModelPlaceholder"></td></tr>
                            <tr><th class="fw-bold">Plate Number:</th><td id="plateNumberPlaceholder"></td></tr>
                            <tr><th class="fw-bold">Engine Number:</th><td id="engineNumberPlaceholder"></td></tr>
                            <tr><th class="fw-bold">Chassis Number:</th><td id="chassisNumberPlaceholder"></td></tr>
                            <tr><th class="fw-bold">Color:</th><td id="colorPlaceholder"></td></tr>
                            <tr><th class="fw-bold">Certificate of Registration:</th><td id="corPlaceholder"></td></tr>
                            <tr><th class="fw-bold">Insurance Details:</th><td id="insurancePlaceholder"></td></tr>
                            <tr><th class="fw-bold">License Plate Expiry:</th><td id="licenseExpiryPlaceholder"></td></tr>
                            <tr><th class="fw-bold">Registration Expiry Date:</th><td id="registrationExpiryPlaceholder"></td></tr>
                            <tr><th class="fw-bold">Truck Type:</th><td id="truckTypePlaceholder"></td></tr>
                            <tr><th class="fw-bold">Fuel Type:</th><td id="fuelTypePlaceholder"></td></tr>
                            <tr><th class="fw-bold">Truck Length:</th><td id="truckLengthPlaceholder"></td></tr>
                            <tr><th class="fw-bold">Load Capacity:</th><td id="loadCapacityPlaceholder"></td></tr>
                            <tr><th class="fw-bold">Maintenance Technician:</th><td id="maintenanceTechnicianPlaceholder"></td></tr>
                        </tbody>
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

    // Function to load truck details into the modal
    
    function getTruckdetails(truckData) {
        document.getElementById("truckIdPlaceholder").innerText = truckData.truckId || 'N/A';
        document.getElementById("truckModelPlaceholder").innerText = truckData.tmodel || 'N/A';
        document.getElementById("plateNumberPlaceholder").innerText = truckData.plate_number || 'N/A';
        document.getElementById("engineNumberPlaceholder").innerText = truckData.enginenumber || 'N/A';
        document.getElementById("chassisNumberPlaceholder").innerText = truckData.chassis_number || 'N/A';
        document.getElementById("colorPlaceholder").innerText = truckData.color || 'N/A';
        document.getElementById("corPlaceholder").innerText = truckData.cor || 'N/A';
        document.getElementById("insurancePlaceholder").innerText = truckData.insurance || 'N/A';
        document.getElementById("licenseExpiryPlaceholder").innerText = truckData.license_expiry || 'N/A';
        document.getElementById("registrationExpiryPlaceholder").innerText = truckData.registration_expiry || 'N/A';
        document.getElementById("truckTypePlaceholder").innerText = truckData.type || 'N/A';
        document.getElementById("fuelTypePlaceholder").innerText = truckData.fuel_type || 'N/A';
        document.getElementById("truckLengthPlaceholder").innerText = truckData.length || 'N/A';
        document.getElementById("loadCapacityPlaceholder").innerText = truckData.capacity || 'N/A';
        document.getElementById("maintenanceTechnicianPlaceholder").innerText = truckData.technician || 'N/A';
        console.log(truckData);
    }
</script>
<?= $this->endSection() ?>
