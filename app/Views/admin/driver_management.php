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
        <input type="text" class="form-control" id="searchDriver" placeholder="Search by First or Last Name">
    </div>
    
    <!-- Create Driver Button -->
    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createDriverModal">
        Create Driver/Conductor
    </button>
    
    <!-- Drivers Table -->
    <table class="table table-bordered table-striped" id="driversTable">
        <thead>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Contact Number</th>
                <th>Position</th>
                <th>Home Address</th>
                <th>Employee ID</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(!empty($drivers)): ?>
                <?php foreach($drivers as $key => $driver): ?>
                    <tr>
                        <td><?= esc($driver['first_name']) ?></td>
                        <td><?= esc($driver['last_name']) ?></td>
                        <td><?= esc($driver['contact_number']) ?></td>
                        <td><?= esc($driver['position']) ?></td>
                        <td><?= esc($driver['home_address']) ?></td>
                        <td><?= esc($driver['employee_id']) ?></td>
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
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="viewDriverModalLabel<?= $key ?>">Driver/Conductor Details (<?= esc($driver['first_name'] . ' ' . $driver['last_name']) ?>)</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            <p><strong>Driver ID:</strong> <?= esc($driver['driver_id']) ?></p>
                            <p><strong>First Name:</strong> <?= esc($driver['first_name']) ?></p>
                            <p><strong>Last Name:</strong> <?= esc($driver['last_name']) ?></p>
                            <p><strong>Contact Number:</strong> <?= esc($driver['contact_number']) ?></p>
                            <p><strong>Position:</strong> <?= esc($driver['position']) ?></p>
                            <p><strong>Home Address:</strong> <?= esc($driver['home_address']) ?></p>
                            <p><strong>Employee ID:</strong> <?= esc($driver['employee_id']) ?></p>
                            <p><strong>Date of Employment:</strong> <?= esc($driver['date_of_employment']) ?></p>
                            <p><strong>Truck Assigned:</strong> <?= esc($driver['truck_assigned']) ?></p>
                            <p><strong>License Number:</strong> <?= esc($driver['license_number']) ?></p>
                            <p><strong>License Expiry:</strong> <?= esc($driver['license_expiry']) ?></p>
                            <p><strong>Birthday:</strong> <?= esc($driver['birthday']) ?></p>
                            <p><strong>Medical Record:</strong> <?= esc($driver['medical_record']) ?></p>
                            <p><strong>Trips Completed:</strong> <?= esc($driver['trips_completed']) ?></p>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    <!-- Edit Driver Modal -->
                    <div class="modal fade" id="editDriverModal<?= $key ?>" tabindex="-1" aria-labelledby="editDriverModalLabel<?= $key ?>" aria-hidden="true">
                      <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                          <form action="<?= base_url('admin/driver/update/' . $driver['driver_id']) ?>" method="POST">
                          <div class="modal-header">
                            <h5 class="modal-title" id="editDriverModalLabel<?= $key ?>">Edit Driver/Conductor (<?= esc($driver['first_name'] . ' ' . $driver['last_name']) ?>)</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                          </div>
                          <div class="modal-body">
                            <!-- For edit, you may allow editing only additional fields -->
                            <div class="mb-3">
                                <label for="employee_id_<?= $key ?>" class="form-label">Employee ID</label>
                                <input type="text" class="form-control" name="employee_id" id="employee_id_<?= $key ?>" value="<?= esc($driver['employee_id']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="date_of_employment_<?= $key ?>" class="form-label">Date of Employment</label>
                                <input type="date" class="form-control" name="date_of_employment" id="date_of_employment_<?= $key ?>" value="<?= esc($driver['date_of_employment']) ?>">
                            </div>
                            <div class="mb-3">
                                <label for="truck_assigned_<?= $key ?>" class="form-label">Truck Assigned</label>
                                <input type="text" class="form-control" name="truck_assigned" id="truck_assigned_<?= $key ?>" value="<?= esc($driver['truck_assigned']) ?>">
                            </div>
                            <div class="mb-3">
                                <label for="license_number_<?= $key ?>" class="form-label">License Number</label>
                                <input type="text" class="form-control" name="license_number" id="license_number_<?= $key ?>" value="<?= esc($driver['license_number']) ?>">
                            </div>
                            <div class="mb-3">
                                <label for="license_expiry_<?= $key ?>" class="form-label">License Expiry</label>
                                <input type="date" class="form-control" name="license_expiry" id="license_expiry_<?= $key ?>" value="<?= esc($driver['license_expiry']) ?>">
                            </div>
                            <div class="mb-3">
                                <label for="medical_record_<?= $key ?>" class="form-label">Medical Record</label>
                                <textarea class="form-control" name="medical_record" id="medical_record_<?= $key ?>"><?= esc($driver['medical_record']) ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="trips_completed_<?= $key ?>" class="form-label">Trips Completed</label>
                                <input type="number" class="form-control" name="trips_completed" id="trips_completed_<?= $key ?>" value="<?= esc($driver['trips_completed']) ?>">
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
                <tr><td colspan="7" class="text-center">No drivers found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Create Driver Modal -->
<div class="modal fade" id="createDriverModal" tabindex="-1" aria-labelledby="createDriverModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="<?= base_url('admin/driver/create') ?>" method="post">
        <div class="modal-header">
          <h5 class="modal-title" id="createDriverModalLabel">Create New Driver/Conductor</h5>
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
          <!-- Employee ID -->
          <div class="mb-3">
            <label for="employee_id" class="form-label">Employee ID</label>
            <input type="text" class="form-control" name="employee_id" id="employee_id" required>
          </div>
          <!-- Date of Employment -->
          <div class="mb-3">
            <label for="date_of_employment" class="form-label">Date of Employment</label>
            <input type="date" class="form-control" name="date_of_employment" id="date_of_employment" max="<?= date('Y-m-d') ?>">
          </div>
          <!-- Truck selection dropdown -->
          <div class="mb-3">
            <label for="truck_assigned" class="form-label">Truck Assigned</label>
            <select name="truck_assigned" id="truck_assigned" class="form-select" required>
              <option value="">-- Select Truck --</option>
              <!-- Options will be populated dynamically based on user selection -->
            </select>
          </div>
          <!-- License Number -->
          <div class="mb-3">
            <label for="license_number" class="form-label">License Number</label>
            <input type="text" class="form-control" name="license_number" id="license_number" required>
          </div>
          <!-- License Expiry -->
          <div class="mb-3">
            <label for="license_expiry" class="form-label">License Expiry</label>
            <input type="date" class="form-control" name="license_expiry" id="license_expiry" min="<?= date('Y-m-d') ?>" required>
          </div>
          <!-- Medical Record (optional) -->
          <div class="mb-3">
            <label for="medical_record" class="form-label">Medical Record</label>
            <textarea class="form-control" name="medical_record" id="medical_record"></textarea>
          </div>
          <!-- Trips Completed -->
          <div class="mb-3">
            <label for="trips_completed" class="form-label">Trips Completed</label>
            <input type="number" class="form-control" name="trips_completed" id="trips_completed" min="0" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Create Driver/Conductor</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
    // Dynamically filter drivers by first or last name
    document.getElementById('searchDriver').addEventListener('keyup', function() {
        var searchValue = this.value.toLowerCase();
        var rows = document.querySelectorAll('#driversTable tbody tr');
        rows.forEach(function(row) {
            var firstName = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
            var lastName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
            row.style.display = (firstName.indexOf(searchValue) > -1 || lastName.indexOf(searchValue) > -1) ? '' : 'none';
        });
    });

    // Available trucks arrays passed from the controller
    var availableTrucksForDriver = <?= json_encode($availableTrucksForDriver) ?>;
    var availableTrucksForConductor = <?= json_encode($availableTrucksForConductor) ?>;

    document.getElementById('user_id').addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];
        var userLevel = selectedOption.getAttribute('data-user-level'); // driver or conductor
        var truckSelect = document.getElementById('truck_assigned');
        
        // Reset the truck dropdown
        truckSelect.innerHTML = '<option value="">-- Select Truck --</option>';

        // Choose the appropriate trucks array based on user level
        var trucksToUse = (userLevel === 'driver') ? availableTrucksForDriver : availableTrucksForConductor;

        // Loop over trucksToUse and create options
        for (var key in trucksToUse) {
        if (trucksToUse.hasOwnProperty(key)) {
            var truck = trucksToUse[key];
            // Assume each truck object includes truck_id, truck_model, and plate_number
            var optionText = truck.truck_model + ' (' + truck.plate_number + ')';
            var option = document.createElement('option');
            option.value = truck.truck_id;
            option.text = optionText;
            truckSelect.appendChild(option);
        }
        }
    });
</script>

<script src="<?= base_url('assets/js/script.js') ?>"></script>
<?= $this->endSection() ?>
