<?= $this->extend('templates/operations_coordinator_layout.php') ?>

<?= $this->section('content') ?>
<link href="<?= base_url('public/assets/css/style.css'); ?>" rel="stylesheet">
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
                // Ensure coordinate fields exist (or set defaults)
                $pickupLat  = $booking['pick_up_lat'] ?? '0';
                $pickupLng  = $booking['pick_up_lng'] ?? '0';
                $dropoffLat = $booking['drop_off_lat'] ?? '0';
                $dropoffLng = $booking['drop_off_lng'] ?? '0';
            ?>
            <tr>
              <td><?= esc($booking['booking_id']) ?></td>
              <td><?= esc($booking['booking_date']) ?></td>
              <td><?= esc($booking['name']) ?></td>
              <td><?= esc($booking['dispatch_date']) ?></td>
              <td><?= esc($booking['cargo_type']) ?></td>
              <td><?= esc($booking['status']) ?></td>
              <td>
                <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#bookingModal<?= esc($booking['booking_id']) ?>">View/Update</button>
              </td>
            </tr>
            <!-- Modal for viewing and updating booking -->
            <div class="modal fade booking-modal" 
                 id="bookingModal<?= esc($booking['booking_id']) ?>" 
                 data-booking-id="<?= esc($booking['booking_id']) ?>"
                 data-pickup-lat="<?= esc($pickupLat) ?>"
                 data-pickup-lng="<?= esc($pickupLng) ?>"
                 data-dropoff-lat="<?= esc($dropoffLat) ?>"
                 data-dropoff-lng="<?= esc($dropoffLng) ?>"
                 tabindex="-1" 
                 aria-labelledby="bookingModalLabel<?= esc($booking['booking_id']) ?>" 
                 aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form action="<?= base_url('admin/update-booking-status') ?>" method="post" id="updateForm<?= esc($booking['booking_id']) ?>">
                    <?= csrf_field() ?>
                    <div class="modal-header">
                      <h5 class="modal-title" id="bookingModalLabel<?= esc($booking['booking_id']) ?>">Booking Details (ID: <?= esc($booking['booking_id']) ?>)</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <!-- Booking Info Section -->
                      <div class="p-3 rounded-3 shadow-sm bg-light mb-4">
                          <h6 class="fw-bold mb-3 text-primary">Booking Information</h6>
                          <div class="d-flex justify-content-between border-bottom pb-2">
                              <span class="fw-bold text-secondary">Name:</span>
                              <span class="text-muted"><?= esc($booking['name'] ?? '') ?></span>
                          </div>
                          <div class="d-flex justify-content-between border-bottom pb-2">
                              <span class="fw-bold text-secondary">Contact Number:</span>
                              <span class="text-muted"><?= esc($booking['contact_number'] ?? '') ?></span>
                          </div>
                          <div class="d-flex justify-content-between border-bottom pb-2">
                              <span class="fw-bold text-secondary">Pick-up Address:</span>
                              <span class="text-muted"><?= esc($booking['pick_up_address'] ?? '') ?></span>
                          </div>
                          <div class="d-flex justify-content-between border-bottom pb-2">
                              <span class="fw-bold text-secondary">Drop-off Address:</span>
                              <span class="text-muted"><?= esc($booking['drop_off_address'] ?? '') ?></span>
                          </div>
                          <div class="d-flex justify-content-between">
                              <span class="fw-bold text-secondary">Dispatch Date:</span>
                              <span class="text-muted"><?= esc($booking['dispatch_date'] ?? '') ?></span>
                          </div>
                      </div>

                      <!-- Cargo Details Section -->
                      <div class="p-3 rounded-3 shadow-sm bg-light mb-4">
                          <h6 class="fw-bold mb-3 text-primary">Cargo Details</h6>
                          <div class="d-flex justify-content-between border-bottom pb-2">
                              <span class="fw-bold text-secondary">Cargo Type:</span>
                              <span class="text-muted"><?= esc($booking['cargo_type'] ?? '') ?></span>
                          </div>
                          <div class="d-flex justify-content-between border-bottom pb-2">
                              <span class="fw-bold text-secondary">Cargo Weight:</span>
                              <span class="text-muted"><?= esc($booking['cargo_weight'] ?? '') ?></span>
                          </div>
                          <div class="d-flex justify-content-between">
                              <span class="fw-bold text-secondary">Delivery Note:</span>
                              <span class="text-muted"><?= esc($booking['delivery_note'] ?? '') ?></span>
                          </div>
                      </div>

                      <!-- Truck Assignment Section -->
                      <div class="p-3 rounded-3 shadow-sm bg-light mb-4">
                          <h6 class="fw-bold mb-3 text-primary">Truck Assignment</h6>
                          <div class="d-flex justify-content-between border-bottom pb-2">
                              <span class="fw-bold text-secondary">Truck Model:</span>
                              <span class="text-muted" id="truckModel<?= esc($booking['booking_id']) ?>">
                                  <?= esc($booking['truck_model'] ?? '') ?>
                              </span>
                          </div>
                          <div class="d-flex justify-content-between border-bottom pb-2">
                              <span class="fw-bold text-secondary">Current Driver:</span>
                              <span class="text-muted" id="currentDriver<?= esc($booking['booking_id']) ?>">
                                  <?= esc($booking['driver_name'] ?? '') ?>
                              </span>
                          </div>
                          <div class="d-flex justify-content-between border-bottom pb-2">
                              <span class="fw-bold text-secondary">Current Conductor:</span>
                              <span class="text-muted" id="currentConductor<?= esc($booking['booking_id']) ?>">
                                  <?= esc($booking['conductor_name'] ?? '') ?>
                              </span>
                          </div>
                          <div class="d-flex justify-content-between border-bottom pb-2">
                              <span class="fw-bold text-secondary">License Plate:</span>
                              <span class="text-muted"><?= esc($booking['license_plate'] ?? '') ?></span>
                          </div>
                          <div class="d-flex justify-content-between border-bottom pb-2">
                              <span class="fw-bold text-secondary">Type of Truck:</span>
                              <span class="text-muted"><?= esc($booking['type_of_truck'] ?? '') ?></span>
                          </div>
                          <div class="d-flex justify-content-between">
                              <span class="fw-bold text-secondary">Distance (km):</span>
                              <span class="text-muted"><?= esc($booking['distance'] ?? '') ?></span>
                          </div>
                      </div>

                      <!-- Update Status Section -->
                      <div class="p-3 rounded-3 shadow-sm bg-light mb-4">
                          <h6 class="fw-bold mb-3 text-primary">Update Status</h6>
                          <div class="mb-3">
                              <label for="status<?= esc($booking['booking_id']) ?>" class="form-label fw-bold">Booking Status:</label>
                              <select name="status" id="status<?= esc($booking['booking_id']) ?>" class="form-select">
                                  <option value="approved" <?= (isset($booking['status']) && $booking['status'] == 'approved') ? 'selected' : '' ?>>Approve</option>
                                  <option value="rejected" <?= (isset($booking['status']) && $booking['status'] == 'rejected') ? 'selected' : '' ?>>Rejected</option>
                                  <option value="pending" <?= (isset($booking['status']) && $booking['status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
                                  <option value="in-transit" <?= (isset($booking['status']) && $booking['status'] == 'in-transit') ? 'selected' : '' ?>>In-transit</option>
                                  <option value="complete" <?= (isset($booking['status']) && $booking['status'] == 'complete') ? 'selected' : '' ?>>Complete</option>
                              </select>
                          </div>
                      </div>

                      <!-- Map Section -->
                      <div class="p-3 rounded-3 shadow-sm bg-light">
                          <h6 class="fw-bold mb-3 text-primary">Location</h6>
                          <div id="map<?= esc($booking['booking_id']) ?>" style="width:100%; height:300px; border-radius: 8px;"></div>
                          <p class="mt-2"><strong>Pick-up Coordinates:</strong> <span id="pickupCoords<?= esc($booking['booking_id']) ?>"></span></p>
                          <p><strong>Drop-off Coordinates:</strong> <span id="dropoffCoords<?= esc($booking['booking_id']) ?>"></span></p>
                      </div>
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

<!-- Load your custom script if any -->
<script src="<?= base_url('assets/js/script.js') ?>"></script>

<!-- Load Google Maps JavaScript API -->
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAtBOU_Ez6dNsAsgVXTxbhl_IC09meVzlw"></script>

<script>
// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Attach an event listener to each modal with the class 'booking-modal'
    var bookingModals = document.querySelectorAll('.booking-modal');
    bookingModals.forEach(function(modal) {
        modal.addEventListener('shown.bs.modal', function() {
            var bookingId = modal.getAttribute('data-booking-id');
            initBookingMap(bookingId);
        });
    });
});

/**
 * Initialize the map for a given booking modal.
 * It displays pickup and dropoff markers, draws the driving route,
 * and fills the distance input field.
 *
 * @param {string} bookingId - The booking ID.
 */
function initBookingMap(bookingId) {
    var modal = document.getElementById('bookingModal' + bookingId);
    var pickupLat = parseFloat(modal.getAttribute('data-pickup-lat'));
    var pickupLng = parseFloat(modal.getAttribute('data-pickup-lng'));
    var dropoffLat = parseFloat(modal.getAttribute('data-dropoff-lat'));
    var dropoffLng = parseFloat(modal.getAttribute('data-dropoff-lng'));

    var pickup = new google.maps.LatLng(pickupLat, pickupLng);
    var dropoff = new google.maps.LatLng(dropoffLat, dropoffLng);

    // Display coordinates in the respective spans
    document.getElementById('pickupCoords' + bookingId).textContent = pickupLat.toFixed(6) + ', ' + pickupLng.toFixed(6);
    document.getElementById('dropoffCoords' + bookingId).textContent = dropoffLat.toFixed(6) + ', ' + dropoffLng.toFixed(6);

    // Initialize the map in the designated container
    var map = new google.maps.Map(document.getElementById('map' + bookingId), {
        center: pickup,
        zoom: 12
    });

    // Add markers for pick-up and drop-off locations
    var markerPickup = new google.maps.Marker({
        position: pickup,
        map: map,
        label: 'P'
    });
    var markerDropoff = new google.maps.Marker({
        position: dropoff,
        map: map,
        label: 'D'
    });

    // Set up the Directions service and renderer to show the driving route
    var directionsService = new google.maps.DirectionsService();
    var directionsRenderer = new google.maps.DirectionsRenderer();
    directionsRenderer.setMap(map);

    var request = {
        origin: pickup,
        destination: dropoff,
        travelMode: google.maps.TravelMode.DRIVING
    };

    directionsService.route(request, function(result, status) {
        if (status === google.maps.DirectionsStatus.OK) {
            directionsRenderer.setDirections(result);
            // Extract distance (in meters) from the route result and convert to km
            var distanceMeters = result.routes[0].legs[0].distance.value;
            var distanceKm = (distanceMeters / 1000).toFixed(2);
            // Update the distance field in the update form
            document.getElementById('distance' + bookingId).value = distanceKm;
        } else {
            console.error("Directions request failed due to " + status);
        }
    });
}
</script>

</body>
</html>
<?= $this->endSection() ?>

