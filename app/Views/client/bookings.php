<?= $this->extend('templates/client_layout.php') ?>

<?= $this->section('content') ?>
<!DOCTYPE html>
<html>
<head>
  <title>Bookings</title>
  <!-- Bootstrap CSS -->
  <link href="<?= base_url('public/assets/css/bootstrap.min.css'); ?>" rel="stylesheet">
  <!-- Custom CSS -->
  <link href="<?= base_url('public/assets/css/style.css'); ?>" rel="stylesheet">
  <style>
    /* Style for maps in modal and create booking form */
    .map-container {
      width: 100%;
      height: 300px;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <h1>Bookings</h1>

  <div class="container-fluid mt-4">
    <!-- Flash messages -->
    <?php if(session()->getFlashdata('success')): ?>
      <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if(session()->getFlashdata('error')): ?>
      <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <!-- Button to trigger create booking modal -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createBookingModal">
      Create Booking
    </button>

    <!-- Table of existing bookings -->
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
            <?php 
              // Skip invalid bookings
              if (!is_array($booking)) {
                continue;
              }
              // Ensure coordinates exist (or default to 0)
              $pickupLat  = $booking['pick_up_lat'] ?? '0';
              $pickupLng  = $booking['pick_up_lng'] ?? '0';
              $dropoffLat = $booking['drop_off_lat'] ?? '0';
              $dropoffLng = $booking['drop_off_lng'] ?? '0';
            ?>
            <tr>
              <td><?= esc($booking['booking_id']) ?></td>
              <td><?= esc($booking['booking_date']) ?></td>
              <td><?= esc($booking['dispatch_date']) ?></td>
              <td><?= esc($booking['cargo_type']) ?></td>
              <td><?= esc($booking['drop_off_address']) ?></td>
              <td><?= esc($booking['status']) ?></td>
              <td>
                <button class="btn btn-info btn-sm" 
                        data-bs-toggle="modal" 
                        data-bs-target="#bookingModal<?= esc($booking['booking_id']) ?>">
                  View
                </button>
              </td>
            </tr>

            <!-- Modal for viewing booking details -->
            <div class="modal fade" id="bookingModal<?= esc($booking['booking_id']) ?>" 
                 tabindex="-1" 
                 aria-labelledby="bookingModalLabel<?= esc($booking['booking_id']) ?>" 
                 aria-hidden="true"
                 data-pickup-lat="<?= esc($pickupLat) ?>"
                 data-pickup-lng="<?= esc($pickupLng) ?>"
                 data-dropoff-lat="<?= esc($dropoffLat) ?>"
                 data-dropoff-lng="<?= esc($dropoffLng) ?>">
              <div class="modal-dialog modal-lg">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="bookingModalLabel<?= esc($booking['booking_id']) ?>">
                      Booking Details (ID: <?= esc($booking['booking_id']) ?>)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <p><strong>Name:</strong> <?= esc($booking['name'] ?? '') ?></p>
                    <p><strong>Contact Number:</strong> <?= esc($booking['contact_number'] ?? '') ?></p>
                    <p><strong>Pick-up Address:</strong> <?= esc($booking['pick_up_address'] ?? '') ?></p>
                    <p><strong>Drop-off Address:</strong> <?= esc($booking['drop_off_address'] ?? '') ?></p>
                    <p><strong>Person of Contact:</strong> <?= esc($booking['person_of_contact'] ?? '') ?></p>
                    <p><strong>Dispatch Date:</strong> <?= esc($booking['dispatch_date'] ?? '') ?></p>
                    <p><strong>Cargo Type:</strong> <?= esc($booking['cargo_type'] ?? '') ?></p>
                    <p><strong>Cargo Weight:</strong> <?= esc($booking['cargo_weight'] ?? '') ?></p>
                    <p><strong>Delivery Note:</strong> <?= esc($booking['delivery_note'] ?? '') ?></p>
                    <p><strong>Truck Model:</strong> <?= esc($booking['truck_model'] ?? '') ?></p>
                    <p><strong>Conductor Name:</strong> <?= esc($booking['conductor_name'] ?? '') ?></p>
                    <p><strong>Driver Name:</strong> <?= esc($booking['driver_name'] ?? '') ?></p>
                    <p><strong>License Plate:</strong> <?= esc($booking['license_plate'] ?? '') ?></p>
                    <p><strong>Status:</strong> <?= esc($booking['status'] ?? '') ?></p>
                    
                    <!-- Map container -->
                    <div id="map<?= esc($booking['booking_id']) ?>" class="map-container"></div>
                    
                    <!-- Display coordinates and computed distance -->
                    <p><strong>Pick-up Coordinates:</strong> 
                      <span id="pickupCoords<?= esc($booking['booking_id']) ?>"></span>
                    </p>
                    <p><strong>Drop-off Coordinates:</strong> 
                      <span id="dropoffCoords<?= esc($booking['booking_id']) ?>"></span>
                    </p>
                    <p><strong>Driving Distance (km):</strong> 
                      <span id="distanceDisplay<?= esc($booking['booking_id']) ?>"></span>
                    </p>
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

  <!-- Create Booking Modal (unchanged) -->
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

            <!-- PICK-UP ADDRESS & MAP -->
            <div class="mb-3">
              <label for="pick_up_address" class="form-label">Pick-up Address *</label>
              <input type="text" name="pick_up_address" id="pick_up_address" class="form-control" placeholder="Type address here..." required>
              <!-- Hidden fields for coordinates -->
              <input type="hidden" name="pick_up_lat" id="pick_up_lat">
              <input type="hidden" name="pick_up_lng" id="pick_up_lng">
            </div>
            <div id="pickup_map" class="map-container"></div>

            <!-- DROP-OFF ADDRESS & MAP -->
            <div class="mb-3">
              <label for="drop_off_address" class="form-label">Drop-off Address *</label>
              <input type="text" name="drop_off_address" id="drop_off_address" class="form-control" placeholder="Type address here..." required>
              <!-- Hidden fields for coordinates -->
              <input type="hidden" name="drop_off_lat" id="drop_off_lat">
              <input type="hidden" name="drop_off_lng" id="drop_off_lng">
            </div>
            <div id="dropoff_map" class="map-container"></div>

            <!-- Distance Field (hidden) -->
            <div class="mb-3">
              <input type="hidden" name="distance" id="distance" readonly>
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

  <!-- Bootstrap JS + Popper -->
  <script src="<?= base_url('public/assets/js/bootstrap.bundle.min.js'); ?>"></script>

  <!-- Load Google Maps JavaScript API (basic, without Places) -->
  <script async src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAtBOU_Ez6dNsAsgVXTxbhl_IC09meVzlw&callback=initMap"></script>

  <script>
  // For the Create Booking modal, we initialize the maps immediately.
  let pickupMap, pickupMarker, dropoffMap, dropoffMarker;
  let pickupLongPressTimer, dropoffLongPressTimer;

  function initMap() {
    const defaultCenter = { lat: 14.5995, lng: 120.9842 };

    // Initialize map for Create Booking - Pickup
    pickupMap = new google.maps.Map(document.getElementById("pickup_map"), {
      center: defaultCenter,
      zoom: 12,
    });
    pickupMarker = new google.maps.Marker({
      map: pickupMap,
      position: defaultCenter,
      draggable: true,
      label: 'P'
    });
    google.maps.event.addListener(pickupMarker, 'dragend', function(evt) {
      document.getElementById('pick_up_lat').value = evt.latLng.lat().toFixed(6);
      document.getElementById('pick_up_lng').value = evt.latLng.lng().toFixed(6);
    });
    google.maps.event.addListener(pickupMap, 'click', function(e) {
      pickupMarker.setPosition(e.latLng);
      pickupMap.setCenter(e.latLng);
      document.getElementById('pick_up_lat').value = e.latLng.lat().toFixed(6);
      document.getElementById('pick_up_lng').value = e.latLng.lng().toFixed(6);
    });

    // Initialize map for Create Booking - Dropoff
    dropoffMap = new google.maps.Map(document.getElementById("dropoff_map"), {
      center: defaultCenter,
      zoom: 12,
    });
    dropoffMarker = new google.maps.Marker({
      map: dropoffMap,
      position: defaultCenter,
      draggable: true,
      label: 'D'
    });
    google.maps.event.addListener(dropoffMarker, 'dragend', function(evt) {
      document.getElementById('drop_off_lat').value = evt.latLng.lat().toFixed(6);
      document.getElementById('drop_off_lng').value = evt.latLng.lng().toFixed(6);
      computeDistance();
    });
    google.maps.event.addListener(dropoffMap, 'click', function(e) {
      dropoffMarker.setPosition(e.latLng);
      dropoffMap.setCenter(e.latLng);
      document.getElementById('drop_off_lat').value = e.latLng.lat().toFixed(6);
      document.getElementById('drop_off_lng').value = e.latLng.lng().toFixed(6);
      computeDistance();
    });
  }

  /**
   * Compute distance using the Directions Service.
   */
  function computeDistance() {
    var pickupLat = parseFloat(document.getElementById('pick_up_lat').value);
    var pickupLng = parseFloat(document.getElementById('pick_up_lng').value);
    var dropoffLat = parseFloat(document.getElementById('drop_off_lat').value);
    var dropoffLng = parseFloat(document.getElementById('drop_off_lng').value);

    if (isNaN(pickupLat) || isNaN(pickupLng) || isNaN(dropoffLat) || isNaN(dropoffLng)) {
      return;
    }

    var origin = new google.maps.LatLng(pickupLat, pickupLng);
    var destination = new google.maps.LatLng(dropoffLat, dropoffLng);

    if (!window.directionsService) {
      window.directionsService = new google.maps.DirectionsService();
    }
    var request = {
      origin: origin,
      destination: destination,
      travelMode: google.maps.TravelMode.DRIVING
    };

    window.directionsService.route(request, function(result, status) {
      if (status === google.maps.DirectionsStatus.OK) {
        var distanceMeters = result.routes[0].legs[0].distance.value;
        var distanceKm = (distanceMeters / 1000).toFixed(2);
        document.getElementById('distance').value = distanceKm;
      } else {
        console.error("Directions request failed: " + status);
      }
    });
  }

  // For each booking "View" modal, initialize its map when the modal is shown.
  document.addEventListener('DOMContentLoaded', function() {
    var modals = document.querySelectorAll('.modal.fade[id^="bookingModal"]');
    modals.forEach(function(modal) {
      modal.addEventListener('shown.bs.modal', function() {
        var bookingId = modal.getAttribute('id').replace('bookingModal', '');
        initBookingMap(bookingId);
      });
    });
  });

  function initBookingMap(bookingId) {
    var modal = document.getElementById("bookingModal" + bookingId);
    var pickupLat = parseFloat(modal.getAttribute("data-pickup-lat"));
    var pickupLng = parseFloat(modal.getAttribute("data-pickup-lng"));
    var dropoffLat = parseFloat(modal.getAttribute("data-dropoff-lat"));
    var dropoffLng = parseFloat(modal.getAttribute("data-dropoff-lng"));

    var pickup = new google.maps.LatLng(pickupLat, pickupLng);
    var dropoff = new google.maps.LatLng(dropoffLat, dropoffLng);

    // Display the coordinates in the modal
    document.getElementById("pickupCoords" + bookingId).textContent = pickupLat.toFixed(6) + ", " + pickupLng.toFixed(6);
    document.getElementById("dropoffCoords" + bookingId).textContent = dropoffLat.toFixed(6) + ", " + dropoffLng.toFixed(6);

    // Initialize map in the modal
    var map = new google.maps.Map(document.getElementById("map" + bookingId), {
      center: pickup,
      zoom: 12
    });

    // Create markers for pick-up and drop-off
    var markerP = new google.maps.Marker({
      position: pickup,
      map: map,
      label: 'P'
    });
    var markerD = new google.maps.Marker({
      position: dropoff,
      map: map,
      label: 'D'
    });

    // Use Directions Service to draw route and compute distance
    var directionsService = new google.maps.DirectionsService();
    var request = {
      origin: pickup,
      destination: dropoff,
      travelMode: google.maps.TravelMode.DRIVING
    };

    directionsService.route(request, function(result, status) {
      if (status === google.maps.DirectionsStatus.OK) {
        var distanceMeters = result.routes[0].legs[0].distance.value;
        var distanceKm = (distanceMeters / 1000).toFixed(2);
        document.getElementById("distanceDisplay" + bookingId).textContent = distanceKm;
        // Optionally, draw the route on the map:
        var directionsRenderer = new google.maps.DirectionsRenderer({
          suppressMarkers: true
        });
        directionsRenderer.setMap(map);
        directionsRenderer.setDirections(result);
      } else {
        console.error("Directions request failed: " + status);
      }
    });
  }
  </script>

</body>
</html>
<?= $this->endSection() ?>
