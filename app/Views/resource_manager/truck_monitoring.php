<?= $this->extend('templates/resource_manager_layout') ?>

<?= $this->section('content') ?>
<link href=<?=base_url('public/assets/css/style.css');?> rel="stylesheet">
<body>
<title>Resource Manager Truck Monitoring</title>
<h1>Resource Manager Truck Monitoring</h1>

    <h2>View Truck Monitoring</h2>
    <div class="d-flex justify-content-between align-items-center">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link active" href="#" id="truckRecordsTab">Truck Records</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" id="geolocationTab">Geolocation</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" id="maintenanceTab">Maintenance</a>
            </li>
        </ul>

        <div>
            <button class="btn btn-outline-secondary btn-sm" id="toggleView">View as Table</button>
            <button class="btn btn-primary btn-sm" id="addTruckBtn" data-bs-toggle="modal" data-bs-target="#addTruckModal">Add New Truck</button>
            <button class="btn btn-primary btn-sm" id="addMaintenanceBtn" data-bs-toggle="modal" data-bs-target="#addMaintenanceModal">Add Maintenance</button>
        </div>
    </div>

    <!-- 🚚 Truck List -->
    <div id="truckRecords" class="mt-3">
        <input type="text" id="searchBar" class="form-control mb-3" placeholder="Search">
        
        <div id="truckList" class="row"></div>

        <div id="truckTableView" class="table-responsive" style="display: none;">
            <table class="table table-striped table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>License Plate</th>
                        <th>Truck Name</th>
                        <th>Fuel Type</th>
                        <th>Certificate of Registration</th>
                        <th>Registration Expiry</th>
                        <th>Truck Type</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="truckTableBody"></tbody>
            </table>
        </div>
    </div>

    <!-- 🏗 Truck Details Modal -->
    <div class="modal fade" id="truckModal" tabindex="-1" aria-labelledby="truckModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="truckModalLabel">Full Truck Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="truckForm">
                        <input type="hidden" id="modalTruckId">
                        <table class="table table-bordered">
                            <tbody>
                                <tr><th>Truck Model</th><td><input type="text" id="modalTruckName" class="form-control"></td></tr>
                                <tr><th>Plate Number</th><td><input type="text" id="modalTruckPlate" class="form-control"></td></tr>
                                <tr><th>Engine Number</th><td><input type="text" id="modalEngineNumber" class="form-control"></td></tr>
                                <tr><th>Chassis Number</th><td><input type="text" id="modalChassisNumber" class="form-control"></td></tr>
                                <tr><th>Color</th><td><input type="text" id="modalColor" class="form-control"></td></tr>
                                <tr><th>Certificate of Registration</th><td><input type="text" id="modalCR" class="form-control"></td></tr>
                            </tbody>
                        </table>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" id="editTruck" class="btn btn-warning">Edit</button>
                    <button type="button" id="saveTruck" class="btn btn-success" style="display: none;">Save</button>
                    <button type="button" id="deleteTruck" class="btn btn-danger">Delete</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 🚛 Add Truck Modal -->
    <div class="modal fade" id="addTruckModal" tabindex="-1" aria-labelledby="addTruckModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTruckModalLabel">Add New Truck</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addTruckForm">
                        <table class="table table-bordered">
                            <tbody>
                                <tr><th>Truck Model</th><td><input type="text" class="form-control" id="truckName" required></td></tr>
                                <tr><th>Plate Number</th><td><input type="text" class="form-control" id="plateNumber" required></td></tr>
                                <tr><th>Fuel Type</th><td>
                                    <select class="form-select" id="fuelType">
                                        <option value="Diesel">Diesel</option>
                                        <option value="Gasoline">Gasoline</option>
                                    </select>
                                </td></tr>
                            </tbody>
                        </table>
                        <div class="text-end">
                            <button type="button" class="btn btn-success" id="saveTruck">Save Truck</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 🛠 Add Maintenance Modal -->
    <div class="modal fade" id="addMaintenanceModal" tabindex="-1" aria-labelledby="addMaintenanceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addMaintenanceModalLabel">Add Maintenance Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="maintenanceForm">
                        <div class="mb-3">
                            <label for="licensePlate" class="form-label">License Plate</label>
                            <input type="text" class="form-control" id="licensePlate" required>
                        </div>
                        <div class="mb-3">
                            <label for="vehicleType" class="form-label">Vehicle Type</label>
                            <input type="text" class="form-control" id="vehicleType" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Maintenance</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= base_url('/public/assets/js/script.js') ?>"></script>
<?= $this->endSection() ?>
