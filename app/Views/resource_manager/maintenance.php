<?= $this->extend('templates/resource_manager_layout.php') ?>

<?= $this->section('content') ?>
<link href="<?= base_url('public/assets/css/style.css'); ?>" rel="stylesheet">

<div class="container-fluid mt-4">
    <h1>Maintenance Analytics</h1>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs" id="managementTabs" role="tablist">
        <li class="nav-item">
            <a href="<?= base_url('resource/trucks'); ?>" class="nav-link <?= (current_url() == base_url('resource/trucks')) ? 'active' : '' ?>">
                <span class="description">Truck Records</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('resource/geolocation'); ?>" class="nav-link <?= (current_url() == base_url('resource/geolocation')) ? 'active' : '' ?>">
                <span class="description">Geolocation</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('resource/maintenance'); ?>" class="nav-link <?= (current_url() == base_url('resource/maintenance')) ? 'active' : '' ?>">
                <span class="description">Maintenance Analytics</span>
            </a>
        </li>
    </ul>
</div>

<!-- Chart & Condition Table side by side -->
<div class="row mt-4">
    <div class="col-md-6">
        <h3>Components Due Chart</h3>
        <!-- Set canvas width to 800px -->
        <canvas id="inspectionChart" style="width:800px;"></canvas>
    </div>
    <div class="col-md-6">
        <h3>Maintenance Condition Table</h3>
        <!-- A static reference table for intervals -->
        <table class="table table-bordered table-sm text-center">
            <thead class="table-info">
                <tr>
                    <th>Component</th>
                    <th>New Trucks<br><small>(0–5 yrs / &lt;100k km)</small></th>
                    <th>Old Trucks<br><small>(&gt;5 yrs / &gt;100k km)</small></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Engine System</td>
                    <td>Every 5,000 km / 6 mo</td>
                    <td>Every 4,000 km</td>
                </tr>
                <tr>
                    <td>Transmission &amp; Drivetrain</td>
                    <td>Every 20,000 km / 24 mo</td>
                    <td>Every 15,000 km</td>
                </tr>
                <tr>
                    <td>Brake System</td>
                    <td>Every 10,000 km</td>
                    <td>Every 4,000 km</td>
                </tr>
                <tr>
                    <td>Suspension &amp; Chassis</td>
                    <td>Every 5,000 km</td>
                    <td>Every 4,000 km</td>
                </tr>
                <tr>
                    <td>Fuel &amp; Cooling System</td>
                    <td>Every 20,000 km</td>
                    <td>Every 15,000 km</td>
                </tr>
                <tr>
                    <td>Steering System</td>
                    <td>Every 20,000 km</td>
                    <td>Every 10,000 km</td>
                </tr>
                <tr>
                    <td>Electrical &amp; Auxiliary</td>
                    <td>Every 10,000 km</td>
                    <td>Every 7,000 km</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Trucks Needing Inspection -->
<div class="row mt-4">
    <div class="col-12">
        <h3 class="text-center">Trucks Needing Inspection</h3>
        <?php if (count($dueTrucks) > 0): ?>
            <table class="table table-bordered table-striped">
                <thead class="table-info text-center">
                    <tr>
                        <th>Truck ID</th>
                        <th>Truck Model</th>
                        <th>Last Service Mileage</th>
                        <th>Condition</th>
                        <th>Last Inspection Date</th>
                        <th>Current Mileage</th>
                        <th>Action Needed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        // Define color-coded badges for components
                        $badgeClasses = [
                            'engine_system'               => 'badge bg-primary',
                            'transmission_drivetrain'     => 'badge bg-success',
                            'brake_system'                => 'badge bg-danger',
                            'suspension_chassis'          => 'badge bg-warning text-dark',
                            'fuel_cooling_system'         => 'badge bg-info text-dark',
                            'steering_system'             => 'badge bg-secondary',
                            'electrical_auxiliary_system' => 'badge bg-dark',
                        ];
                    ?>
                    <?php foreach ($dueTrucks as $item): ?>
                        <?php
                            $truckId           = $item['truckId'];
                            $truckModel        = $item['truckModel'];
                            $manufacturingDate = $item['manufacturingDate'] ?? 'N/A';
                            $details           = $item['details'];
                            $lastServiceMileage = $details['last_inspection_mileage'] ?? 'N/A';
                            $condition         = $item['condition'] ?? 'Unknown'; // New or Old
                            $dueComponents     = $item['dueComponents'];
                        ?>
                        <tr>
                            <td class="text-center"><?= esc($truckId) ?></td>
                            <td class="text-center"><?= esc($truckModel) ?></td>
                            <td class="text-center"><?= esc($lastServiceMileage) ?></td>
                            <td class="text-center"><?= esc($condition) ?></td>
                            <td class="text-center"><?= esc($details['last_inspection_date'] ?? 'N/A') ?></td>
                            <td class="text-center"><?= esc($details['current_mileage'] ?? 'N/A') ?></td>
                            <td>
                                <?php foreach ($dueComponents as $compKey): ?>
                                    <?php 
                                        $label = $allComponents[$compKey] ?? $compKey; 
                                        $css   = $badgeClasses[$compKey] ?? 'badge bg-secondary';
                                    ?>
                                    <span class="<?= $css ?>" style="margin-right:5px;">
                                        <?= esc($label) ?>
                                    </span>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center">No trucks currently need inspection.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Modal for showing trucks per component (triggered by chart click) -->
<div class="modal fade" id="componentModal" tabindex="-1" aria-labelledby="componentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="componentModalLabel">Trucks Needing <span id="componentName"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered">
            <thead class="table-info">
                <tr>
                    <th>Truck ID</th>
                    <th>Truck Model</th>
                </tr>
            </thead>
            <tbody id="componentModalBody">
                <!-- Filled by JS on click -->
            </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var chartData       = <?= json_encode($chartData) ?>;
    var componentTrucks = <?= json_encode($componentTrucks) ?>;
    var allComponents   = <?= json_encode($allComponents) ?>;
    var componentKeys   = Object.keys(allComponents);

    // Set distinct colors for each bar
    chartData.datasets[0].backgroundColor = [
      'rgba(255, 99, 132, 0.7)',   // Engine System
      'rgba(54, 162, 235, 0.7)',   // Transmission & Drivetrain
      'rgba(255, 206, 86, 0.7)',   // Brake System
      'rgba(75, 192, 192, 0.7)',   // Suspension & Chassis
      'rgba(153, 102, 255, 0.7)',  // Fuel & Cooling System
      'rgba(255, 159, 64, 0.7)',   // Steering System
      'rgba(201, 203, 207, 0.7)'   // Electrical & Auxiliary System
    ];

    var ctx = document.getElementById('inspectionChart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: chartData.datasets
        },
        options: {
            responsive: true,
            onClick: function(evt) {
                var activePoints = myChart.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, false);
                if (activePoints.length > 0) {
                    var idx = activePoints[0].index;
                    var componentKey = componentKeys[idx];
                    var trucks = componentTrucks[componentKey] || [];

                    // Update modal title
                    document.getElementById('componentName').textContent = allComponents[componentKey] || componentKey;

                    // Build table rows in modal
                    var tbody = document.getElementById('componentModalBody');
                    tbody.innerHTML = '';
                    if (trucks.length > 0) {
                        trucks.forEach(function(t) {
                            var tr = document.createElement('tr');
                            var tdId = document.createElement('td');
                            tdId.textContent = t.truck_id;
                            var tdModel = document.createElement('td');
                            tdModel.textContent = t.truck_model;
                            tr.appendChild(tdId);
                            tr.appendChild(tdModel);
                            tbody.appendChild(tr);
                        });
                    } else {
                        var tr = document.createElement('tr');
                        var td = document.createElement('td');
                        td.setAttribute('colspan', '2');
                        td.textContent = 'No trucks need this component right now.';
                        tr.appendChild(td);
                        tbody.appendChild(tr);
                    }

                    // Show the modal (Bootstrap 5)
                    var componentModal = new bootstrap.Modal(document.getElementById('componentModal'));
                    componentModal.show();
                }
            },
            scales: {
                y: { 
                    beginAtZero: true,
                    title: { display: true, text: 'Number of Trucks Needing' }
                },
                x: {
                    title: { display: true, text: 'Maintenance Components' }
                }
            }
        }
    });
</script>

<?= $this->endSection() ?>
