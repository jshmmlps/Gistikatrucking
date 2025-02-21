<?= $this->extend('templates/layout.php') ?>

<?= $this->section('content') ?>
<link href=<?= base_url('public/assets/css/style.css'); ?> rel="stylesheet">
<title>Booking Management</title>
<h1>Booking Management</h1>

<div class="content">
    <div class="booking-list">
        <h2>View Booking</h2>
        <table class="table table-bordered table-hover align-middle text-center">
            <thead class="table-light text-dark">
                <tr>
                    <th>Client Name</th>
                    <th>Booking Date</th>
                    <th>Dispatch Date</th>
                    <th>Cargo Type</th>
                    <th>Drop-off Location</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td><?= $booking['client_name'] ?></td>
                    <td><?= $booking['booking_date'] ?></td>
                    <td><?= $booking['dispatch_date'] ?></td>
                    <td><?= $booking['cargo_type'] ?></td>
                    <td><?= $booking['drop_off_location'] ?></td>
                    <td><?= $booking['status'] ?></td>
                    <td>
                        <button type="button" class="btn btn-warning btn-sm fw-bold px-4 view-client text-dark"
                        data-id="<?= $booking['id'] ?>" 
                        data-bs-toggle="modal" 
                        data-bs-target="#bookingModal">View</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Booking Details Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center w-100" id="offcanvasRightLabel">Load Assignment</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
                <div class="modal-body">
                    <div id="booking-info">
                        <p><strong>Booking ID:</strong> <span id="booking-id"></span></p>
                        <p><strong>Client Name:</strong> <span id="booking-client"></span></p>
                        <p><strong>Booking Date:</strong> <span id="booking-date"></span></p>
                        <p><strong>Dispatch Date:</strong> <span id="booking-dispatch"></span></p>
                        <p><strong>Cargo Type:</strong> <span id="booking-cargo"></span></p>
                        <p><strong>Cargo Weight:</strong> <span id="booking-weight"></span></p>
                        <p><strong>Drop-off Location:</strong> <span id="booking-dropoff"></span></p>
                        <p><strong>Pick-up Location:</strong> <span id="booking-pickup"></span></p>
                        <p><strong>Truck Model:</strong> <span id="booking-truck"></span></p>
                        <p><strong>License Plate:</strong> <span id="booking-license"></span></p>
                        <p><strong>Conductor Name:</strong> <span id="booking-conductor"></span></p>
                        <p><strong>Driver Name:</strong> <span id="booking-driver"></span></p>
                        <p><strong>Type of Truck:</strong> <span id="booking-truck-type"></span></p>
                        <p><strong>Distance:</strong> <span id="booking-distance"></span></p>
                        <p><strong>Person of Contact:</strong> <span id="booking-contact-person"></span></p>
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
    document.querySelectorAll(".view-booking").forEach(button => {
        button.addEventListener("click", function() {
            let bookingId = this.getAttribute("data-id");

            fetch("<?= base_url('bookings/details/') ?>" + bookingId)
                .then(response => response.json())
                .then(data => {
                    document.getElementById("booking-id").innerText = data.booking_id;
                    document.getElementById("booking-client").innerText = data.client_name;
                    document.getElementById("booking-date").innerText = data.booking_date;
                    document.getElementById("booking-dispatch").innerText = data.dispatch_date;
                    document.getElementById("booking-cargo").innerText = data.cargo_type;
                    document.getElementById("booking-weight").innerText = data.cargo_weight;
                    document.getElementById("booking-dropoff").innerText = data.drop_off_location;
                    document.getElementById("booking-pickup").innerText = data.pick_up_location;
                    document.getElementById("booking-truck").innerText = data.truck_model;
                    document.getElementById("booking-license").innerText = data.license_plate;
                    document.getElementById("booking-conductor").innerText = data.conductor_name;
                    document.getElementById("booking-driver").innerText = data.driver_name;
                    document.getElementById("booking-truck-type").innerText = data.type_of_truck;
                    document.getElementById("booking-distance").innerText = data.distance;
                    document.getElementById("booking-contact-person").innerText = data.person_of_contact;
                })
                .catch(error => console.error("Error fetching booking details:", error));
        });
    });
});
</script>



<?= $this->endSection() ?>
