<?= $this->extend('templates/client_layout.php') ?>

<?= $this->section('content') ?>
<link href="<?= base_url('public/assets/css/style.css'); ?>" rel="stylesheet">
<title>Driver Geolocation</title>

<div class="container-fluid mt-4">
    <h1>Driver Geolocation</h1>
    
    <!-- Driver Cards -->
    <div class="row mt-4">
        <?php if (!empty($drivers)): ?>
            <?php foreach ($drivers as $driverId => $driver): ?>
                <?php
                    // Build the driver's full name
                    $fullName = trim(($driver['first_name'] ?? '') . ' ' . ($driver['last_name'] ?? ''));
                    $lastLat  = $driver['last_lat'] ?? '0';
                    $lastLng  = $driver['last_lng'] ?? '0';
                    
                    // Get booking details attached in the controller
                    $booking = $driver['booking'] ?? [];
                    $bookingId = $booking['booking_id'] ?? 'N/A';
                    $pickup    = $booking['pick_up_address'] ?? 'N/A';
                    $dropoff   = $booking['drop_off_address'] ?? 'N/A';
                    $bookDate  = $booking['booking_date'] ?? 'N/A';
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <!-- Use an animated delivery truck GIF as an indicator -->
                            <img src="<?= base_url('public/images/delivery-truck.gif') ?>" 
                                 alt="Delivery Truck" 
                                 style="width:40px; height:40px; margin-right:10px;">
                            <strong><?= esc($fullName) ?></strong>
                        </div>
                        <div class="card-body">
                            <p><strong>Driver ID:</strong> <?= esc($driverId) ?></p>
                            <p>
                                <strong>Last Coordinates:</strong><br>
                                Lat: <?= esc($lastLat) ?>, Lng: <?= esc($lastLng) ?>
                            </p>
                            <?php if (!empty($booking)): ?>
                            <p>
                                <strong>Booking Details:</strong><br>
                                <strong>ID:</strong> <?= esc($bookingId) ?><br>
                                <strong>Pickup:</strong> <?= esc($pickup) ?><br>
                                <strong>Drop-off:</strong> <?= esc($dropoff) ?><br>
                                <strong>Date:</strong> <?= esc($bookDate) ?>
                            </p>
                            <?php endif; ?>
                            <!-- View Map Button -->
                            <button class="btn btn-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#mapModal"
                                    data-driverid="<?= esc($driverId) ?>"
                                    data-lat="<?= esc($lastLat) ?>"
                                    data-lng="<?= esc($lastLng) ?>"
                                    data-name="<?= esc($fullName) ?>">
                                View Map
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No drivers found with location data.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Reusable Modal for Viewing Map -->
<div class="modal fade" id="mapModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="mapModalTitle">Driver Location</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Map container -->
        <div id="map" style="width: 100%; height: 500px;"></div>
      </div>
    </div>
  </div>
</div>

<!-- Load the Google Maps JavaScript API (replace YOUR_API_KEY with your key) -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAtBOU_Ez6dNsAsgVXTxbhl_IC09meVzlw"></script>
<script>
  let map;
  let marker;

  // Initialize the map at the given latitude/longitude, with a custom truck icon
  function initMap(lat, lng, driverName) {
    const location = { lat: parseFloat(lat), lng: parseFloat(lng) };
    map = new google.maps.Map(document.getElementById("map"), {
      center: location,
      zoom: 15,
    });
    marker = new google.maps.Marker({
      position: location,
      map: map,
      title: driverName,
      icon: {
        url: "<?= base_url('public/images/icons/truck.png') ?>",
        scaledSize: new google.maps.Size(40, 40),
      }
    });
  }

  // When the modal is about to be shown, extract data and initialize the map
  const mapModal = document.getElementById('mapModal');
  mapModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const driverId = button.getAttribute('data-driverid');
    const lat = button.getAttribute('data-lat');
    const lng = button.getAttribute('data-lng');
    const name = button.getAttribute('data-name');

    const modalTitle = mapModal.querySelector('.modal-title');
    modalTitle.textContent = 'Location for ' + name + ' (' + driverId + ')';

    // Delay map initialization to ensure modal is rendered
    setTimeout(() => {
      initMap(lat, lng, name);
    }, 300);
  });
</script>

<?= $this->endSection() ?>
