<?= $this->extend('templates/client_layout.php') ?>

<?= $this->section('content') ?>
<link href=<?=base_url('public/assets/css/style.css');?> rel="stylesheet">
<body>
<title>Geolocation</title>
<h1>Geolocation</h1>


<div class="container-fluid mt-4">
    <!-- Display any flash messages -->
    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

   
</div>


<script src="<?= base_url('assets/js/script.js') ?>"></script>
</body>
</html>

<?= $this->endSection() ?>