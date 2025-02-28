<?= $this->extend('templates/operations_coordinator_layout') ?>

<?= $this->section('content') ?>
<link href=<?=base_url('public/assets/css/style.css');?> rel="stylesheet">
<body>
<title>Operations Coordinator Truck Monitoring</title>
<h1>Operations Coordinator Truck Monitoring</h1>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link active" href="#" id="truckRecordsTab">Truck Records</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#" id="geolocationTab">Geolocation</a>
        </li>
    </ul>

    <!-- Top Controls -->
    <div class="d-flex justify-content-between align-items-center mt-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTruckModal">Add New Truck</button>
    </div>

    <!-- Truck Records Table -->
    <div class="table-responsive mt-3">
        <table class="table table-striped">
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
                <?php foreach ($trucks as $truck) : ?>
                    <tr>
                        <td><?= esc($truck['plate_number']) ?></td>
                        <td><?= esc($truck['name']) ?></td>
                        <td><?= esc($truck['fuel_type']) ?></td>
                        <td><?= esc($truck['registration_expiry']) ?></td>
                        <td><?= esc($truck['type']) ?></td>
                        <td>
                            <button class="btn btn-info btn-sm view-truck" 
                                data-bs-toggle="modal" 
                                data-bs-target="#viewTruckModal"
                                data-id="<?= esc($truck['id']) ?>"
                                data-name="<?= esc($truck['name']) ?>"
                                data-plate="<?= esc($truck['plate_number']) ?>"
                                data-engine="<?= esc($truck['engine_number']) ?>"
                                data-chassis="<?= esc($truck['chassis_number']) ?>"
                                data-color="<?= esc($truck['color']) ?>"
                                data-cr="<?= esc($truck['certificate_registration']) ?>"
                                data-insurance="<?= esc($truck['insurance_details']) ?>"
                                data-expiry="<?= esc($truck['registration_expiry']) ?>"
                                data-type="<?= esc($truck['type']) ?>"
                                data-fuel="<?= esc($truck['fuel_type']) ?>">
                                View
                            </button>
                            <button class="btn btn-warning btn-sm edit-truck" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editTruckModal"
                                data-id="<?= esc($truck['id']) ?>"
                                data-name="<?= esc($truck['name']) ?>"
                                data-plate="<?= esc($truck['plate_number']) ?>"
                                data-engine="<?= esc($truck['engine_number']) ?>"
                                data-chassis="<?= esc($truck['chassis_number']) ?>"
                                data-color="<?= esc($truck['color']) ?>"
                                data-cr="<?= esc($truck['certificate_registration']) ?>"
                                data-insurance="<?= esc($truck['insurance_details']) ?>"
                                data-expiry="<?= esc($truck['registration_expiry']) ?>"
                                data-type="<?= esc($truck['type']) ?>"
                                data-fuel="<?= esc($truck['fuel_type']) ?>">
                                Edit
                            </button>
                            <button class="btn btn-danger btn-sm delete-truck" 
                                data-bs-toggle="modal" 
                                data-bs-target="#deleteTruckModal"
                                data-id="<?= esc($truck['id']) ?>">
                                Delete
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- View Truck Modal -->
<div class="modal fade" id="viewTruckModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Truck Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Truck Model:</strong> <span id="viewTruckName"></span></p>
                <p><strong>Plate Number:</strong> <span id="viewTruckPlate"></span></p>
                <p><strong>Fuel Type:</strong> <span id="viewFuelType"></span></p>
                <p><strong>Registration Expiry:</strong> <span id="viewRegExpiry"></span></p>
            </div>
        </div>
    </div>
</div>

<!-- Edit Truck Modal -->
<div class="modal fade" id="editTruckModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Truck</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editTruckForm">
                    <input type="hidden" id="editTruckId">
                    <div class="mb-3">
                        <label for="editTruckName">Truck Name</label>
                        <input type="text" class="form-control" id="editTruckName" required>
                    </div>
                    <div class="mb-3">
                        <label for="editPlateNumber">Plate Number</label>
                        <input type="text" class="form-control" id="editPlateNumber" required>
                    </div>
                    <button type="submit" class="btn btn-success">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Truck Modal -->
<div class="modal fade" id="deleteTruckModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this truck?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".view-truck").forEach(button => {
        button.addEventListener("click", function () {
            document.getElementById("viewTruckName").innerText = this.dataset.name;
            document.getElementById("viewTruckPlate").innerText = this.dataset.plate;
            document.getElementById("viewFuelType").innerText = this.dataset.fuel;
            document.getElementById("viewRegExpiry").innerText = this.dataset.expiry;
        });
    });

    document.querySelectorAll(".edit-truck").forEach(button => {
        button.addEventListener("click", function () {
            document.getElementById("editTruckId").value = this.dataset.id;
            document.getElementById("editTruckName").value = this.dataset.name;
            document.getElementById("editPlateNumber").value = this.dataset.plate;
        });
    });
});
</script>
<?= $this->endSection() ?>
