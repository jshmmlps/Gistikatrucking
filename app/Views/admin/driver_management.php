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
    
    <!-- Search Bar -->
    <div class="mb-3">
        <input type="text" class="form-control" id="searchDriver" placeholder="Search by Name">
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
                        <td><?= esc($driver['first_name'] . ' ' . $driver['last_name']) ?></td>
                        <td><?= esc($driver['contact_number']) ?></td>
                        <td><?= esc($driver['position']) ?></td>
                        <td><?= esc($driver['employee_id']) ?></td>
                        <td>
                            <?php 
                                if(!empty($driver['truck_assigned'])):
                                    // Look up truck details from allTrucks (passed from controller)
                                    $truckInfo = '';
                                    if(isset($allTrucks[$driver['truck_assigned']])) {
                                        $truck = $allTrucks[$driver['truck_assigned']];
                                        $truckInfo = $truck['truck_model'] . ' (' . $truck['plate_number'] . ')';
                                    } else {
                                        $truckInfo = $driver['truck_assigned'];
                                    }
                                    echo esc($truckInfo);
                                else:
                                    echo 'Not Assigned';
                                endif;
                            ?>
                        </td>
                        <td>
                            <!-- View Button -->
                            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#viewDriverModal<?= $key ?>">
                                View
                            </button>
                            <!-- Edit Button (only truck assignment is editable) -->
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
                            <h5 class="modal-title text-center w-100" id="viewDriverModalLabel<?= $key ?>">Driver/Conductor Details (<?= esc($driver['first_name'] . ' ' . $driver['last_name']) ?>)</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            <div class="p-3 rounded-3 shadow-sm bg-light">
                                <!-- (Display details as desired; here we show truck assignment as well) -->
                                <div class="d-flex justify-content-between border-bottom pb-2">
                                    <span class="fw-bold text-secondary">Driver ID:</span>
                                    <span class="text-muted"><?= esc($driver['driver_id']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom pb-2">
                                    <span class="fw-bold text-secondary">Name:</span>
                                    <span class="text-muted"><?= esc($driver['first_name'] . ' ' . $driver['last_name']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom pb-2">
                                    <span class="fw-bold text-secondary">Contact Number:</span>
                                    <span class="text-muted"><?= esc($driver['contact_number']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom pb-2">
                                    <span class="fw-bold text-secondary">Position:</span>
                                    <span class="text-muted"><?= esc($driver['position']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom pb-2">
                                    <span class="fw-bold text-secondary">Employee ID:</span>
                                    <span class="text-muted"><?= esc($driver['employee_id']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom pb-2">
                                    <span class="fw-bold text-secondary">Truck Assigned:</span>
                                    <span class="text-muted">
                                        <?php 
                                            if(!empty($driver['truck_assigned'])):
                                                if(isset($allTrucks[$driver['truck_assigned']])) {
                                                    $truck = $allTrucks[$driver['truck_assigned']];
                                                    echo esc($truck['truck_model'] . ' (' . $truck['plate_number'] . ')');
                                                } else {
                                                    echo esc($driver['truck_assigned']);
                                                }
                                            else:
                                                echo 'Not Assigned';
                                            endif;
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
                    
                    <!-- Edit Driver Modal (only truck assignment editable) -->
                    <div class="modal fade" id="editDriverModal<?= $key ?>" tabindex="-1" aria-labelledby="editDriverModalLabel<?= $key ?>" aria-hidden="true">
                      <div class="modal-dialog modal-md">
                        <div class="modal-content">
                          <form action="<?= base_url('admin/driver/update/' . $driver['driver_id']) ?>" method="POST">
                          <div class="modal-header">
                            <h5 class="modal-title" id="editDriverModalLabel<?= $key ?>">Edit Truck Assignment for <?= esc($driver['first_name'] . ' ' . $driver['last_name']) ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            <div class="mb-3">
                                <label for="truck_assigned_<?= $key ?>" class="form-label">Truck Assigned</label>
                                <select name="truck_assigned" id="truck_assigned_<?= $key ?>" class="form-select" required>
                                    <option value="">-- Select Truck --</option>
                                    <?php
                                    // Determine the appropriate trucks array based on position.
                                    $position = strtolower($driver['position']);
                                    $trucksToUse = ($position === 'driver') ? $availableTrucksForDriver : $availableTrucksForConductor;
                                    foreach($trucksToUse as $truck):
                                    ?>
                                        <option value="<?= esc($truck['truck_id']) ?>" <?= ($truck['truck_id'] == $driver['truck_assigned']) ? 'selected' : '' ?>>
                                            <?= esc($truck['truck_model'] . ' (' . $truck['plate_number'] . ')') ?>
                                        </option>
                                    <?php endforeach; ?>
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
                            <h5 class="modal-title" id="deleteDriverModalLabel<?= $key ?>">Delete Driver/Conductor (<?= esc($driver['first_name'] . ' ' . $driver['last_name']) ?>)</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            Are you sure you want to delete this driver/conductor?
                          </div>
                          <div class="modal-footer">
                            <a href="<?= base_url('admin/driver/delete/' . $driver['driver_id']) ?>" class="btn btn-danger">Yes</a>
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
      <form action="<?= base_url('admin/driver/create') ?>" method="post">
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
                  <option value="<?= esc($uid) ?>" data-user-level="<?= esc(strtolower($user['user_level'])) ?>">
                    <?= esc($user['first_name'] . ' ' . $user['last_name']) ?> (<?= esc($user['contact_number']) ?>)
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
              <!-- Options will be populated dynamically based on selected user -->
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
    // Search functionality
    document.getElementById('searchDriver').addEventListener('keyup', function() {
        var searchValue = this.value.toLowerCase();
        var rows = document.querySelectorAll('#driversTable tbody tr');
        rows.forEach(function(row) {
            var name = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
            row.style.display = (name.indexOf(searchValue) > -1) ? '' : 'none';
        });
    });

    // Populate truck dropdown in Create Modal based on selected user's position.
    // Available trucks arrays for driver and conductor are passed from controller.
    var availableTrucksForDriver = <?= json_encode($availableTrucksForDriver) ?>;
    var availableTrucksForConductor = <?= json_encode($availableTrucksForConductor) ?>;

    document.getElementById('user_id').addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];
        var userLevel = selectedOption.getAttribute('data-user-level'); // "driver" or "conductor"
        var truckSelect = document.getElementById('truck_assigned');
        
        // Reset the truck dropdown
        truckSelect.innerHTML = '<option value="">-- Select Truck --</option>';

        // Choose the appropriate trucks array based on user level.
        var trucksToUse = (userLevel === 'driver') ? availableTrucksForDriver : availableTrucksForConductor;

        for (var key in trucksToUse) {
            if (trucksToUse.hasOwnProperty(key)) {
                var truck = trucksToUse[key];
                var optionText = truck.truck_model + ' (' + truck.plate_number + ')';
                var option = document.createElement('option');
                option.value = truck.truck_id;
                option.text = optionText;
                truckSelect.appendChild(option);
            }
        }
    });
</script>

<?= $this->endSection() ?>
