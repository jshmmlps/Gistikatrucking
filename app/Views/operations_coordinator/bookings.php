<?= $this->extend('templates/operations_coordinator_layout.php') ?>

<?= $this->section('content') ?>
<link href="<?= base_url('public/assets/css/style.css'); ?>" rel="stylesheet">
<body>
<title>Profile</title>
<h1>Profile</h1>

<div class="container-fluid mt-4">
    <!-- Display any flash messages -->
    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Booking ID</th>
          <th>Date Submitted</th>
          <th>Client Name</th>
          <th>Dispatch Date</th>
          <th>Cargo Type</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if(!empty($bookings) && is_array($bookings)): ?>
          <?php foreach($bookings as $booking): ?>
            <?php 
                // Skip invalid bookings
                if (!is_array($booking)) {
                    continue;
                }
            ?>
            <tr>
              <td><?= $booking['booking_id'] ?></td>
              <td><?= $booking['booking_date'] ?></td>
              <td><?= $booking['name'] ?></td>
              <td><?= $booking['dispatch_date'] ?></td>
              <td><?= $booking['cargo_type'] ?></td>
              <td><?= $booking['status'] ?></td>
              <td>
                <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#bookingModal<?= $booking['booking_id'] ?>">View/Update</button>
              </td>
            </tr>
            <!-- Modal for viewing and updating booking -->
            <div class="modal fade" id="bookingModal<?= $booking['booking_id'] ?>" tabindex="-1" aria-labelledby="bookingModalLabel<?= $booking['booking_id'] ?>" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form action="<?= base_url('admin/update-booking-status') ?>" method="post" id="updateForm<?= $booking['booking_id'] ?>">
                    <?= csrf_field() ?>
                    <div class="modal-header">
                      <h5 class="modal-title" id="bookingModalLabel<?= $booking['booking_id'] ?>">Booking Details (ID: <?= $booking['booking_id'] ?>)</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <p><strong>Name:</strong> <?= $booking['name'] ?></p>
                      <p><strong>Contact Number:</strong> <?= $booking['contact_number'] ?></p>
                      <p><strong>Pick-up Address:</strong> <?= $booking['pick_up_address'] ?></p>
                      <p><strong>Drop-off Address:</strong> <?= $booking['drop_off_address'] ?></p>
                      <p><strong>Dispatch Date:</strong> <?= $booking['dispatch_date'] ?></p>
                      <p><strong>Cargo Type:</strong> <?= $booking['cargo_type'] ?></p>
                      <p><strong>Cargo Weight:</strong> <?= $booking['cargo_weight'] ?></p>
                      <p><strong>Delivery Note:</strong> <?= $booking['delivery_note'] ?></p>
                      <p><strong>Truck Model:</strong> <span id="truckModel<?= $booking['booking_id'] ?>"><?= $booking['truck_model'] ?></span></p>
                      <p><strong>Current Driver:</strong> <span id="currentDriver<?= $booking['booking_id'] ?>"><?= $booking['driver_name'] ?></span></p>
                      <p><strong>Current Conductor:</strong> <span id="currentConductor<?= $booking['booking_id'] ?>"><?= $booking['conductor_name'] ?></span></p>
                      <p><strong>License Plate:</strong> <?= $booking['license_plate'] ?></p>
                      <p><strong>Type of Truck:</strong> <?= $booking['type_of_truck'] ?></p>
                      
                      <!-- Editable Distance Field -->
                      <div class="mb-3">
                        <label for="distance<?= $booking['booking_id'] ?>" class="form-label">Distance (km):</label>
                        <input type="number" name="distance" id="distance<?= $booking['booking_id'] ?>" class="form-control" value="<?= $booking['distance'] ?>" required>
                      </div>
                      
                      
                      <p><strong>Conductor Name:</strong> <?= $booking['conductor_name'] ?></p>
                      <p><strong>Driver Name:</strong> <?= $booking['driver_name'] ?></p>

                      <!-- Hidden field to store the truck id, updated automatically -->
                      <input type="hidden" name="truck_id" id="truck_id<?= $booking['booking_id'] ?>" value="<?= $booking['truck_id'] ?>">
                      
                      <!-- Dropdown to update booking status -->
                      <div class="mb-3">
                        <label for="status<?= $booking['booking_id'] ?>" class="form-label">Update Status:</label>
                        <select name="status" id="status<?= $booking['booking_id'] ?>" class="form-select">
                          <option value="approved" <?= ($booking['status'] == 'approved') ? 'selected' : '' ?>>Approve</option>
                          <option value="rejected" <?= ($booking['status'] == 'rejected') ? 'selected' : '' ?>>Reject</option>
                        </select>
                      </div>
                      <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" class="btn btn-primary">Submit Update</button>
                    </div>
                  </form>
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

<script src="<?= base_url('assets/js/script.js') ?>"></script>
</body>
</html>
<?= $this->endSection() ?>
