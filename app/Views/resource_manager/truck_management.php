<?= $this->extend('templates/resource_manager_layout.php') ?>

<?= $this->section('content') ?>
<link href="<?= base_url('public/assets/css/style.css'); ?>" rel="stylesheet">
<title>Trucks Record Management</title>

<div class="container-fluid mt-4">
    <h1>Trucks Record Management</h1>
    
    <!-- Tab Navigation -->
    <ul class="nav nav-tabs" id="managementTabs" role="tablist">
        <a href="<?= base_url('resource/trucks'); ?>" class="nav-link <?= (current_url() == base_url('resource/trucks')) ? 'active' : '' ?>">
            <span class="description">Truck Records</span>
        </a>
        <a href="<?= base_url('resource/geolocation'); ?>" class="nav-link <?= (current_url() == base_url('resource/geolocation')) ? 'active' : '' ?>">
            <span class="description">Geolocation</span>
        </a>
        <a href="<?= base_url('resource/maintenance'); ?>" class="nav-link <?= (current_url() == base_url('resource/maintenance')) ? 'active' : '' ?>">
            <span class="description">Maintenance Analytics</span>
        </a>
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
                        <th>Truck ID</th>
                        <th>Plate Number</th>
                        <th>Truck Model</th>
                        <!-- <th>Last Inspection Date</th> -->
                        <!-- <th>Last Inspection Mileage</th> -->
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
                                <td><?= esc($truck['truck_id']) ?></td>
                                <td><?= esc($truck['plate_number']) ?></td>
                                <td><?= esc($truck['truck_model']) ?></td>
                                <!-- <td><?= esc($truck['last_inspection_date'] ?? 'N/A') ?></td> -->
                                <!-- <td><?= esc($truck['last_inspection_mileage'] ?? 'N/A') ?></td> -->
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
                              <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="viewTruckModalLabel<?= $key ?>">Truck Details (<?= esc($truck['truck_model']) ?>)</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body">
                                    <!-- Truck Info Section -->
                                    <div class="p-3 mb-4 rounded-3 shadow-sm border bg-light">
                                        <h6 class="fw-bold mb-3 text-primary">Truck Information</h6>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold text-secondary">Truck ID:</span>
                                            <span><?= esc($truck['truck_id']) ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold text-secondary">Truck Model:</span>
                                            <span><?= esc($truck['truck_model']) ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold text-secondary">Plate Number:</span>
                                            <span><?= esc($truck['plate_number']) ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold text-secondary">Engine Number:</span>
                                            <span><?= esc($truck['engine_number']) ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold text-secondary">Chassis Number:</span>
                                            <span><?= esc($truck['chassis_number']) ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold text-secondary">Color:</span>
                                            <span><?= esc($truck['color']) ?></span>
                                        </div>
                                        <!-- Added Current Mileage Row -->
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold text-secondary">Current Mileage:</span>
                                            <span><?= esc($truck['current_mileage'] ?? 'N/A') ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold text-secondary">Last Inspection Date:</span>
                                            <span><?= esc($truck['last_inspection_date'] ?? 'N/A') ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold text-secondary">Last Inspection Mileage:</span>
                                            <span><?= esc($truck['last_inspection_mileage'] ?? 'N/A') ?></span>
                                        </div>
                                    </div>

                                    <!-- Registration & Insurance Section -->
                                    <div class="p-3 mb-4 rounded-3 shadow-sm border bg-light">
                                        <h6 class="fw-bold mb-3 text-primary">Registration & Insurance</h6>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold text-secondary">COR Number:</span>
                                            <span><?= esc($truck['cor_number']) ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold text-secondary">Insurance Details:</span>
                                            <span><?= esc($truck['insurance_details']) ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold text-secondary">License Plate Expiry:</span>
                                            <span><?= esc($truck['license_plate_expiry']) ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="fw-bold text-secondary">Registration Expiry:</span>
                                            <span><?= esc($truck['registration_expiry']) ?></span>
                                        </div>
                                    </div>

                                    <!-- Truck Specifications Section -->
                                    <div class="p-3 mb-4 rounded-3 shadow-sm border bg-light">
                                        <h6 class="fw-bold mb-3 text-primary">Truck Specifications</h6>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold text-secondary">Truck Type:</span>
                                            <span><?= esc($truck['truck_type']) ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold text-secondary">Fuel Type:</span>
                                            <span><?= esc($truck['fuel_type']) ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold text-secondary">Truck Length:</span>
                                            <span><?= esc($truck['truck_length']) ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="fw-bold text-secondary">Load Capacity (kg):</span>
                                            <span><?= esc($truck['load_capacity']) ?></span>
                                        </div>
                                    </div>

                                    <!-- Maintenance Technician Section -->
                                    <div class="p-3 rounded-3 shadow-sm border bg-light">
                                        <h6 class="fw-bold mb-3 text-primary">Maintenance</h6>
                                        <div class="d-flex justify-content-between">
                                            <span class="fw-bold text-secondary">Maintenance Technician:</span>
                                            <span><?= esc($truck['maintenance_technician']) ?></span>
                                        </div>
                                    </div>

                                    <!-- New Section: Maintenance Items Details -->
                                    <?php if(isset($truck['maintenance_items'])): ?>
                                    <div class="p-3 rounded-3 shadow-sm border bg-light mt-4">
                                        <h6 class="fw-bold mb-3 text-primary">Maintenance Items</h6>
                                        <?php foreach($truck['maintenance_items'] as $itemName => $item): ?>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span class="fw-bold text-secondary"><?= ucwords(str_replace('_', ' ', $itemName)) ?>:</span>
                                                <span>
                                                    Last Service Mileage: <?= esc($item['last_service_mileage'] ?? 'N/A') ?>,
                                                    Last Service Date: <?= esc($item['last_service_date'] ?? 'N/A') ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>

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
                                  <form action="<?= base_url('resource/trucks/update/' . $truck['truck_id']) ?>" method="post">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="editTruckModalLabel<?= $key ?>">Edit Truck (<?= esc($truck['truck_model']) ?>)</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body">
                                    <!-- Common Truck Fields -->
                                    <div class="mb-3">
                                        <label for="truck_model_<?= $key ?>" class="form-label">Truck Model <span style="color:red;">*</span></label>
                                        <input type="text" class="form-control" name="truck_model" id="truck_model_<?= $key ?>" value="<?= esc($truck['truck_model']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="plate_number_<?= $key ?>" class="form-label">Plate Number <span style="color:red;">*</span></label>
                                        <input type="text" class="form-control" name="plate_number" id="plate_number_<?= $key ?>" value="<?= esc($truck['plate_number']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="engine_number_<?= $key ?>" class="form-label">Engine Number <span style="color:red;">*</span></label>
                                        <input type="text" class="form-control" name="engine_number" id="engine_number_<?= $key ?>" value="<?= esc($truck['engine_number']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="chassis_number_<?= $key ?>" class="form-label">Chassis Number <span style="color:red;">*</span></label>
                                        <input type="text" class="form-control" name="chassis_number" id="chassis_number_<?= $key ?>" value="<?= esc($truck['chassis_number']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="color_<?= $key ?>" class="form-label">Color <span style="color:red;">*</span></label>
                                        <input type="text" class="form-control" name="color" id="color_<?= $key ?>" value="<?= esc($truck['color']) ?>" required>
                                    </div>
                                    <!-- Added Current Mileage Field -->
                                    <div class="mb-3">
                                        <label for="current_mileage_<?= $key ?>" class="form-label">Current Mileage <span style="color:red;">*</span></label>
                                        <input type="number" class="form-control" name="current_mileage" id="current_mileage_<?= $key ?>" value="<?= esc($truck['current_mileage'] ?? '') ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="last_inspection_date_<?= $key ?>" class="form-label">Last Inspection Date <span style="color:red;">*</span></label>
                                        <input type="date" class="form-control" name="last_inspection_date" id="last_inspection_date_<?= $key ?>" value="<?= esc($truck['last_inspection_date'] ?? '') ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="last_inspection_mileage_<?= $key ?>" class="form-label">Last Inspection Mileage <span style="color:red;">*</span></label>
                                        <input type="number" class="form-control" name="last_inspection_mileage" id="last_inspection_mileage_<?= $key ?>" value="<?= esc($truck['last_inspection_mileage'] ?? '') ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="cor_number_<?= $key ?>" class="form-label">COR Number <span style="color:red;">*</span></label>
                                        <input type="text" class="form-control" name="cor_number" id="cor_number_<?= $key ?>" value="<?= esc($truck['cor_number']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="insurance_details_<?= $key ?>" class="form-label">Insurance Details <span style="color:red;">*</span></label>
                                        <input type="text" class="form-control" name="insurance_details" id="insurance_details_<?= $key ?>" value="<?= esc($truck['insurance_details']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="license_plate_expiry_<?= $key ?>" class="form-label">License Plate Expiry <span style="color:red;">*</span></label>
                                        <input type="date" class="form-control" name="license_plate_expiry" id="license_plate_expiry_<?= $key ?>" value="<?= esc($truck['license_plate_expiry']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="registration_expiry_<?= $key ?>" class="form-label">Registration Expiry <span style="color:red;">*</span></label>
                                        <input type="date" class="form-control" name="registration_expiry" id="registration_expiry_<?= $key ?>" value="<?= esc($truck['registration_expiry']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="truck_type_<?= $key ?>" class="form-label">Truck Type/Configuration <span style="color:red;">*</span></label>
                                        <input type="text" class="form-control" name="truck_type" id="truck_type_<?= $key ?>" value="<?= esc($truck['truck_type']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="fuel_type_<?= $key ?>" class="form-label">Fuel Type <span style="color:red;">*</span></label>
                                        <input type="text" class="form-control" name="fuel_type" id="fuel_type_<?= $key ?>" value="<?= esc($truck['fuel_type']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="truck_length_<?= $key ?>" class="form-label">Truck Length <span style="color:red;">*</span></label>
                                        <input type="text" class="form-control" name="truck_length" id="truck_length_<?= $key ?>" value="<?= esc($truck['truck_length']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="load_capacity_<?= $key ?>" class="form-label">Load Capacity (kg) <span style="color:red;">*</span></label>
                                        <input type="number" class="form-control" name="load_capacity" id="load_capacity_<?= $key ?>" value="<?= esc($truck['load_capacity']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="maintenance_technician_<?= $key ?>" class="form-label">Maintenance Technician</label>
                                        <input type="text" class="form-control" name="maintenance_technician" id="maintenance_technician_<?= $key ?>" value="<?= esc($truck['maintenance_technician']) ?>">
                                    </div>

                                    <!-- New Section for Individual Maintenance Items -->
                                    <hr>
                                    <h5>Maintenance Items</h5>
                                    <!-- Engine Oil & Filter -->
                                    <div class="mb-3">
                                        <label for="engine_oil_last_service_mileage_<?= $key ?>" class="form-label">Engine Oil - Last Service Mileage</label>
                                        <input type="number" class="form-control" name="engine_oil_last_service_mileage" id="engine_oil_last_service_mileage_<?= $key ?>" value="<?= esc($truck['maintenance_items']['engine_oil']['last_service_mileage'] ?? $truck['last_inspection_mileage'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="engine_oil_last_service_date_<?= $key ?>" class="form-label">Engine Oil - Last Service Date</label>
                                        <input type="date" class="form-control" name="engine_oil_last_service_date" id="engine_oil_last_service_date_<?= $key ?>" value="<?= esc($truck['maintenance_items']['engine_oil']['last_service_date'] ?? $truck['last_inspection_date'] ?? '') ?>">
                                    </div>

                                    <!-- Transmission Fluids & Filter -->
                                    <div class="mb-3">
                                        <label for="transmission_last_service_mileage_<?= $key ?>" class="form-label">Transmission - Last Service Mileage</label>
                                        <input type="number" class="form-control" name="transmission_last_service_mileage" id="transmission_last_service_mileage_<?= $key ?>" value="<?= esc($truck['maintenance_items']['transmission']['last_service_mileage'] ?? $truck['last_inspection_mileage'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="transmission_last_service_date_<?= $key ?>" class="form-label">Transmission - Last Service Date</label>
                                        <input type="date" class="form-control" name="transmission_last_service_date" id="transmission_last_service_date_<?= $key ?>" value="<?= esc($truck['maintenance_items']['transmission']['last_service_date'] ?? $truck['last_inspection_date'] ?? '') ?>">
                                    </div>

                                    <!-- Air Filters -->
                                    <div class="mb-3">
                                        <label for="air_filters_last_service_mileage_<?= $key ?>" class="form-label">Air Filters - Last Service Mileage</label>
                                        <input type="number" class="form-control" name="air_filters_last_service_mileage" id="air_filters_last_service_mileage_<?= $key ?>" value="<?= esc($truck['maintenance_items']['air_filters']['last_service_mileage'] ?? $truck['last_inspection_mileage'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="air_filters_last_service_date_<?= $key ?>" class="form-label">Air Filters - Last Service Date</label>
                                        <input type="date" class="form-control" name="air_filters_last_service_date" id="air_filters_last_service_date_<?= $key ?>" value="<?= esc($truck['maintenance_items']['air_filters']['last_service_date'] ?? $truck['last_inspection_date'] ?? '') ?>">
                                    </div>

                                    <!-- Brake Components -->
                                    <div class="mb-3">
                                        <label for="brake_components_last_service_mileage_<?= $key ?>" class="form-label">Brake Components - Last Service Mileage</label>
                                        <input type="number" class="form-control" name="brake_components_last_service_mileage" id="brake_components_last_service_mileage_<?= $key ?>" value="<?= esc($truck['maintenance_items']['brake_components']['last_service_mileage'] ?? $truck['last_inspection_mileage'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="brake_components_last_service_date_<?= $key ?>" class="form-label">Brake Components - Last Service Date</label>
                                        <input type="date" class="form-control" name="brake_components_last_service_date" id="brake_components_last_service_date_<?= $key ?>" value="<?= esc($truck['maintenance_items']['brake_components']['last_service_date'] ?? $truck['last_inspection_date'] ?? '') ?>">
                                    </div>

                                    <!-- Tires -->
                                    <div class="mb-3">
                                        <label for="tires_last_service_mileage_<?= $key ?>" class="form-label">Tires - Last Service Mileage</label>
                                        <input type="number" class="form-control" name="tires_last_service_mileage" id="tires_last_service_mileage_<?= $key ?>" value="<?= esc($truck['maintenance_items']['tires']['last_service_mileage'] ?? $truck['last_inspection_mileage'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="tires_last_service_date_<?= $key ?>" class="form-label">Tires - Last Service Date</label>
                                        <input type="date" class="form-control" name="tires_last_service_date" id="tires_last_service_date_<?= $key ?>" value="<?= esc($truck['maintenance_items']['tires']['last_service_date'] ?? $truck['last_inspection_date'] ?? '') ?>">
                                    </div>

                                    <!-- Belt & Hoses -->
                                    <div class="mb-3">
                                        <label for="belt_hoses_last_service_mileage_<?= $key ?>" class="form-label">Belt & Hoses - Last Service Mileage</label>
                                        <input type="number" class="form-control" name="belt_hoses_last_service_mileage" id="belt_hoses_last_service_mileage_<?= $key ?>" value="<?= esc($truck['maintenance_items']['belt_hoses']['last_service_mileage'] ?? $truck['last_inspection_mileage'] ?? '') ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="belt_hoses_last_service_date_<?= $key ?>" class="form-label">Belt & Hoses - Last Service Date</label>
                                        <input type="date" class="form-control" name="belt_hoses_last_service_date" id="belt_hoses_last_service_date_<?= $key ?>" value="<?= esc($truck['maintenance_items']['belt_hoses']['last_service_date'] ?? $truck['last_inspection_date'] ?? '') ?>">
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
                                    <a href="<?= base_url('resource/trucks/delete/' . $truck['truck_id']) ?>" class="btn btn-danger">Yes</a>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                  </div>
                                </div>
                              </div>
                            </div>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="10" class="text-center">No trucks found.</td></tr>
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
      <form action="<?= base_url('resource/trucks/create') ?>" method="post">
      <div class="modal-header">
        <h5 class="modal-title" id="createTruckModalLabel">Create New Truck</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Form fields for creating a new truck -->
        <div class="mb-3">
            <label for="truck_model" class="form-label">Truck Model <span style="color:red;">*</span></label>
            <input type="text" class="form-control" name="truck_model" id="truck_model" required>
        </div>
        <div class="mb-3">
            <label for="plate_number" class="form-label">Plate Number <span style="color:red;">*</span></label>
            <input type="text" class="form-control" name="plate_number" id="plate_number" required>
        </div>
        <div class="mb-3">
            <label for="engine_number" class="form-label">Engine Number <span style="color:red;">*</span></label>
            <input type="text" class="form-control" name="engine_number" id="engine_number" required>
        </div>
        <div class="mb-3">
            <label for="chassis_number" class="form-label">Chassis Number <span style="color:red;">*</span></label>
            <input type="text" class="form-control" name="chassis_number" id="chassis_number" required>
        </div>
        <div class="mb-3">
            <label for="color" class="form-label">Color <span style="color:red;">*</span></label>
            <input type="text" class="form-control" name="color" id="color" required>
        </div>
        <!-- Added Current Mileage Field -->
        <div class="mb-3">
            <label for="current_mileage" class="form-label">Current Mileage <span style="color:red;">*</span></label>
            <input type="number" class="form-control" name="current_mileage" id="current_mileage" required>
        </div>
        <div class="mb-3">
            <label for="last_inspection_date" class="form-label">Last Inspection Date<span style="color:red;">*</span></label>
            <input type="date" class="form-control" name="last_inspection_date" id="last_inspection_date" required>
        </div>
        <div class="mb-3">
            <label for="last_inspection_mileage" class="form-label">Last Inspection Mileage<span style="color:red;">*</span></label>
            <input type="number" class="form-control" name="last_inspection_mileage" id="last_inspection_mileage" required>
        </div>
        <div class="mb-3">
            <label for="cor_number" class="form-label">COR Number <span style="color:red;">*</span></label>
            <input type="text" class="form-control" name="cor_number" id="cor_number" required>
        </div>
        <div class="mb-3">
            <label for="insurance_details" class="form-label">Insurance Details <span style="color:red;">*</span></label>
            <input type="text" class="form-control" name="insurance_details" id="insurance_details" required>
        </div>
        <div class="mb-3">
            <label for="license_plate_expiry" class="form-label">License Plate Expiry <span style="color:red;">*</span></label>
            <input type="date" class="form-control" name="license_plate_expiry" id="license_plate_expiry" required>
        </div>
        <div class="mb-3">
            <label for="registration_expiry" class="form-label">Registration Expiry <span style="color:red;">*</span></label>
            <input type="date" class="form-control" name="registration_expiry" id="registration_expiry" required>
        </div>
        <div class="mb-3">
            <label for="truck_type" class="form-label">Truck Type/Configuration <span style="color:red;">*</span></label>
            <input type="text" class="form-control" name="truck_type" id="truck_type" required>
        </div>
        <div class="mb-3">
            <label for="fuel_type" class="form-label">Fuel Type <span style="color:red;">*</span></label>
            <input type="text" class="form-control" name="fuel_type" id="fuel_type" required>
        </div>
        <div class="mb-3">
            <label for="truck_length" class="form-label">Truck Length <span style="color:red;">*</span></label>
            <input type="text" class="form-control" name="truck_length" id="truck_length" required>
        </div>
        <div class="mb-3">
            <label for="load_capacity" class="form-label">Load Capacity (kg) <span style="color:red;">*</span></label>
            <input type="number" class="form-control" name="load_capacity" id="load_capacity" required>
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
            var truckModel = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
            row.style.display = truckModel.indexOf(searchValue) > -1 ? '' : 'none';
        });
    });
</script>

<script src="<?= base_url('assets/js/script.js') ?>"></script>
<?= $this->endSection() ?>
