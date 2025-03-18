<?= $this->extend('templates/resource_manager_layout.php') ?>

<?= $this->section('content') ?>

<link href="<?= base_url('public/assets/css/style.css'); ?>" rel="stylesheet">
<title>Driver Geolocation</title>

<div class="container-fluid mt-4">
    <h1>Driver Geolocation</h1>

    <!-- Optional Navigation Tabs -->
    <ul class="nav nav-tabs" id="managementTabs" role="tablist">
        <a href="<?= base_url('resource/trucks'); ?>" 
           class="nav-link <?= (current_url() == base_url('resource/trucks')) ? 'active' : '' ?>">
            Truck Records
        </a>
        <a href="<?= base_url('resource/geolocation'); ?>" 
           class="nav-link <?= (current_url() == base_url('resource/geolocation')) ? 'active' : '' ?>">
            Geolocation
        </a>
        <a href="<?= base_url('resource/maintenance'); ?>" 
           class="nav-link <?= (current_url() == base_url('resource/maintenance')) ? 'active' : '' ?>">
            Maintenance Analytics
        </a>
    </ul>

    <div class="row mt-4">
        <?php if (!empty($drivers)): ?>
            <?php foreach ($drivers as $driverId => $driverData): ?>
                <?php
                    $fullName = trim(($driverData['first_name'] ?? '') . ' ' . ($driverData['last_name'] ?? ''));
                    $lastLat = $driverData['last_lat'] ?? '0';
                    $lastLng = $driverData['last_lng'] ?? '0';
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <!-- Insert the image -->
                            <img src="<?= base_url('public/images/icons/truck.png') ?>" 
                                 alt="Delivery Truck" 
                                 style="width:40px; height:40px; margin-right:10px;">
                            <strong><?= esc($fullName) ?></strong>
                        </div>
                        <div class="card-body">
                            <p><strong>Driver ID:</strong> <?= esc($driverId) ?></p>
                            <p>
                                <strong>Last Location:</strong><br>
                                Lat: <?= esc($lastLat) ?>, Lng: <?= esc($lastLng) ?>
                            </p>
                            <!-- View Map Button triggers modal -->
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

<!-- Modal (Reusable for all drivers) -->
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

<!-- Load the Google Maps JavaScript API (replace YOUR_API_KEY with your actual key) -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAtBOU_Ez6dNsAsgVXTxbhl_IC09meVzlw"></script>
<script>
  let map;
  let marker;

  // Initialize the map for given lat, lng, and driver name.
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
        url: "<?= base_url('public/images/delivery-truck.gif') ?>",
        scaledSize: new google.maps.Size(40, 40),
      }
    });
  }

  // When modal is shown, extract driver info and initialize the map.
  const mapModal = document.getElementById('mapModal');
  mapModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const driverId = button.getAttribute('data-driverid');
    const lat = button.getAttribute('data-lat');
    const lng = button.getAttribute('data-lng');
    const name = button.getAttribute('data-name');

    const modalTitle = mapModal.querySelector('.modal-title');
    modalTitle.textContent = 'Location for ' + name + ' (' + driverId + ')';

    // Delay initialization to ensure modal is fully rendered.
    setTimeout(() => {
      initMap(lat, lng, name);
    }, 300);
  });
</script>

<?= $this->endSection() ?>
