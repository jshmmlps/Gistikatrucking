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
                    <th>Home Address</th>
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
                    <td><?= $driver['home_address'] ?></td>
                    <td><?= $driver['employee_id'] ?></td>
                    <td>
                        <button type="button" class="btn btn-warning btn-sm fw-bold px-4 view-client text-dark" 
                        data-id="<?= $driver['id'] ?>"
                        data-bs-toggle="modal"
                        data-bs-target="#driverModal">View</button>
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
                <div id="driver-info">
                    <p><strong>Name:</strong> <span id="driver-name"></span></p>
                    <p><strong>Date of Employment:</strong> <span id="driver-employment"></span></p>
                    <p><strong>Position:</strong> <span id="driver-position"></span></p>
                    <p><strong>Last Truck Assigned:</strong> <span id="driver-truck"></span></p>
                    <p><strong>License Number:</strong> <span id="driver-license"></span></p>
                    <p><strong>License Expiry Date:</strong> <span id="driver-expiry"></span></p>
                    <p><strong>Birthday:</strong> <span id="driver-birthday"></span></p>
                    <p><strong>Medical Record:</strong> <span id="driver-medical"></span></p>
                    <p><strong>Trips Completed:</strong> <span id="driver-trips"></span></p>
                    <p><strong>Notes:</strong> <textarea id="driver-notes" class="form-control"></textarea></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-warning" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript to Handle Modal Pop-up -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".view-driver").forEach(button => {
        button.addEventListener("click", function() {
            let driverId = this.getAttribute("data-id");

            fetch("<?= base_url('drivers/details/') ?>" + driverId)
                .then(response => response.json())
                .then(data => {
                    document.getElementById("driver-name").innerText = data.first_name + " " + data.last_name;
                    document.getElementById("driver-employment").innerText = data.date_of_employment;
                    document.getElementById("driver-position").innerText = data.position;
                    document.getElementById("driver-truck").innerText = data.last_truck_assigned;
                    document.getElementById("driver-license").innerText = data.license_number;
                    document.getElementById("driver-expiry").innerText = data.license_expiry_date;
                    document.getElementById("driver-birthday").innerText = data.birthday;
                    document.getElementById("driver-medical").innerText = data.medical_record;
                    document.getElementById("driver-trips").innerText = data.trips_completed;
                    document.getElementById("driver-notes").value = data.notes || "";
                })
                .catch(error => console.error("Error fetching driver details:", error));
        });
    });
});
</script>


<?= $this->endSection() ?>
