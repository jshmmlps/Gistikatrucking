<?= $this->extend('templates/operations_coordinator_layout.php') ?>

<?= $this->section('content') ?>
<link href="<?= base_url('public/assets/css/style.css'); ?>" rel="stylesheet">
<title>Bookings</title>

<h1>Bookings</h1>

<div class="container-fluid mt-4">
    <!-- Flash messages -->
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
                if (!is_array($booking)) {
                    continue;
                }
                $pickupLat  = $booking['pick_up_lat']  ?? '0';
                $pickupLng  = $booking['pick_up_lng']  ?? '0';
                $dropoffLat = $booking['drop_off_lat'] ?? '0';
                $dropoffLng = $booking['drop_off_lng'] ?? '0';
                $bId        = $booking['booking_id']    ?? 'N/A';
            ?>
            <tr>
              <td><?= esc($bId) ?></td>
              <td><?= esc($booking['booking_date'] ?? '') ?></td>
              <td><?= esc($booking['name'] ?? '') ?></td>
              <td><?= esc($booking['dispatch_date'] ?? '') ?></td>
              <td><?= esc($booking['cargo_type'] ?? '') ?></td>
              <td><?= esc($booking['status'] ?? '') ?></td>
              <td>
                <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#bookingModal<?= esc($bId) ?>">
                  View/Update
                </button>
              </td>
            </tr>

            <!-- Modal -->
            <div class="modal fade booking-modal" 
                 id="bookingModal<?= esc($bId) ?>"
                 data-booking-id="<?= esc($bId) ?>"
                 data-pickup-lat="<?= esc($pickupLat) ?>"
                 data-pickup-lng="<?= esc($pickupLng) ?>"
                 data-dropoff-lat="<?= esc($dropoffLat) ?>"
                 data-dropoff-lng="<?= esc($dropoffLng) ?>"
                 tabindex="-1"
                 aria-labelledby="bookingModalLabel<?= esc($bId) ?>"
                 aria-hidden="true">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <form action="<?= base_url('operations/update-booking-status') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="modal-header">
                      <h5 class="modal-title w-100 text-center" id="bookingModalLabel<?= esc($bId) ?>">
                        Booking Details (ID: <?= esc($bId) ?>)
                      </h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                      <!-- Basic booking info -->
                      <div class="p-3 mb-3 rounded-3 shadow-sm border bg-light">
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

                      <!-- Cargo details -->
                      <div class="p-3 mb-3 rounded-3 shadow-sm border bg-light">
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

                      <!-- Truck & driver info -->
                      <div class="p-3 mb-3 rounded-3 shadow-sm border bg-light">
                        <h6 class="fw-bold mb-3 text-primary">Truck/Driver Information</h6>
                        <div class="d-flex justify-content-between mb-2">
                          <span class="fw-bold text-secondary">Truck Model:</span>
                          <span><?= esc($booking['truck_model'] ?? 'N/A') ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                          <span class="fw-bold text-secondary">Current Driver:</span>
                          <span><?= esc($booking['driver_name'] ?? 'N/A') ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                          <span class="fw-bold text-secondary">Current Conductor:</span>
                          <span><?= esc($booking['conductor_name'] ?? 'N/A') ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                          <span class="fw-bold text-secondary">License Plate:</span>
                          <span><?= esc($booking['license_plate'] ?? 'N/A') ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                          <span class="fw-bold text-secondary">Distance (km):</span>
                          <span><?= esc($booking['distance'] ?? 'N/A') ?></span>
                        </div>
                      </div>

                      <!-- Hidden fields -->
                      <input type="hidden" name="booking_id" value="<?= esc($bId) ?>">
                      <input type="hidden" name="truck_id" value="<?= esc($booking['truck_id'] ?? '') ?>">
                      <input type="hidden" name="distance" id="distance<?= esc($bId) ?>" value="<?= esc($booking['distance'] ?? '') ?>">

                      <!-- Update status -->
                      <div class="p-3 mb-3 rounded-3 shadow-sm bg-light">
                        <h6 class="fw-bold mb-3 text-primary">Update Status</h6>
                        <div class="mb-3">
                          <label class="form-label fw-bold">Booking Status:</label>
                          <select name="status" class="form-select">
                            <option value="approved"   <?= (isset($booking['status']) && $booking['status']=='approved')   ? 'selected' : '' ?>>Approve</option>
                            <option value="rejected"   <?= (isset($booking['status']) && $booking['status']=='rejected')   ? 'selected' : '' ?>>Reject</option>
                            <option value="pending"    <?= (isset($booking['status']) && $booking['status']=='pending')    ? 'selected' : '' ?>>Pending</option>
                            <option value="in-transit" <?= (isset($booking['status']) && $booking['status']=='in-transit') ? 'selected' : '' ?>>In-transit</option>
                            <option value="completed"  <?= (isset($booking['status']) && $booking['status']=='completed')  ? 'selected' : '' ?>>Complete</option>
                          </select>
                        </div>
                      </div>

                      <!-- Reassign driver/conductor if needed -->
                      <div class="p-3 mb-3 rounded-3 shadow-sm bg-light">
                        <h6 class="fw-bold mb-3 text-primary">Reassign Driver / Conductor</h6>

                        <!-- Drivers dropdown -->
                        <div class="mb-3">
                          <label class="form-label fw-bold">Select Driver (Optional):</label>
                          <select name="driver" class="form-select">
                            <option value="">-- No Change --</option>
                            <?php if (!empty($driversList)): ?>
                              <?php foreach ($driversList as $dKey => $dInfo): ?>
                                <?php 
                                  $dName = trim(($dInfo['first_name'] ?? '').' '.($dInfo['last_name'] ?? ''));
                                ?>
                                <option value="<?= esc($dKey) ?>">
                                  <?= esc($dName) ?> (<?= esc($dKey) ?>)
                                </option>
                              <?php endforeach; ?>
                            <?php endif; ?>
                          </select>
                        </div>

                        <!-- Conductors dropdown -->
                        <div class="mb-3">
                          <label class="form-label fw-bold">Select Conductor (Optional):</label>
                          <select name="conductor" class="form-select">
                            <option value="">-- No Change --</option>
                            <?php if (!empty($conductorsList)): ?>
                              <?php foreach ($conductorsList as $cKey => $cInfo): ?>
                                <?php 
                                  $cName = trim(($cInfo['first_name'] ?? '').' '.($cInfo['last_name'] ?? ''));
                                ?>
                                <option value="<?= esc($cKey) ?>">
                                  <?= esc($cName) ?> (<?= esc($cKey) ?>)
                                </option>
                              <?php endforeach; ?>
                            <?php endif; ?>
                          </select>
                        </div>
                      </div>

                      <!-- Map Container -->
                      <div id="map<?= esc($bId) ?>" style="width: 100%; height: 300px;" class="border mb-3"></div>

                      <!-- Coordinates display -->
                      <div class="mb-2">
                        <span class="fw-bold text-secondary">Pick-up Coordinates:</span>
                        <span id="pickupCoords<?= esc($bId) ?>"></span>
                      </div>
                      <div>
                        <span class="fw-bold text-secondary">Drop-off Coordinates:</span>
                        <span id="dropoffCoords<?= esc($bId) ?>"></span>
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

<!-- Load Google Maps API -->
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAtBOU_Ez6dNsAsgVXTxbhl_IC09meVzlw"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // For each modal, initialize the map on shown
  var bookingModals = document.querySelectorAll('.booking-modal');
  bookingModals.forEach(function(modal) {
    modal.addEventListener('shown.bs.modal', function() {
      var bId = modal.getAttribute('data-booking-id');
      initBookingMap(bId);
    });
  });
});

/**
 * Initialize the map for a given booking modal.
 * Renders route from pickup to dropoff, stores distance in hidden field.
 */
function initBookingMap(bookingId) {
  var modal = document.getElementById('bookingModal' + bookingId);
  var pickupLat = parseFloat(modal.getAttribute('data-pickup-lat'));
  var pickupLng = parseFloat(modal.getAttribute('data-pickup-lng'));
  var dropoffLat = parseFloat(modal.getAttribute('data-dropoff-lat'));
  var dropoffLng = parseFloat(modal.getAttribute('data-dropoff-lng'));

  var pickup  = new google.maps.LatLng(pickupLat, pickupLng);
  var dropoff = new google.maps.LatLng(dropoffLat, dropoffLng);

  document.getElementById('pickupCoords' + bookingId).textContent  = pickupLat.toFixed(6) + ', ' + pickupLng.toFixed(6);
  document.getElementById('dropoffCoords' + bookingId).textContent = dropoffLat.toFixed(6) + ', ' + dropoffLng.toFixed(6);

  var map = new google.maps.Map(document.getElementById('map' + bookingId), {
    center: pickup,
    zoom: 12
  });

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
      var distanceMeters = result.routes[0].legs[0].distance.value;
      var distanceKm     = (distanceMeters / 1000).toFixed(2);
      // Update hidden distance field
      var distanceField = document.getElementById('distance' + bookingId);
      if (distanceField) {
        distanceField.value = distanceKm;
      }
    } else {
      console.error("Directions request failed due to " + status);
    }
  });
}
</script>
<?= $this->endSection() ?>
