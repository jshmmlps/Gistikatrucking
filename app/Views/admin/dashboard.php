<?= $this->extend('templates/admin_layout.php') ?>

<?= $this->section('content') ?>
<link href="<?= base_url('public/assets/css/style.css'); ?>" rel="stylesheet">
<title>Dashboard</title>

<div class="container-fluid mt-4">
    <h1>Admin Dashboard</h1>

    <!-- Flash messages -->
    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <!-- Cards Row -->
    <div class="row mb-4">
        <!-- Card: Number of Bookings -->
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-header">Number of Bookings</div>
                <div class="card-body">
                    <h3 class="card-title"><?= esc($totalBookings) ?></h3>
                </div>
            </div>
        </div>
        
        <!-- Card: Number of Users -->
        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3">
                <div class="card-header">Pending Bookings</div>
                <div class="card-body">
                    <h3 class="card-title"><?= esc($pendingBookings) ?></h3>
                </div>
            </div>
        </div>

        <!-- Card: Pending Bookings -->
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-header">Number of Users</div>
                <div class="card-body">
                    <h3 class="card-title"><?= esc($numUsers) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Row with Pie Chart (small) and Trucks with drivers -->
    <div class="row mb-4">
        <!-- Small Pie Chart for maintenance status -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Maintenance Status</div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <canvas id="maintenanceChart" width="200" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Available Trucks (with assigned drivers) -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Available Trucks (Assigned Drivers)</div>
                <div class="card-body">
                    <?php if (!empty($trucksWithDrivers)): ?>
                        <p>Total: <strong><?= count($trucksWithDrivers) ?></strong></p>
                        <ul class="list-group">
                            <?php foreach ($trucksWithDrivers as $tid => $truck): ?>
                                <?php
                                    $model = $truck['truck_model'] ?? 'N/A';
                                    $plate = $truck['plate_number'] ?? '';
                                ?>
                                <li class="list-group-item d-flex align-items-center" style="text-align:left;">
                                    <img src="<?= base_url('public/images/delivery-truck.gif') ?>" 
                                         alt="Truck GIF" 
                                         style="width:40px; height:40px; margin-right:10px;">
                                    <div>
                                        <strong><?= esc($tid) ?></strong> - <?= esc($model) ?>
                                        <span class="text-muted">(<?= esc($plate) ?>)</span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>No trucks currently assigned to drivers.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Geolocation Map of All Drivers -->
    <div class="card mb-4">
        <div class="card-header">Driver Locations</div>
        <div class="card-body">
            <div id="map" style="width: 100%; height: 500px;"></div>
        </div>
    </div>
</div>

<!-- Load Chart.js for Pie Chart -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Load Google Maps (replace YOUR_GOOGLE_MAPS_API_KEY) -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAtBOU_Ez6dNsAsgVXTxbhl_IC09meVzlw"></script>

<script>
    // Maintenance Chart Data
    const goodConditionCount = <?= json_encode($goodConditionCount) ?>;
    const needsMaintenanceCount = <?= json_encode($needsMaintenanceCount) ?>;

    // Create a small Pie Chart
    const ctx = document.getElementById('maintenanceChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Good Condition', 'Needs Maintenance'],
            datasets: [{
                data: [goodConditionCount, needsMaintenanceCount],
                backgroundColor: ['#28a745', '#dc3545']
            }]
        },
        options: {
            responsive: false,
            maintainAspectRatio: false, // or set to true if you want a square chart
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Google Map for Driver Locations
    let map;
    let markers = [];

    function initMap() {
        // Center near Metro Manila (example)
        let center = { lat: 14.5995, lng: 120.9842 };
        map = new google.maps.Map(document.getElementById("map"), {
            center: center,
            zoom: 12,
        });

        const driverLocations = <?= json_encode($driverLocations) ?>;
        driverLocations.forEach(driver => {
            let position = { 
                lat: parseFloat(driver.lat), 
                lng: parseFloat(driver.lng) 
            };
            let marker = new google.maps.Marker({
                position: position,
                map: map,
                title: driver.name,
                icon: {
                    url: "<?= base_url('public/images/delivery-truck.gif') ?>", 
                    scaledSize: new google.maps.Size(60, 60)
                }
            });
            markers.push(marker);
        });
    }

    // Initialize Map on Page Load
    window.addEventListener('load', initMap);
</script>

<?= $this->endSection() ?>
