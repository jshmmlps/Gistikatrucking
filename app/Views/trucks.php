<?= $this->extend('templates/layout.php') ?>

<?= $this->section('content') ?>
<link href=<?= base_url('public/assets/css/style.css'); ?> rel="stylesheet">

<div class="container">
    <h1 class="title">Truck Record and Maintenance Management</h1>
    
    <div class="tabs">
        <button class="active">Truck Details</button>
        <button disabled>Geolocation</button>
        <button disabled>Maintenance</button>
    </div>

    <div class="grid-container">
        <?php foreach ($trucks as $truck): ?>
            <div class="truck-card">
                <div class="truck-header">
                    <i class="fas fa-truck"></i>
                    <h3><?= esc($truck['name']) ?></h3>
                </div>
                <p>Plate Number: <?= esc($truck['plate_number']) ?></p>
                <p><?= esc($truck['wheels']) ?>-Wheeler</p>
                <p>Status: <?= esc($truck['status']) ?></p>
                <a href="<?= site_url('trucks/view/' . $truck['id']) ?>" class="view-button">View</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.container { text-align: center; }
.title { font-size: 24px; margin: 20px 0; }
.tabs { display: flex; justify-content: center; gap: 10px; margin-bottom: 20px; }
.tabs button { padding: 10px; border: none; background: #ccc; cursor: pointer; }
.tabs button.active { background: #0056b3; color: white; }
.grid-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
.truck-card { background: #f8f8f8; padding: 15px; border-radius: 8px; text-align: left; }
.truck-header { display: flex; align-items: center; gap: 10px; }
.view-button { display: block; margin-top: 10px; padding: 8px; background: #ffcc00; text-align: center; text-decoration: none; }
</style>

<?= $this->endSection() ?>
