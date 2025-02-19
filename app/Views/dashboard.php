<?= $this->extend('templates/layout.php') ?>

<?= $this->section('content') ?>
<link href=<?=base_url('public/assets/css/style.css');?> rel="stylesheet">
<title>Dashboard</title>
<div class="headerbg">
    <h1>Dashboard</h1>
</div>

<div class="content">
    <!-- Data Visualization Section -->
    <div class="visualizations">
        <div class="chart">
            <h4>Data Visualization</h4>
            <!--<canvas id="pieChart"></canvas>-->
            <img src="public/images/charts.png" alt="chart">
        </div>
        <div class="chart">
            <h4>Days Until Maintenance</h4>
            <canvas id="barChart"></canvas>
        </div>
    </div>
</div>
<div>
    <!-- Cards Section -->
    <div class="cards">
        <div class="card">
            <h3>Available Truck</h3>
            <div class="data"><?= $available_trucks ?> Available</div>
        </div>
        <div class="card">
            <h3>Geolocation</h3>
            <div class="map">
                <img src="public/images/maps.jpg" alt="Map">
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

