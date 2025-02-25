<?= $this->extend('templates/layout.php') ?>

<?= $this->section('content') ?>
<link href=<?=base_url('public/assets/css/style.css'); ?> rel="stylesheet">
<title>Driver and Conductor Management</title>
<h1>Driver and Conductor Management</h1>

<div class="content">
    <div class="driver-list">
        <h2>Information List</h2>
        <table class="table table-bordered table-hover align-middle text-center" style="width:100%">
            <thead class="table-light text-dark">
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Contact Number</th>
                    <th>Position</th>
                    <th>Employee ID</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($drivers as $driver): ?>
                <tr>
                    <td><?= $driver['first_name'] ?></td>
                    <td><?= $driver['last_name'] ?></td>
                    <td><?= $driver['contact_number'] ?></td>
                    <td><?= $driver['position'] ?></td>
                    <td><?= $driver['employee_id'] ?></td>
                    <td>
                        <button type="button" class="btn btn-warning btn-sm fw-bold px-4 view-client text-dark" 
                        data-bs-toggle="modal"
                        data-bs-target="#driverModal"
                        onclick='getAllDrivers(<?= json_encode($driver) ?>)'>View</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Driver Details Modal -->
<div class="modal fade" id="driverModal" tabindex="-1" aria-labelledby="driverModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
            <h4 class="modal-title text-center w-100" id="offcanvasRightLabel">Complete Driver Details</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
                <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <tbody>
                        <tr>
                    <th>Name:</th>
                    <td id="driverNamePlaceholder"></td>
                </tr>
                <tr>
                    <th>Contact Number:</th>
                    <td id="contactNumberPlaceholder"></td>
                </tr>
                <tr>
                    <th>Date of Employment:</th>
                    <td id="dateOfEmploymentPlaceholder"></td>
                </tr>
                <tr>
                    <th>Address:</th>
                    <td id="addressPlaceholder"></td>
                </tr>
                <tr>
                    <th>Position:</th>
                    <td id="positionPlaceholder"></td>
                </tr>
                <tr>
                    <th>Employee ID:</th>
                    <td id="employeeIdPlaceholder"></td>
                </tr>
                <tr>
                    <th>Last Truck Assigned:</th>
                    <td id="lastTruckPlaceholder"></td>
                </tr>
                <tr>
                    <th>License Number:</th>
                    <td id="licenseNumberPlaceholder"></td>
                </tr>
                <tr>
                    <th>License Expiry Date:</th>
                    <td id="licenseExpiryPlaceholder"></td>
                </tr>
                <tr>
                    <th>Birthday:</th>
                    <td id="birthdayPlaceholder"></td>
                </tr>
                <tr>
                    <th>Medical Record:</th>
                    <td id="medicalRecordPlaceholder"></td>
                </tr>
                <tr>
                    <th>Trips Completed:</th>
                    <td id="tripsCompletedPlaceholder"></td>
                </tr>
                <tr>
                                <th class="fw-bold">Notes:</th>
                                <td>
                                    <textarea id="driver-notes" class="form-control" rows="3"></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-warning" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

    <!-- Key Indicators Container (if needed) -->
    <div id="keyIndicatorsContainer" style="display:none;">
        <div id="keyIndicatorsIcons"></div>
    </div>
</div>

<!-- JavaScript Section -->
<script>
    // Function to load driver details into the offcanvas
    let driverData = <?= json_encode($driver) ?>;
    // Function to hide the key indicators container
    function hideKeyIndicators() {
        document.getElementById("keyIndicatorsContainer").style.display = "none";
    }
    function getDriverDetails(driverData) {
        document.getElementById("driverNamePlaceholder").innerText = (driverData.first_name                         || 'N/A') + ' ' + (driverData.last_name || '');
        document.getElementById("contactNumberPlaceholder").innerText = driverData.contact_number                   || 'N/A';
        document.getElementById("dateOfEmploymentPlaceholder").innerText = driverData.date_of_employment            || 'N/A';
        document.getElementById("positionPlaceholder").innerText = driverData.position                              || 'N/A';
        document.getElementById("employeeIdPlaceholder").innerText = driverData.employee_ID                         || 'N/A';
        document.getElementById("lastTruckPlaceholder").innerText = driverData.last_truck                           || 'N/A';
        document.getElementById("licenseNumberPlaceholder").innerText = driverData.license_number                   || 'N/A';
        document.getElementById("licenseExpiryPlaceholder").innerText = driverData.license_expiry                   || 'N/A';
        document.getElementById("birthdayPlaceholder").innerText = driverData.birthday                              || 'N/A';
        document.getElementById("medicalRecordPlaceholder").innerText = driverData.medical_record                   || 'N/A';
        document.getElementById("tripsCompletedPlaceholder").innerText = driverData.trip_completed                  || 'N/A';
        document.getElementById("notesPlaceholder").innerText = driverData.notes                                    || 'N/A';
    }
</script>
<?= $this->endSection() ?>

