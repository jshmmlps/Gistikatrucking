<?= $this->extend('templates/admin_layout.php') ?>

<?= $this->section('content') ?>
<link href="<?= base_url('public/assets/css/style.css'); ?>" rel="stylesheet">
<title>Driver/Conductor Management</title>

<div class="container-fluid mt-4">
    <h1>Driver/Conductor Management</h1>
    
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
    
    <!-- Filter Options: Search, Position, Truck -->
    <div class="row mb-3">
        <!-- Search by Name -->
        <div class="col-md-4 mb-2 mb-md-0">
            <input type="text" class="form-control" id="searchDriver" placeholder="Search by Name">
        </div>
        <!-- Filter by Position -->
        <div class="col-md-4 mb-2 mb-md-0">
            <select class="form-select" id="filterPosition">
                <option value="">All Positions</option>
                <option value="driver">Driver</option>
                <option value="conductor">Conductor</option>
            </select>
        </div>
        <!-- Filter by Truck Assigned -->
        <div class="col-md-4">
            <select class="form-select" id="filterTruck">
                <option value="">All Trucks</option>
                <option value="assigned">Assigned</option>
                <option value="not assigned">Not Assigned</option>
            </select>
        </div>
    </div>
    
    <!-- Create (Assign Truck) Button -->
    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createDriverModal">
        Assign Truck to Driver/Conductor
    </button>
    
    <!-- Drivers Table -->
    <table class="table table-bordered table-striped" id="driversTable">
        <thead>
            <tr>
                <th>Name</th>
                <th>Contact Number</th>
                <th>Position</th>
                <th>Employee ID</th>
                <th>Truck Assigned</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($drivers)): ?>
                <?php foreach($drivers as $key => $driver): ?>
                    <tr>
                        <td><?= esc(($driver['first_name'] ?? '') . ' ' . ($driver['last_name'] ?? '')) ?></td>
                        <td><?= esc($driver['contact_number'] ?? '') ?></td>
                        <td><?= esc($driver['position'] ?? '') ?></td>
                        <td><?= esc($driver['employee_id'] ?? '') ?></td>
                        <td>
                            <?php 
                                // If driver has a truck_assigned
                                $truckAssigned = $driver['truck_assigned'] ?? '';
                                if(!empty($truckAssigned)){
                                    // If it exists in $allTrucks
                                    if(isset($allTrucks[$truckAssigned])) {
                                        $truck = $allTrucks[$truckAssigned];
                                        $truckInfo = ($truck['truck_model'] ?? '') . ' (' . ($truck['plate_number'] ?? '') . ')';
                                        echo esc($truckInfo);
                                    } else {
                                        // If for some reason it's not found in allTrucks, show it as-is
                                        echo esc($truckAssigned);
                                    }
                                } else {
                                    // Must match "Not Assigned" EXACTLY (without extra spaces)
                                    echo 'Not Assigned';
                                }
                            ?>
                        </td>
                        <td>
                            <!-- View Button -->
                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewDriverModal<?= $key ?>">
                                View
                            </button>
                            <!-- Edit Button -->
                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editDriverModal<?= $key ?>">
                                Edit
                            </button>
                            <!-- Delete Button -->
                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteDriverModal<?= $key ?>">
                                Delete
                            </button>
                        </td>
                    </tr>
                    
                    <!-- View Driver Modal -->
                    <div class="modal fade" id="viewDriverModal<?= $key ?>" tabindex="-1" aria-labelledby="viewDriverModalLabel<?= $key ?>" aria-hidden="true">
                      <div class="modal-dialog modal-md">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title text-center w-100" id="viewDriverModalLabel<?= $key ?>">
                                Driver/Conductor Details (<?= esc(($driver['first_name'] ?? '') . ' ' . ($driver['last_name'] ?? '')) ?>)
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            <div class="p-3 rounded-3 shadow-sm bg-light">
                                <div class="d-flex justify-content-between border-bottom pb-2">
                                    <span class="fw-bold text-secondary">Driver ID:</span>
                                    <span class="text-muted"><?= esc($driver['driver_id'] ?? '') ?></span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom pb-2">
                                    <span class="fw-bold text-secondary">Name:</span>
                                    <span class="text-muted"><?= esc(($driver['first_name'] ?? '') . ' ' . ($driver['last_name'] ?? '')) ?></span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom pb-2">
                                    <span class="fw-bold text-secondary">Contact Number:</span>
                                    <span class="text-muted"><?= esc($driver['contact_number'] ?? '') ?></span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom pb-2">
                                    <span class="fw-bold text-secondary">Position:</span>
                                    <span class="text-muted"><?= esc($driver['position'] ?? '') ?></span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom pb-2">
                                    <span class="fw-bold text-secondary">Employee ID:</span>
                                    <span class="text-muted"><?= esc($driver['employee_id'] ?? '') ?></span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold text-secondary">Truck Assigned:</span>
                                    <span class="text-muted">
                                        <?php 
                                            if(!empty($truckAssigned) && isset($allTrucks[$truckAssigned])) {
                                                $truck = $allTrucks[$truckAssigned];
                                                echo esc(($truck['truck_model'] ?? '') . ' (' . ($truck['plate_number'] ?? '') . ')');
                                            } else if(!empty($truckAssigned)) {
                                                echo esc($truckAssigned);
                                            } else {
                                                echo 'Not Assigned';
                                            }
                                        ?>
                                    </span>
                                </div>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    <!-- Edit Driver Modal -->
                    <div class="modal fade" id="editDriverModal<?= $key ?>" tabindex="-1" aria-labelledby="editDriverModalLabel<?= $key ?>" aria-hidden="true">
                      <div class="modal-dialog modal-md">
                        <div class="modal-content">
                          <form action="<?= base_url('admin/driver/update/' . ($driver['driver_id'] ?? '')) ?>" method="POST">
                            <?= csrf_field() ?>
                            <div class="modal-header">
                                <h5 class="modal-title" id="editDriverModalLabel<?= $key ?>">
                                    Edit Truck Assignment for <?= esc(($driver['first_name'] ?? '') . ' ' . ($driver['last_name'] ?? '')) ?>
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="truck_assigned_<?= $key ?>" class="form-label">Truck Assigned</label>
                                    <select name="truck_assigned" id="truck_assigned_<?= $key ?>" class="form-select" required>
                                        <option value="">-- Select Truck --</option>
                                        <?php
                                        // Determine the appropriate trucks array based on position.
                                        $position = strtolower($driver['position'] ?? '');
                                        $trucksToUse = ($position === 'driver')
                                            ? $availableTrucksForDriver
                                            : $availableTrucksForConductor;

                                        foreach($trucksToUse as $truck):
                                        ?>
                                            <option value="<?= esc($truck['truck_id'] ?? '') ?>"
                                                <?= (($truck['truck_id'] ?? '') == ($driver['truck_assigned'] ?? '')) ? 'selected' : '' ?>>
                                                <?= esc(($truck['truck_model'] ?? '') . ' (' . ($truck['plate_number'] ?? '') . ')') ?>
                                            </option>
                                        <?php endforeach; ?>
                                        <!-- Optionally add an explicit "Not Assigned" choice:
                                             <option value="">Not Assigned</option> 
                                             (Then handle in your controller)
                                        -->
                                    </select>
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
                    
                    <!-- Delete Driver Modal -->
                    <div class="modal fade" id="deleteDriverModal<?= $key ?>" tabindex="-1" aria-labelledby="deleteDriverModalLabel<?= $key ?>" aria-hidden="true">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="deleteDriverModalLabel<?= $key ?>">
                                Delete Driver/Conductor (<?= esc(($driver['first_name'] ?? '') . ' ' . ($driver['last_name'] ?? '')) ?>)
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            Are you sure you want to delete this driver/conductor?
                          </div>
                          <div class="modal-footer">
                            <a href="<?= base_url('admin/driver/delete/' . ($driver['driver_id'] ?? '')) ?>" class="btn btn-danger">Yes</a>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center">No drivers found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Create (Assign Truck) Driver Modal -->
<div class="modal fade" id="createDriverModal" tabindex="-1" aria-labelledby="createDriverModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <form action="<?= base_url('admin/driver/create') ?>" method="POST">
        <?= csrf_field() ?>
        <div class="modal-header">
          <h5 class="modal-title" id="createDriverModalLabel">Assign Truck to Driver/Conductor</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Select eligible user -->
          <div class="mb-3">
            <label for="user_id" class="form-label">Select User</label>
            <select name="user_id" id="user_id" class="form-select" required>
              <option value="">-- Select a User --</option>
              <?php if (!empty($eligibleUsers)): ?>
                <?php foreach ($eligibleUsers as $uid => $user): ?>
                  <option value="<?= esc($uid) ?>" data-user-level="<?= esc(strtolower($user['user_level'] ?? '')) ?>">
                    <?= esc(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?> 
                    (<?= esc($user['contact_number'] ?? '') ?>)
                  </option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
          </div>
          <!-- Truck selection dropdown -->
          <div class="mb-3">
            <label for="truck_assigned" class="form-label">Truck Assigned</label>
            <select name="truck_assigned" id="truck_assigned" class="form-select" required>
              <option value="">-- Select Truck --</option>
              <!-- Options will be populated by JS based on the selected user's position -->
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Assign Truck</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Filtering function that checks Name, Position, and Truck Assigned columns
function filterRows() {
    var searchValue = document.getElementById('searchDriver').value.toLowerCase().trim();
    var filterPosition = document.getElementById('filterPosition').value.toLowerCase();
    var filterTruck = document.getElementById('filterTruck').value.toLowerCase();
    var rows = document.querySelectorAll('#driversTable tbody tr');
    
    rows.forEach(function(row) {
        var name = row.querySelector('td:nth-child(1)').textContent.toLowerCase().trim();
        var position = row.querySelector('td:nth-child(3)').textContent.toLowerCase().trim();
        var truck = row.querySelector('td:nth-child(5)').textContent.toLowerCase().trim();
        
        // Check name search
        var matchesSearch = (name.indexOf(searchValue) > -1);
        
        // Check position filter
        var matchesPosition = true;
        if (filterPosition) {
            matchesPosition = (position === filterPosition);
        }

        // Check truck filter
        // filterTruck === 'assigned' => truck cell != 'not assigned'
        // filterTruck === 'not assigned' => truck cell == 'not assigned'
        // filterTruck === '' => show all trucks
        var matchesTruck = true;
        if (filterTruck === 'assigned') {
            matchesTruck = (truck !== 'not assigned');
        } else if (filterTruck === 'not assigned') {
            matchesTruck = (truck === 'not assigned');
        }

        // Show row only if all conditions are true
        row.style.display = (matchesSearch && matchesPosition && matchesTruck) ? '' : 'none';
    });
}

// Add event listeners for filters
document.getElementById('searchDriver').addEventListener('keyup', filterRows);
document.getElementById('filterPosition').addEventListener('change', filterRows);
document.getElementById('filterTruck').addEventListener('change', filterRows);

// Populate truck dropdown in Create Modal based on user's position
var availableTrucksForDriver = <?= json_encode($availableTrucksForDriver ?? []) ?>;
var availableTrucksForConductor = <?= json_encode($availableTrucksForConductor ?? []) ?>;

document.getElementById('user_id').addEventListener('change', function() {
    var selectedOption = this.options[this.selectedIndex];
    var userLevel = selectedOption.getAttribute('data-user-level'); // "driver" or "conductor"
    var truckSelect = document.getElementById('truck_assigned');
    
    // Reset the truck dropdown
    truckSelect.innerHTML = '<option value="">-- Select Truck --</option>';
    
    // Choose the appropriate array based on user level.
    var trucksToUse = (userLevel === 'driver') ? availableTrucksForDriver : availableTrucksForConductor;
    
    for (var key in trucksToUse) {
        if (trucksToUse.hasOwnProperty(key)) {
            var truck = trucksToUse[key];
            var model = truck.truck_model || '';
            var plate = truck.plate_number || '';
            var optionText = model + ' (' + plate + ')';
            var option = document.createElement('option');
            option.value = truck.truck_id || '';
            option.text = optionText;
            truckSelect.appendChild(option);
        }
    }
});
</script>

<?= $this->endSection() ?>
