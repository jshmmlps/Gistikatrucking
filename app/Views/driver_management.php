<?= $this->extend('templates/layout.php') ?>

<?= $this->section('content') ?>
<link href=<?=base_url('public/assets/css/style.css'); ?> rel="stylesheet">
<title>Driver and Conductor Management</title>
<h1>Driver and Conductor Management</h1>

<div class="content">
    <div class="driver-list">
        <h2>Information List</h2>
        <table>
            <thead>
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
                    <td><a href="#" class="view-driver" data-id="<?= $driver['id'] ?>">View</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Driver Details Section -->
    <div class="driver-details">
        <h2>COMPLETE DETAILS</h2>
        <div id="driver-info">
            <p><strong>NAME:</strong> <span id="driver-name"></span></p>
            <p><strong>DATE OF EMPLOYMENT:</strong> <span id="driver-employment"></span></p>
            <p><strong>POSITION:</strong> <span id="driver-position"></span></p>
            <p><strong>LAST TRUCK ASSIGNED:</strong> <span id="driver-truck"></span></p>
            <p><strong>LICENSE NUMBER:</strong> <span id="driver-license"></span></p>
            <p><strong>LICENSE EXPIRY DATE:</strong> <span id="driver-expiry"></span></p>
            <p><strong>BIRTHDAY:</strong> <span id="driver-birthday"></span></p>
            <p><strong>MEDICAL RECORD:</strong> <span id="driver-medical"></span></p>
            <p><strong>TRIPS COMPLETED:</strong> <span id="driver-trips"></span></p>
            <p><strong>NOTES:</strong> <textarea id="driver-notes"></textarea></p>
        </div>
    </div>
</div>

<!-- JavaScript to Handle Click Event -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".view-driver").forEach(button => {
        button.addEventListener("click", function(event) {
            event.preventDefault();
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
