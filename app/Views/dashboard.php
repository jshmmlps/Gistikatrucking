<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>

<div class="header" style="background-color:#003366; padding: 30px; display: flex; justify-content: center; align-items: center; height: 60px;">
    <h3 style="color: azure; font-weight: 700; margin: 0;">Dashboard</h3>
</div>


<div class="content">
    <!-- Data Visualization Section -->
    <div class="visualizations" style="display: flex; justify-content: space-between; gap: 20px; margin-top: 20px;">
        <div class="chart" style="flex: 1; background: white; padding: 15px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
            <h4>Data Visualization</h4>
            <img src="<?= base_url('public/assets/images/charts.png') ?>" alt="Chart" style="width: 100%;">
        </div>
        <div class="chart" style="flex: 1; background: white; padding: 15px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
            <h4>Days Until Maintenance</h4>
            <canvas id="barChart"></canvas>
        </div>
    </div>

    <!-- Cards Section -->
    <div class="cards" style="display: flex; justify-content: space-between; gap: 20px; margin-top: 20px;">
        <div class="card" style="flex: 1; background: white; padding: 15px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); text-align: center;">
            <h3>Available Truck</h3>
            <div class="data" style="font-size: 24px; font-weight: bold; color: #007bff;">
                <?= $available_trucks ?> Available
            </div>
        </div>
        <div class="card" style="flex: 1; background: white; padding: 15px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); text-align: center;">
            <h3>Geolocation</h3>
            <div class="map">
                <img src="<?= base_url('public/assets/images/maps.jpg') ?>" alt="Map" style="width: 100%; border-radius: 5px;">
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

