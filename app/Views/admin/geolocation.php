<?= $this->extend('templates/admin_layout.php') ?>

<?= $this->section('content') ?>
<link href="<?= base_url('public/assets/css/style.css'); ?>" rel="stylesheet">

<div class="container-fluid mt-4">
    <h1>Geolocation</h1>

    <!-- Tab Navigation -->
    <ul class="nav nav-tabs" id="managementTabs" role="tablist">
        <a href="<?= base_url('admin/trucks'); ?>" class="nav-link <?= (current_url() == base_url('admin/trucks')) ? 'active' : '' ?>">
            <span class="description">Truck Records</span>
        </a>

        <a href="<?= base_url('admin/geolocation'); ?>" class="nav-link <?= (current_url() == base_url('admin/geolocation')) ? 'active' : '' ?>">
            <span class="description">Geolocation</span>
        </a>
    
        <a href="<?= base_url('admin/maintenance'); ?>" class="nav-link <?= (current_url() == base_url('admin/maintenance')) ? 'active' : '' ?>">
            <span class="description">Maintenance Analytics</span>
        </a>
        </li>
    </ul>
    
</div>



<?= $this->endSection() ?>
