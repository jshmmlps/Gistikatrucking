<?= $this->extend('templates/operations_coordinator_layout.php') ?>

<?= $this->section('content') ?>
<link href="<?= base_url('public/assets/css/style.css'); ?>" rel="stylesheet">
<title>Trucks Record Management</title>

<div class="container-fluid mt-4">
    <h1>Trucks Record Management</h1>
    
    <!-- Tab Navigation -->
    <ul class="nav nav-tabs" id="managementTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link active" id="truck-records-tab" data-bs-toggle="tab" href="#truck-records" role="tab" aria-controls="truck-records" aria-selected="true">Truck Records</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="geolocation-tab" data-bs-toggle="tab" href="#geolocation" role="tab" aria-controls="geolocation" aria-selected="false">Geolocation</a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link" id="maintenance-tab" data-bs-toggle="tab" href="#maintenance" role="tab" aria-controls="maintenance" aria-selected="false">Maintenance</a>
        </li>
    </ul>
    
    <!-- Tab Content -->
    <div class="tab-content mt-3" id="managementTabsContent">
        <!-- Truck Records Tab -->
        <div class="tab-pane fade show active" id="truck-records" role="tabpanel" aria-labelledby="truck-records-tab">
            <!-- Flash Messages -->
            <?php if(session()->has('success')): ?>
                <div class="alert alert-success">
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>
            <?php if(session()->has('error')): ?>
                <div class="alert alert-danger">
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <!-- Search Bar -->
            <div class="mb-3">
                <input type="text" class="form-control" id="searchTruck" placeholder="Search by Truck Model">
            </div>

            <!-- Create Truck Button -->
            <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createTruckModal">
                Create Truck
            </button>

            <!-- Trucks Table -->
            <table class="table table-bordered table-striped" id="trucksTable">
                <thead>
                    <tr>
                        <th>Plate Number</th>
                        <th>Truck Model</th>
                        <th>Fuel Type</th>
                        <th>COR Number</th>
                        <th>Registration Expiry</th>
                        <th>Truck Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($trucks)): ?>
                        <?php foreach($trucks as $key => $truck): ?>
                            <tr>
                                <td><?= esc($truck['plate_number']) ?></td>
                                <td><?= esc($truck['truck_model']) ?></td>
                                <td><?= esc($truck['fuel_type']) ?></td>
                                <td><?= esc($truck['cor_number']) ?></td>
                                <td><?= esc($truck['registration_expiry']) ?></td>
                                <td><?= esc($truck['truck_type']) ?></td>
                                <td>
                                    <!-- View Button -->
                                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewTruckModal<?= $key ?>">
                                        View
                                    </button>
                                    <!-- Edit Button -->
                                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editTruckModal<?= $key ?>">
                                        Edit
                                    </button>
                                    <!-- Delete Button -->
                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteTruckModal<?= $key ?>">
                                        Delete
                                    </button>
                                </td>
                            </tr>

                            <!-- View Truck Modal -->
                            <div class="modal fade" id="viewTruckModal<?= $key ?>" tabindex="-1" aria-labelledby="viewTruckModalLabel<?= $key ?>" aria-hidden="true">
                              <div class="modal-dialog">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="viewTruckModalLabel<?= $key ?>">Truck Details (<?= esc($truck['truck_model']) ?>)</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body">
                                    <p><strong>Truck ID:</strong> <?= esc($truck['truck_id']) ?></p>
                                    <p><strong>Truck Model:</strong> <?= esc($truck['truck_model']) ?></p>
                                    <p><strong>Plate Number:</strong> <?= esc($truck['plate_number']) ?></p>
                                    <p><strong>Engine Number:</strong> <?= esc($truck['engine_number']) ?></p>
                                    <p><strong>Chassis Number:</strong> <?= esc($truck['chassis_number']) ?></p>
                                    <p><strong>Color:</strong> <?= esc($truck['color']) ?></p>
                                    <p><strong>COR Number:</strong> <?= esc($truck['cor_number']) ?></p>
                                    <p><strong>Insurance Details:</strong> <?= esc($truck['insurance_details']) ?></p>
                                    <p><strong>License Plate Expiry:</strong> <?= esc($truck['license_plate_expiry']) ?></p>
                                    <p><strong>Registration Expiry:</strong> <?= esc($truck['registration_expiry']) ?></p>
                                    <p><strong>Truck Type:</strong> <?= esc($truck['truck_type']) ?></p>
                                    <p><strong>Fuel Type:</strong> <?= esc($truck['fuel_type']) ?></p>
                                    <p><strong>Truck Length:</strong> <?= esc($truck['truck_length']) ?></p>
                                    <p><strong>Load Capacity:</strong> <?= esc($truck['load_capacity']) ?></p>
                                    <p><strong>Maintenance Technician:</strong> <?= esc($truck['maintenance_technician']) ?></p>
                                  </div>
                                  <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                  </div>
                                </div>
                              </div>
                            </div>

                            <!-- Edit Truck Modal -->
                            <div class="modal fade" id="editTruckModal<?= $key ?>" tabindex="-1" aria-labelledby="editTruckModalLabel<?= $key ?>" aria-hidden="true">
                              <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                  <form action="<?= base_url('admin/trucks/update/' . $truck['truck_id']) ?>" method="post">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="editTruckModalLabel<?= $key ?>">Edit Truck (<?= esc($truck['truck_model']) ?>)</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body">
                                    <!-- All form fields matching the create form -->
                                    <div class="mb-3">
                                        <label for="truck_model_<?= $key ?>" class="form-label">Truck Model</label>
                                        <input type="text" class="form-control" name="truck_model" id="truck_model_<?= $key ?>" value="<?= esc($truck['truck_model']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="plate_number_<?= $key ?>" class="form-label">Plate Number</label>
                                        <input type="text" class="form-control" name="plate_number" id="plate_number_<?= $key ?>" value="<?= esc($truck['plate_number']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="engine_number_<?= $key ?>" class="form-label">Engine Number</label>
                                        <input type="text" class="form-control" name="engine_number" id="engine_number_<?= $key ?>" value="<?= esc($truck['engine_number']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="chassis_number_<?= $key ?>" class="form-label">Chassis Number</label>
                                        <input type="text" class="form-control" name="chassis_number" id="chassis_number_<?= $key ?>" value="<?= esc($truck['chassis_number']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="color_<?= $key ?>" class="form-label">Color</label>
                                        <input type="text" class="form-control" name="color" id="color_<?= $key ?>" value="<?= esc($truck['color']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="cor_number_<?= $key ?>" class="form-label">COR Number</label>
                                        <input type="text" class="form-control" name="cor_number" id="cor_number_<?= $key ?>" value="<?= esc($truck['cor_number']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="insurance_details_<?= $key ?>" class="form-label">Insurance Details</label>
                                        <input type="text" class="form-control" name="insurance_details" id="insurance_details_<?= $key ?>" value="<?= esc($truck['insurance_details']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="license_plate_expiry_<?= $key ?>" class="form-label">License Plate Expiry</label>
                                        <input type="date" class="form-control" name="license_plate_expiry" id="license_plate_expiry_<?= $key ?>" value="<?= esc($truck['license_plate_expiry']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="registration_expiry_<?= $key ?>" class="form-label">Registration Expiry</label>
                                        <input type="date" class="form-control" name="registration_expiry" id="registration_expiry_<?= $key ?>" value="<?= esc($truck['registration_expiry']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="truck_type_<?= $key ?>" class="form-label">Truck Type/Configuration</label>
                                        <input type="text" class="form-control" name="truck_type" id="truck_type_<?= $key ?>" value="<?= esc($truck['truck_type']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="fuel_type_<?= $key ?>" class="form-label">Fuel Type</label>
                                        <input type="text" class="form-control" name="fuel_type" id="fuel_type_<?= $key ?>" value="<?= esc($truck['fuel_type']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="truck_length_<?= $key ?>" class="form-label">Truck Length</label>
                                        <input type="text" class="form-control" name="truck_length" id="truck_length_<?= $key ?>" value="<?= esc($truck['truck_length']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="load_capacity_<?= $key ?>" class="form-label">Load Capacity</label>
                                        <input type="text" class="form-control" name="load_capacity" id="load_capacity_<?= $key ?>" value="<?= esc($truck['load_capacity']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="maintenance_technician_<?= $key ?>" class="form-label">Maintenance Technician</label>
                                        <input type="text" class="form-control" name="maintenance_technician" id="maintenance_technician_<?= $key ?>" value="<?= esc($truck['maintenance_technician']) ?>">
                                    </div>
                                  </div>
                                  <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                  </div>
                                  </form>
                                </div>
                              </div>
                            </div>

                            <!-- Delete Truck Modal -->
                            <div class="modal fade" id="deleteTruckModal<?= $key ?>" tabindex="-1" aria-labelledby="deleteTruckModalLabel<?= $key ?>" aria-hidden="true">
                              <div class="modal-dialog">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="deleteTruckModalLabel<?= $key ?>">Delete Truck (<?= esc($truck['truck_model']) ?>)</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body">
                                    Are you sure you want to delete this truck?
                                  </div>
                                  <div class="modal-footer">
                                    <a href="<?= base_url('admin/trucks/delete/' . $truck['truck_id']) ?>" class="btn btn-danger">Yes</a>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                  </div>
                                </div>
                              </div>
                            </div>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No trucks found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Geolocation Tab -->
        <div class="tab-pane fade" id="geolocation" role="tabpanel" aria-labelledby="geolocation-tab">
            <h3>Geolocation Data</h3>
            <p>Display geolocation details here.</p>
        </div>

        <!-- Maintenance Tab -->
        <div class="tab-pane fade" id="maintenance" role="tabpanel" aria-labelledby="maintenance-tab">
            <h3>Maintenance Records</h3>
            <p>Display maintenance details here.</p>
        </div>
    </div>
</div>

<!-- Create Truck Modal -->
<div class="modal fade" id="createTruckModal" tabindex="-1" aria-labelledby="createTruckModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="<?= base_url('admin/trucks/create') ?>" method="post">
      <div class="modal-header">
        <h5 class="modal-title" id="createTruckModalLabel">Create New Truck</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Form fields for creating a new truck -->
        <div class="mb-3">
            <label for="truck_model" class="form-label">Truck Model</label>
            <input type="text" class="form-control" name="truck_model" id="truck_model" required>
        </div>
        <div class="mb-3">
            <label for="plate_number" class="form-label">Plate Number</label>
            <input type="text" class="form-control" name="plate_number" id="plate_number" required>
        </div>
        <div class="mb-3">
            <label for="engine_number" class="form-label">Engine Number</label>
            <input type="text" class="form-control" name="engine_number" id="engine_number">
        </div>
        <div class="mb-3">
            <label for="chassis_number" class="form-label">Chassis Number</label>
            <input type="text" class="form-control" name="chassis_number" id="chassis_number">
        </div>
        <div class="mb-3">
            <label for="color" class="form-label">Color</label>
            <input type="text" class="form-control" name="color" id="color">
        </div>
        <div class="mb-3">
            <label for="cor_number" class="form-label">COR Number</label>
            <input type="text" class="form-control" name="cor_number" id="cor_number">
        </div>
        <div class="mb-3">
            <label for="insurance_details" class="form-label">Insurance Details</label>
            <input type="text" class="form-control" name="insurance_details" id="insurance_details">
        </div>
        <div class="mb-3">
            <label for="license_plate_expiry" class="form-label">License Plate Expiry</label>
            <input type="date" class="form-control" name="license_plate_expiry" id="license_plate_expiry">
        </div>
        <div class="mb-3">
            <label for="registration_expiry" class="form-label">Registration Expiry</label>
            <input type="date" class="form-control" name="registration_expiry" id="registration_expiry">
        </div>
        <div class="mb-3">
            <label for="truck_type" class="form-label">Truck Type/Configuration</label>
            <input type="text" class="form-control" name="truck_type" id="truck_type">
        </div>
        <div class="mb-3">
            <label for="fuel_type" class="form-label">Fuel Type</label>
            <input type="text" class="form-control" name="fuel_type" id="fuel_type">
        </div>
        <div class="mb-3">
            <label for="truck_length" class="form-label">Truck Length</label>
            <input type="text" class="form-control" name="truck_length" id="truck_length">
        </div>
        <div class="mb-3">
            <label for="load_capacity" class="form-label">Load Capacity</label>
            <input type="text" class="form-control" name="load_capacity" id="load_capacity">
        </div>
        <div class="mb-3">
            <label for="maintenance_technician" class="form-label">Maintenance Technician</label>
            <input type="text" class="form-control" name="maintenance_technician" id="maintenance_technician">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Create Truck</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
      </form>
    </div>
  </div>
</div>

<script>
    // Filter table rows dynamically based on search input
    document.getElementById('searchTruck').addEventListener('keyup', function() {
        var searchValue = this.value.toLowerCase();
        var rows = document.querySelectorAll('#trucksTable tbody tr');
        rows.forEach(function(row) {
            var truckModel = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            row.style.display = truckModel.indexOf(searchValue) > -1 ? '' : 'none';
        });
    });
</script>

<script src="<?= base_url('assets/js/script.js') ?>"></script>
<?= $this->endSection() ?>

