<?= $this->extend('templates/layout.php') ?>

<?= $this->section('content') ?>
<link href=<?= base_url('public/assets/css/style.css'); ?> rel="stylesheet">
<title>Booking Management</title>
<h1>Booking Management</h1>

<div class="content">
    <div class="booking-list">
        <h2>View Booking</h2>
        <table class="table table-striped" style="width:100%">
            <thead>
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
                    <td><button type="button" class="btn btn-secondary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight"><a href="#" class="view-booking" data-id="<?= $booking['id'] ?>">View</a></button>
                        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
                            <div class="offcanvas-header">
                                <h5 class="offcanvas-title" id="offcanvasRightLabel">Booking Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body">
                                <h2>Load Assignment</h2>
                                <!-- Booking Details Section -->
                                <div id="booking-info">
                                    <p><strong>BOOKING ID:</strong> <span id="booking-id"></span></p>
                                    <p><strong>CLIENT NAME:</strong> <span id="booking-client"></span></p>
                                    <p><strong>BOOKING DATE:</strong> <span id="booking-date"></span></p>
                                    <p><strong>DISPATCH DATE:</strong> <span id="booking-dispatch"></span></p>
                                    <p><strong>CARGO TYPE:</strong> <span id="booking-cargo"></span></p>
                                    <p><strong>CARGO WEIGHT:</strong> <span id="booking-weight"></span></p>
                                    <p><strong>DROP OFF LOCATION:</strong> <span id="booking-dropoff"></span></p>
                                    <p><strong>CONTACT NUMBER:</strong> <span id="booking-contact"></span></p>
                                    <p><strong>PICK UP LOCATION:</strong> <span id="booking-pickup"></span></p>
                                    <p><strong>TRUCK MODEL:</strong> <span id="booking-truck"></span></p>
                                    <p><strong>CONDUCTOR NAME:</strong> <span id="booking-conductor"></span></p>
                                    <p><strong>LICENSE PLATE:</strong> <span id="booking-license"></span></p>
                                    <p><strong>DRIVER NAME:</strong> <span id="booking-driver"></span></p>
                                    <p><strong>DISTANCE:</strong> <span id="booking-distance"></span></p>
                                    <p><strong>TYPE OF TRUCK:</strong> <span id="booking-truck-type"></span></p>
                                    <p><strong>PERSON OF CONTACT:</strong> <span id="booking-contact-person"></span></p>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- JavaScript to Handle Click Event -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".view-booking").forEach(button => {
        button.addEventListener("click", function(event) {
            event.preventDefault();
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
                    document.getElementById("booking-contact").innerText = data.contact_number;
                    document.getElementById("booking-pickup").innerText = data.pick_up_location;
                    document.getElementById("booking-truck").innerText = data.truck_model;
                    document.getElementById("booking-conductor").innerText = data.conductor_name;
                    document.getElementById("booking-license").innerText = data.license_plate;
                    document.getElementById("booking-driver").innerText = data.driver_name;
                    document.getElementById("booking-distance").innerText = data.distance;
                    document.getElementById("booking-truck-type").innerText = data.type_of_truck;
                    document.getElementById("booking-contact-person").innerText = data.person_of_contact;
                })
                .catch(error => console.error("Error fetching booking details:", error));
        });
    });
});
</script>

<?= $this->endSection() ?>
