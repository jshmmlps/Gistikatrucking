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
    .role-badge {
    display: inline-block;
    padding: 0.35em 0.65em;
    font-size: 0.75em;
    font-weight: 700;
    line-height: 1;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 0.375rem;
}

/* Custom badge colors */
    .badge-pending {
    background-color: #ff6d01 !important;
    color: white;
}
    .badge-approved {
    background-color: #674ea7 !important;
    color: white;
}
    .badge-intransit {
    background-color: #4285f4 !important;
    color: white;
}
    .badge-rejected {
    background-color: #ea4335 !important;
    color: white;
}
    .badge-completed {
    background-color: #34a853 !important;
    color: white;
}
    /* Style for maps in modal and create booking form */
    .map-container {
      width: 100%;
      height: 300px;
      margin-bottom: 20px;
    }

    .pac-container {
      z-index: 2000 !important; /* or higher than the modal’s z-index */
    }

    .position-relative {
      position: relative;
      display: inline-block;
    }

    .position-absolute {
      position: absolute;
    }

    .translate-middle {
      transform: translate(-50%, -50%);
    }

    .top-0 {
      top: 0;
    }

    .start-100 {
      left: 100%;
    }

    .remarks-section {
      border-left: 3px solid #0d6efd;
    }

  </style>
</head>
<body>
  <h1>Bookings</h1>

  <div class="container-fluid mt-4">
    <!-- Show an alert based on driver availability -->
    <?php if ($driverAvailability === null): ?>
        <div class="alert alert-danger">
            No drivers available. Please contact support.
        </div>
    <?php elseif ($driverAvailability === false): ?>
        <div class="alert alert-warning">
            All drivers are currently busy. You can book but please wait for approval and check regularly for booking updates.
        </div>
    <?php else: ?>
        <div class="alert alert-success">
            Good news! A driver is currently available for new bookings.
        </div>
    <?php endif; ?>

    <!-- Show available driver and conductors - for checking -->
    <!-- <?php if (empty($availableDrivers)): ?>
        <div class="alert alert-warning">
            No drivers are currently available. Please check back later.
        </div>
    <?php else: ?>
        <div class="alert alert-success">
            <strong>Available Drivers (<?= count($availableDrivers) ?>)</strong>
            <ul class="mt-2">
                <?php foreach ($availableDrivers as $driver): ?>
                    <li>
                        <?= esc($driver['first_name'] . ' ' . $driver['last_name']) ?> 
                        (Assigned to Truck: <?= esc($driver['truck_assigned']) ?>)
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?> -->

    <!-- Existing flash messages (errors, success) -->
    <?php if(session()->getFlashdata('error')): ?>
      <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <?php if(session()->getFlashdata('success')): ?>
      <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <!-- Search and Filter Controls -->
    <div class="row mb-3">
      <!-- Search by Booking ID -->
      <div class="col-md-6 mb-2 mb-md-0">
        <input type="text" class="form-control" id="searchBookingId" placeholder="Search by Booking ID">
      </div>
      <!-- Filter by Status -->
      <div class="col-md-6">
        <select class="form-select" id="filterStatus">
          <option value="">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
          <option value="in-transit">In-transit</option>
          <option value="completed">Completed</option>
        </select>
      </div>
    </div>

    <!-- Button to trigger create booking modal -->
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#createBookingModal">
      Create Booking
    </button>

    <!-- Table of existing bookings -->
    <table id="bookingsTable" class="table table-bordered">
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
              <td>
                <span class="role-badge" data-role="<?= esc($booking['status'] ?? '') ?>">
                <?= esc($booking['status'] ?? '') ?>
              </td>
              <td>
                <div class="position-relative d-inline-block">
                  <button class="btn btn-info btn-sm view-booking-btn" 
                          data-bs-toggle="modal" 
                          data-bs-target="#bookingModal<?= esc($booking['booking_id']) ?>"
                          data-booking-id="<?= esc($booking['booking_id']) ?>">
                    View
                  </button>
                  <?php if (!empty($booking['remarks'])): ?>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle remark-notification" 
                          id="notification-<?= esc($booking['booking_id']) ?>">
                      <span class="visually-hidden">New remarks</span>
                    </span>
                  <?php endif; ?>
                </div>
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

                    <!-- Add this section for remarks -->
                    <?php if (!empty($booking['remarks'])): ?>
                      <div class="remarks-section mt-3 p-3 bg-light rounded">
                        <h5>Remarks Updates</h5>
                        <p><?= nl2br(esc($booking['remarks'])) ?></p>
                      </div>
                    <?php endif; ?>
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
              <input
                type="text"
                name="contact_number"
                id="contact_number"
                class="form-control"
                placeholder="Enter up 7 to 11 digit number only"
                pattern="\d{5,11}"
                maxlength="11"
                required
              >
              <div class="invalid-feedback">
                Please enter a valid contact number (7 to 11 digits only).
              </div>
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
              <input 
                type="text" 
                name="pick_up_address" 
                id="pick_up_address" 
                class="form-control" 
                placeholder="Type address or move pin..." 
                required
              >
              <!-- Hidden fields for coordinates -->
              <input type="hidden" name="pick_up_lat" id="pick_up_lat">
              <input type="hidden" name="pick_up_lng" id="pick_up_lng">
            </div>
            <div id="pickup_map" class="map-container"></div>

            <!-- DROP-OFF ADDRESS & MAP -->
            <div class="mb-3">
              <label for="drop_off_address" class="form-label">Drop-off Address *</label>
              <input 
                type="text" 
                name="drop_off_address" 
                id="drop_off_address" 
                class="form-control" 
                placeholder="Type address or move pin..." 
                required
              >
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
              <label for="cargo_weight" class="form-label">Cargo Weight (kg) *</label>
              <select name="cargo_weight" id="cargo_weight" class="form-control" required>
                <option value="">Select Weight Range</option>
                <option value="0-500">0 - 500 kg (Small parcel)</option>
                <option value="500-1000">500 - 1,000 kg (Light pallets)</option>
                <option value="1000-5000">1,000 - 5,000 kg (Partial load)</option>
                <option value="5000-10000">5,000 - 10,000 kg (Medium truckload)</option>
                <option value="10000-20000">10,000 - 20,000 kg (Standard truckload)</option>
                <option value="20000+">20,000+ kg (Heavy truckload)</option>
              </select>
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

  <!-- 
    Load Google Maps JavaScript API with the 'places' library for Autocomplete.
    Note: Replace YOUR_API_KEY_HERE with your actual key.
  -->
  <script async
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAtBOU_Ez6dNsAsgVXTxbhl_IC09meVzlw&libraries=places&callback=initMap">
  </script>

  <script>

    document.addEventListener('DOMContentLoaded', function() {
      // Handle click on any view button
      document.querySelectorAll('.view-booking-btn').forEach(button => {
        button.addEventListener('click', function() {
          const bookingId = this.getAttribute('data-booking-id');
          const notificationDot = document.getElementById(`notification-${bookingId}`);
          
          // If there's a notification dot for this booking, remove it
          if (notificationDot) {
            notificationDot.remove();
            
            // Optional: Send AJAX request to mark remarks as seen on server
            markRemarksAsSeen(bookingId);
          }
        });
      });
      
      // Optional function to update server that remarks were seen
      function markRemarksAsSeen(bookingId) {
        fetch('/client/markRemarksSeen', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({ booking_id: bookingId })
        })
        .then(response => response.json())
        .then(data => {
          console.log('Remarks marked as seen', data);
        })
        .catch(error => {
          console.error('Error:', error);
        });
      }
    });
    
  let pickupMap, pickupMarker, dropoffMap, dropoffMarker;
  let pickupAutocomplete, dropoffAutocomplete;
  let directionsService;

  function initMap() {
    const defaultCenter = { lat: 14.5995, lng: 120.9842 };

    // Initialize service(s)
    directionsService = new google.maps.DirectionsService();

    // -------- PICKUP MAP --------
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

    // When user drags the pickup marker manually
    google.maps.event.addListener(pickupMarker, 'dragend', function(evt) {
      document.getElementById('pick_up_lat').value = evt.latLng.lat().toFixed(6);
      document.getElementById('pick_up_lng').value = evt.latLng.lng().toFixed(6);

      // Update placeholder to indicate manual selection
      document.getElementById('pick_up_address').placeholder = "Manually type the place...";
    });

    // When user clicks on the pickup map
    google.maps.event.addListener(pickupMap, 'click', function(e) {
      pickupMarker.setPosition(e.latLng);
      pickupMap.setCenter(e.latLng);
      document.getElementById('pick_up_lat').value = e.latLng.lat().toFixed(6);
      document.getElementById('pick_up_lng').value = e.latLng.lng().toFixed(6);

      // Update placeholder to indicate manual selection
      document.getElementById('pick_up_address').placeholder = "Manually type the place...";
    });

    // -------- DROPOFF MAP --------
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

    // When user drags the dropoff marker manually
    google.maps.event.addListener(dropoffMarker, 'dragend', function(evt) {
      document.getElementById('drop_off_lat').value = evt.latLng.lat().toFixed(6);
      document.getElementById('drop_off_lng').value = evt.latLng.lng().toFixed(6);

      // Update placeholder to indicate manual selection
      document.getElementById('drop_off_address').placeholder = "Manually type the place...";
      computeDistance();
    });

    // When user clicks on the dropoff map
    google.maps.event.addListener(dropoffMap, 'click', function(e) {
      dropoffMarker.setPosition(e.latLng);
      dropoffMap.setCenter(e.latLng);
      document.getElementById('drop_off_lat').value = e.latLng.lat().toFixed(6);
      document.getElementById('drop_off_lng').value = e.latLng.lng().toFixed(6);

      // Update placeholder to indicate manual selection
      document.getElementById('drop_off_address').placeholder = "Manually type the place...";
      computeDistance();
    });

    // -------- AUTOCOMPLETE (PICKUP) --------
    let pickupInput = document.getElementById('pick_up_address');
    pickupAutocomplete = new google.maps.places.Autocomplete(pickupInput, {
      fields: ["formatted_address", "geometry"],
      // You can narrow down to specific countries, or types, etc.:
      componentRestrictions: { country: "ph" },
      types: ["geocode"] // or "address", etc.
    });

    pickupAutocomplete.addListener("place_changed", function() {
      const place = pickupAutocomplete.getPlace();
      if (!place.geometry || !place.geometry.location) {
        return;
      }
      // Center and move the marker
      pickupMap.setCenter(place.geometry.location);
      pickupMarker.setPosition(place.geometry.location);
      document.getElementById('pick_up_lat').value = place.geometry.location.lat().toFixed(6);
      document.getElementById('pick_up_lng').value = place.geometry.location.lng().toFixed(6);

      // Update placeholder back to normal since user used Autocomplete
      pickupInput.placeholder = "Type address here...";
    });

    // -------- AUTOCOMPLETE (DROPOFF) --------
    let dropoffInput = document.getElementById('drop_off_address');
    dropoffAutocomplete = new google.maps.places.Autocomplete(dropoffInput, {
      fields: ["formatted_address", "geometry"],
      componentRestrictions: { country: "ph" },
      types: ["geocode"] // or "address", et
    });

    dropoffAutocomplete.addListener("place_changed", function() {
      const place = dropoffAutocomplete.getPlace();
      if (!place.geometry || !place.geometry.location) {
        return;
      }
      // Center and move the marker
      dropoffMap.setCenter(place.geometry.location);
      dropoffMarker.setPosition(place.geometry.location);
      document.getElementById('drop_off_lat').value = place.geometry.location.lat().toFixed(6);
      document.getElementById('drop_off_lng').value = place.geometry.location.lng().toFixed(6);

      // Update placeholder back to normal
      dropoffInput.placeholder = "Type address here...";
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

    var request = {
      origin: origin,
      destination: destination,
      travelMode: google.maps.TravelMode.DRIVING
    };

    directionsService.route(request, function(result, status) {
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
    var ds = new google.maps.DirectionsService();
    var request = {
      origin: pickup,
      destination: dropoff,
      travelMode: google.maps.TravelMode.DRIVING
    };

    ds.route(request, function(result, status) {
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

  // Search and filter functionality for the bookings table
  function filterBookings() {
    const searchBookingId = document.getElementById('searchBookingId').value.toLowerCase().trim();
    const filterStatus = document.getElementById('filterStatus').value.toLowerCase().trim();
    const rows = document.querySelectorAll('#bookingsTable tbody tr');
    
    rows.forEach(function(row) {
      const bookingId = row.cells[0].textContent.toLowerCase().trim();
      const status = row.cells[5].textContent.toLowerCase().trim();
      
      const matchesBookingId = bookingId.indexOf(searchBookingId) > -1;
      const matchesStatus = filterStatus ? (status === filterStatus) : true;
      
      row.style.display = (matchesBookingId && matchesStatus) ? '' : 'none';
    });
  }

  // Add event listeners for search and filter
  document.getElementById('searchBookingId').addEventListener('keyup', filterBookings);
  document.getElementById('filterStatus').addEventListener('change', filterBookings);

  document.addEventListener('DOMContentLoaded', () => {
    const badges = document.querySelectorAll('.role-badge');

    badges.forEach(badge => {
        const role = badge.getAttribute('data-role');

        badge.classList.remove('badge-pending', 'badge-approved', 'badge-intransit', 'badge-rejected', 'badge-completed');

        switch (role) {
            case 'pending':
                badge.classList.add('badge-pending');
                break;
            case 'approved':
                badge.classList.add('badge-approved');
                break;
            case 'in-transit':
                badge.classList.add('badge-intransit');
                break;
            case 'rejected':
                badge.classList.add('badge-rejected');
                break;
            case 'completed':
                badge.classList.add('badge-completed');
                break;
            default:
                break;
        }
    });
});
  </script>

</body>
</html>
<?= $this->endSection() ?>
