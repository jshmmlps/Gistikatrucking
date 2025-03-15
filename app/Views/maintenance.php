<?= $this->extend('templates/admin_layout.php') ?>

<?= $this->section('content') ?>
<link href="<?= base_url('public/assets/css/style.css'); ?>" rel="stylesheet">

<div class="container-fluid mt-4">
    <h1>Maintenance Analytics</h1>
</div>

<!-- Analytics Section: Chart and Trucks Needing Inspection -->
<div class="row">
    <div class="col-md-6">
        <!-- Chart.js Integration -->
        <canvas id="inspectionChart" style="max-width: 600px;"></canvas>
    </div>
    <div class="col-md-6">
        <h2>Trucks Needing Inspection</h2>
        <?php if (count($dueTrucks) > 0): ?>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Truck ID</th>
                        <th>Last Inspection Date</th>
                        <th>Current Mileage</th>
                        <th>Action Needed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dueTrucks as $item): ?>
                        <?php
                            $truckId = $item['truckId'];
                            $info    = $item['details'];
                        ?>
                        <tr>
                            <td><?= esc($truckId) ?></td>
                            <td><?= esc($info['last_inspection_date'] ?? 'N/A') ?></td>
                            <td><?= esc($info['current_mileage'] ?? 'N/A') ?></td>
                            <td>Inspection Due</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No trucks currently need inspection.</p>
        <?php endif; ?>
    </div>
</div>

<hr>

<!-- Available Trucks Card -->
<div class="card mt-4">
    <div class="card-header">
        <h3>Available Trucks</h3>
    </div>
    <div class="card-body">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Truck ID</th>
                    <th>Last Inspection Date</th>
                    <th>Current Mileage</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($availableTrucks)): ?>
                    <?php foreach ($availableTrucks as $truckId => $truck): ?>
                        <tr>
                            <td><?= esc($truckId) ?></td>
                            <td><?= esc($truck['last_inspection_date'] ?? 'N/A') ?></td>
                            <td><?= esc($truck['current_mileage'] ?? 'N/A') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">No trucks available</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Data passed from the Controller as JSON
    var chartData = <?= json_encode($chartData) ?>;
    var ctx = document.getElementById('inspectionChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: chartData.datasets
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>

<?= $this->endSection() ?>
