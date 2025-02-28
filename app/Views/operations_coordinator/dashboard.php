<?= $this->extend('templates/operations_coordinator_layout') ?>

<?= $this->section('content') ?>
<link href=<?=base_url('public/assets/css/style.css');?> rel="stylesheet">
<body>
<title>Operations Coordinator Dashboard</title>
<h1>Operations Coordinator Dashboard</h1>


    <div class="container-fluid mt-5">
        <div class="row">
            <!-- Maintenance Visualization -->
            <div class="col-lg-8">
                <div class="card p-4 shadow-sm">
                    <h3>Maintenance Visualization</h3>
                    <div class="d-flex flex-wrap">
                        <canvas id="maintenancePieChart" class="me-3"></canvas>
                        <canvas id="maintenanceBarChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Side Info Cards -->
            <div class="col-lg-4">
                <div class="card p-4 mb-3 shadow-sm">
                    <h4 class="text-center">Driver On Duty</h4>
                    <div class="text-center bg-success text-white p-3 rounded">
                        <h2>4</h2>
                        <p>On Duty</p>
                    </div>
                </div>
                <div class="card p-4 shadow-sm">
                    <h4 class="text-center">Truck Available</h4>
                    <div class="text-center bg-primary text-white p-3 rounded">
                        <h2>4</h2>
                        <p>Available</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= base_url('/public/assets/js/chart.js') ?>"></script>
    <script src="<?= base_url('/public/assets/js/script.js') ?>"></script>

<?= $this->endSection() ?>
