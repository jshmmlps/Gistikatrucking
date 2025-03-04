<?= $this->extend('templates/client_layout.php') ?>

<?= $this->section('content') ?>
<link href=<?=base_url('public/assets/css/style.css');?> rel="stylesheet">
<body>
<title>Bookings</title>
<h1>Bookings</h1>


<div class="container-fluid mt-4">
    <!-- Display any flash messages -->
    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createBookingModal">
        Create Booking
    </button>

    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Booking ID</th>
            <th>Date Submitted</th>
            <th>Dispatch Date</th>
            <th>Cargo Type</th>
            <th>Drop-off Address</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php if(!empty($bookings) && is_array($bookings)): ?>
            <?php foreach($bookings as $booking): ?>
            <tr>
                <td><?= $booking['booking_id'] ?></td>
                <td><?= $booking['booking_date'] ?></td>
                <td><?= $booking['dispatch_date'] ?></td>
                <td><?= $booking['cargo_type'] ?></td>
                <td><?= $booking['drop_off_address'] ?></td>
                <td><?= $booking['status'] ?></td>
                <td>
                <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#bookingModal<?= $booking['booking_id'] ?>">View</button>
                </td>
            </tr>
            <!-- Modal for booking details -->
            <div class="modal fade" id="bookingModal<?= $booking['booking_id'] ?>" tabindex="-1" aria-labelledby="bookingModalLabel<?= $booking['booking_id'] ?>" aria-hidden="true">
                <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                    <h5 class="modal-title" id="bookingModalLabel<?= $booking['booking_id'] ?>">Booking Details (ID: <?= $booking['booking_id'] ?>)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                    <p><strong>Name:</strong> <?= $booking['name'] ?></p>
                    <p><strong>Contact Number:</strong> <?= $booking['contact_number'] ?></p>
                    <p><strong>Pick-up Address:</strong> <?= $booking['pick_up_address'] ?></p>
                    <p><strong>Drop-off Address:</strong> <?= $booking['drop_off_address'] ?></p>
                    <p><strong>Person of Contact:</strong> <?= $booking['person_of_contact'] ?></p>
                    <p><strong>Dispatch Date:</strong> <?= $booking['dispatch_date'] ?></p>
                    <p><strong>Cargo Type:</strong> <?= $booking['cargo_type'] ?></p>
                    <p><strong>Cargo Weight:</strong> <?= $booking['cargo_weight'] ?></p>
                    <p><strong>Delivery Note:</strong> <?= $booking['delivery_note'] ?></p>
                    <p><strong>Truck Model:</strong> <?= $booking['truck_model'] ?></p>
                    <p><strong>Conductor Name:</strong> <?= $booking['conductor_name'] ?></p>
                    <p><strong>Driver Name:</strong> <?= $booking['driver_name'] ?></p>
                    <p><strong>License Plate:</strong> <?= $booking['license_plate'] ?></p>
                    <p><strong>Distance:</strong> <?= $booking['distance'] ?></p>
                    <p><strong>Type of Truck:</strong> <?= $booking['type_of_truck'] ?></p>
                    <p><strong>Status:</strong> <?= $booking['status'] ?></p>
                    </div>
                    <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
            <td colspan="7" class="text-center">No bookings found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>   
</div>

<!-- Create Booking Modal -->
<div class="modal fade" id="createBookingModal" tabindex="-1" aria-labelledby="createBookingModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="<?= base_url('client/store-booking') ?>" method="post">
        <?= csrf_field() ?>
        <div class="modal-header">
          <h5 class="modal-title" id="createBookingModalLabel">Create Booking</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Booking form fields -->
          <div class="mb-3">
            <label for="name" class="form-label">Name *</label>
            <input type="text" name="name" id="name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="contact_number" class="form-label">Contact Number *</label>
            <input type="text" name="contact_number" id="contact_number" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="address" class="form-label">Address *</label>
            <input type="text" name="address" id="address" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="person_of_contact" class="form-label">Person of Contact *</label>
            <input type="text" name="person_of_contact" id="person_of_contact" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email (Optional)</label>
            <input type="email" name="email" id="email" class="form-control">
          </div>
          <div class="mb-3">
            <label for="dispatch_date" class="form-label">Date of Dispatch *</label>
            <input type="date" name="dispatch_date" id="dispatch_date" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="pick_up_address" class="form-label">Pick-up Address *</label>
            <input type="text" name="pick_up_address" id="pick_up_address" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="drop_off_address" class="form-label">Drop-off Address *</label>
            <input type="text" name="drop_off_address" id="drop_off_address" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="cargo_type" class="form-label">Cargo Type *</label>
            <input type="text" name="cargo_type" id="cargo_type" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="cargo_weight" class="form-label">Cargo Weight *</label>
            <input type="number" name="cargo_weight" id="cargo_weight" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="delivery_note" class="form-label">Delivery Note (Optional)</label>
            <textarea name="delivery_note" id="delivery_note" class="form-control"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Submit Booking</button>
        </div>
      </form>
    </div>
  </div>
</div>


<script src="<?= base_url('assets/js/script.js') ?>"></script>
</body>
</html>

<?= $this->endSection() ?>