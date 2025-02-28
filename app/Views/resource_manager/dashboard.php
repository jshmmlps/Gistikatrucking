<?= $this->extend('templates/resource_manager_layout') ?>

<?= $this->section('content') ?>
<link href=<?=base_url('public/assets/css/style.css');?> rel="stylesheet">
<body>
<title>Resource Manager Dashboard</title>
<h1>Resource Manager Dashboard</h1>


    <div class="container-fluid">
        <div class="row">
            <!-- Maintenance Visualization -->
            <div class="col-md-8">
                <h4 class="fw-bold">Maintenance Visualization</h4>
                <div class="row">
                    <div class="col-md-6">
                        <canvas id="maintenancePieChart"></canvas>
                    </div>
                    <div class="col-md-6">
                        <canvas id="daysUntilMaintenanceChart"></canvas>
                    </div>
                </div>
                <div class="mt-3">
                    <canvas id="costChart"></canvas>
                </div>
            </div>

            <!-- Available Trucks -->
            <div class="col-md-4">
                <div class="card text-center p-3">
                    <h5>Available Truck</h5>
                    <div class="available-box">
                        <h2 id="available-trucks">4</h2>
                        <p>Available</p>
                    </div>
                </div>

                <!-- Geolocation -->
                <div class="card mt-3 p-3">
                    <h5>Geolocation</h5>
                    <p><strong>Isuzu F-Series FSR34</strong></p>
                    <div id="map"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= base_url('/public/assets/js/script.js') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<?= $this->endSection() ?>
