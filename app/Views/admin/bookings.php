<?= $this->extend('templates/admin_layout.php') ?>

<?= $this->section('content') ?>
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
    background-color: #34a853 !important;
    color: white;
}
    .badge-completed {
    background-color: #ea4335 !important;
    color: white;
}
</style>
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

    <!-- Search and Filter Controls -->
    <div class="row mb-3">
        <!-- Client Name Search -->
        <div class="col-md-6 mb-2 mb-md-0">
            <input type="text" class="form-control" id="searchClient" placeholder="Search by Client Name">
        </div>
        <!-- Status Filter -->
        <div class="col-md-6">
            <select class="form-select" id="filterStatus">
                <option value="">All Statuses</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="pending">Pending</option>
                <option value="in-transit">In-transit</option>
                <option value="completed">Completed</option>
            </select>
        </div>
    </div>

    <!-- Bookings Table -->
    <table id="bookingsTable" class="table table-bordered">
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
              <td>
                <span class="role-badge" data-role="<?= esc($booking['status'] ?? '') ?>">
                <?= esc($booking['status'] ?? '') ?>
              </td>
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
                  <form action="<?= base_url('admin/update-booking-status') ?>" method="post">
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

                      <!-- Reassign driver (and conductor) -->
                      <div class="p-3 mb-3 rounded-3 shadow-sm bg-light">
                        <h6 class="fw-bold mb-3 text-primary">Reassign Driver / Conductor</h6>
                        <div class="mb-3">
                          <label class="form-label fw-bold">Select Driver (and Partner Conductor):</label>
                          <select name="driver" class="form-select">
                            <?php 
                              if (!empty($driversList) && is_array($driversList)):
                                foreach ($driversList as $dKey => $dInfo):
                                  // For each driver, build the driver label.
                                  $dName = trim(($dInfo['first_name'] ?? '') . ' ' . ($dInfo['last_name'] ?? ''));
                                  $dEmp  = $dInfo['employee_id'] ?? '';
                                  $partnerConductor = null;
                                  if (!empty($conductorsList) && is_array($conductorsList)) {
                                    foreach ($conductorsList as $cKey => $cInfo) {
                                        if (isset($cInfo['truck_assigned']) && $cInfo['truck_assigned'] === ($dInfo['truck_assigned'] ?? '')) {
                                            $partnerConductor = $cInfo;
                                            break;
                                        }
                                    }
                                  }
                                  $cName = $partnerConductor 
                                          ? trim(($partnerConductor['first_name'] ?? '') . ' ' . ($partnerConductor['last_name'] ?? ''))
                                          : 'No Conductor';
                                  $cEmp  = $partnerConductor ? $partnerConductor['employee_id'] ?? '' : '';
                            ?>
                              <option value="<?= esc($dKey) ?>" <?= (isset($currentDriverId) && $currentDriverId === $dKey) ? 'selected' : '' ?>>
                                <?= esc($dName) ?> (<?= esc($dEmp) ?>) - D / <?= esc($cName) ?> <?= $cEmp ? '(' . esc($cEmp) . ')' : '' ?> - C
                              </option>
                            <?php 
                                endforeach;
                              endif;
                            ?>
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
// Initialize maps for each booking modal when shown
document.addEventListener('DOMContentLoaded', function() {
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
      var distanceField = document.getElementById('distance' + bookingId);
      if (distanceField) {
        distanceField.value = distanceKm;
      }
    } else {
      console.error("Directions request failed due to " + status);
    }
  });
}

// Filter function for bookings table
function filterBookings() {
  var searchClient = document.getElementById('searchClient').value.toLowerCase().trim();
  var filterStatus = document.getElementById('filterStatus').value.toLowerCase().trim();
  var rows = document.querySelectorAll('#bookingsTable tbody tr');
  
  rows.forEach(function(row) {
    var clientName = row.cells[2].textContent.toLowerCase().trim();
    var status = row.cells[5].textContent.toLowerCase().trim();
    var matchesClient = clientName.indexOf(searchClient) > -1;
    var matchesStatus = filterStatus ? (status === filterStatus) : true;
    
    row.style.display = (matchesClient && matchesStatus) ? '' : 'none';
  });
}

// Add event listeners for search and status filter
document.getElementById('searchClient').addEventListener('keyup', filterBookings);
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
<?= $this->endSection() ?>
