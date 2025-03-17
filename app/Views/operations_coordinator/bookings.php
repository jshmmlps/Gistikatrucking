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
              <td><?= esc($booking['booking_id'] ?? 'N/A') ?></td>
              <td><?= esc($booking['booking_date'] ?? 'N/A') ?></td>
              <td><?= esc($booking['name'] ?? 'N/A') ?></td>
              <td><?= esc($booking['dispatch_date'] ?? 'N/A') ?></td>
              <td><?= esc($booking['cargo_type'] ?? 'N/A') ?></td>
              <td><?= esc($booking['status'] ?? 'N/A') ?></td>
              <td>
                <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#bookingModal<?= esc($booking['booking_id'] ?? 'N/A') ?>">View/Update</button>
              </td>
            </tr>
            <!-- Modal for viewing and updating booking -->
            <div class="modal fade booking-modal" 
                 id="bookingModal<?= esc($booking['booking_id'] ?? 'N/A') ?>" 
                 data-booking-id="<?= esc($booking['booking_id'] ?? 'N/A') ?>"
                 data-pickup-lat="<?= esc($pickupLat) ?>"
                 data-pickup-lng="<?= esc($pickupLng) ?>"
                 data-dropoff-lat="<?= esc($dropoffLat) ?>"
                 data-dropoff-lng="<?= esc($dropoffLng) ?>"
                 tabindex="-1" 
                 aria-labelledby="bookingModalLabel<?= esc($booking['booking_id'] ?? 'N/A') ?>" 
                 aria-hidden="true">
              <div class="modal-dialog modal-md">
                <div class="modal-content">
                  <form action="<?= base_url('admin/update-booking-status') ?>" method="post" id="updateForm<?= esc($booking['booking_id'] ?? 'N/A') ?>">
                    <?= csrf_field() ?>
                    <div class="modal-header">
                      <h5 class="modal-title text-center w-100" id="bookingModalLabel<?= esc($booking['booking_id'] ?? 'N/A') ?>">Booking Details (ID: <?= esc($booking['booking_id'] ?? 'N/A') ?>)</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <!-- Booking Info Section -->
                        <div class="p-3 mb-4 rounded-3 shadow-sm border bg-light">
                            <h6 class="fw-bold mb-3 text-primary">Booking Information</h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold text-secondary">Name:</span>
                                <span><?= esc($booking['name'] ?? 'N/A') ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold text-secondary">Contact Number:</span>
                                <span><?= esc($booking['contact_number'] ?? 'N/A') ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold text-secondary">Pick-up Address:</span>
                                <span><?= esc($booking['pick_up_address'] ?? 'N/A') ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold text-secondary">Drop-off Address:</span>
                                <span><?= esc($booking['drop_off_address'] ?? 'N/A') ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold text-secondary">Dispatch Date:</span>
                                <span><?= esc($booking['dispatch_date'] ?? 'N/A') ?></span>
                            </div>
                        </div>

                        <!-- Cargo Info Section -->
                        <div class="p-3 mb-4 rounded-3 shadow-sm border bg-light">
                            <h6 class="fw-bold mb-3 text-primary">Cargo Details</h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold text-secondary">Cargo Type:</span>
                                <span><?= esc($booking['cargo_type'] ?? 'N/A') ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold text-secondary">Cargo Weight:</span>
                                <span><?= esc($booking['cargo_weight'] ?? 'N/A') ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold text-secondary">Delivery Note:</span>
                                <span><?= esc($booking['delivery_note'] ?? 'N/A') ?></span>
                            </div>
                        </div>

                        <!-- Truck Info Section -->
                        <div class="p-3 mb-4 rounded-3 shadow-sm border bg-light">
                            <h6 class="fw-bold mb-3 text-primary">Truck Information</h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold text-secondary">Truck Model:</span>
                                <span id="truckModel<?= esc($booking['booking_id'] ?? 'N/A') ?>"><?= esc($booking['truck_model'] ?? 'N/A') ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold text-secondary">Current Driver:</span>
                                <span id="currentDriver<?= esc($booking['booking_id'] ?? 'N/A') ?>"><?= esc($booking['driver_name'] ?? 'N/A') ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold text-secondary">Current Conductor:</span>
                                <span id="currentConductor<?= esc($booking['booking_id'] ?? 'N/A') ?>"><?= esc($booking['conductor_name'] ?? 'N/A') ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold text-secondary">License Plate:</span>
                                <span><?= esc($booking['license_plate'] ?? 'N/A') ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold text-secondary">Type of Truck:</span>
                                <span><?= esc($booking['type_of_truck'] ?? 'N/A') ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold text-secondary">Distance (km):</span>
                                <span><?= esc($booking['distance'] ?? 'N/A') ?></span>
                            </div>
                        </div>

                        <!-- Hidden Fields -->
                        <input type="hidden" name="truck_id" id="truck_id<?= esc($booking['booking_id'] ?? 'N/A') ?>" value="<?= esc($booking['truck_id'] ?? 'N/A') ?>">
                        <!-- Added hidden input for distance -->
                        <input type="hidden" name="distance" id="distance<?= esc($booking['booking_id'] ?? 'N/A') ?>" value="<?= esc($booking['distance'] ?? 'N/A') ?>">

                        <!-- Update Status Dropdown -->
                        <div class="p-3 rounded-3 shadow-sm bg-light mb-4">
                          <h6 class="fw-bold mb-3 text-primary">Update Status</h6>
                          <div class="mb-3">
                              <label for="status<?= esc($booking['booking_id'] ?? 'N/A') ?>" class="form-label fw-bold">Booking Status:</label>
                              <select name="status" id="status<?= esc($booking['booking_id'] ?? 'N/A') ?>" class="form-select">
                                  <option value="approved" <?= (isset($booking['status']) && $booking['status'] == 'approved') ? 'selected' : 'N/A' ?>>Approve</option>
                                  <option value="rejected" <?= (isset($booking['status']) && $booking['status'] == 'rejected') ? 'selected' : 'N/A' ?>>Rejected</option>
                                  <option value="pending" <?= (isset($booking['status']) && $booking['status'] == 'pending') ? 'selected' : 'N/A' ?>>Pending</option>
                                  <option value="in-transit" <?= (isset($booking['status']) && $booking['status'] == 'in-transit') ? 'selected' : 'N/A' ?>>In-transit</option>
                                  <option value="complete" <?= (isset($booking['status']) && $booking['status'] == 'complete') ? 'selected' : 'N/A' ?>>Complete</option>
                              </select>
                          </div>
                      </div>

                        <input type="hidden" name="booking_id" value="<?= esc($booking['booking_id'] ?? 'N/A') ?>">

                        <!-- Map Container -->
                        <div id="map<?= esc($booking['booking_id'] ?? 'N/A') ?>" class="rounded-3 shadow-sm border" style="width: 100%; height: 300px; margin-bottom: 20px;"></div>

                        <!-- Coordinates -->
                        <div class="mb-2">
                            <span class="fw-bold text-secondary">Pick-up Coordinates:</span>
                            <span id="pickupCoords<?= esc($booking['booking_id'] ?? 'N/A') ?>"></span>
                        </div>
                        <div>
                            <span class="fw-bold text-secondary">Drop-off Coordinates:</span>
                            <span id="dropoffCoords<?= esc($booking['booking_id'] ?? 'N/A') ?>"></span>
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
    new google.maps.Marker({
        position: pickup,
        map: map,
        label: 'P'
    });
    new google.maps.Marker({
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
            // Update the hidden distance field in the update form
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
