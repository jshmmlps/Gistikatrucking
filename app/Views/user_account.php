<?= $this->extend('templates/layout.php') ?>

<?= $this->section('content') ?>
<link href=<?=base_url('public/assets/css/style.css');?> rel="stylesheet">
<body>
<title>User Account</title>
<h1>User Account</h1>

<div class="content">  
    <div class="user-profile">
        <?php foreach ($users as $user): ?>
            <div class="user-card">
                <div class="user-icon">
                    <img src="<?= base_url('public/images/luffy.jpg') ?>" alt="User Icon">
                </div>
                <p class="user-name"><?= strtoupper($user['first_name'] . ' ' . $user['last_name']) ?></p>
                <button onclick="viewUser(<?= $user['id'] ?>)">Upload</button>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="user-container">
        <div class="user-details">
            <h2>Account Information</h2>
            <p><strong>Name:</strong> <span id="user-name"></span></p>
            <p><strong>Email:</strong> <span id="user-email"></span></p>
            <p><strong>Contact:</strong> <span id="user-contact"></span></p>
            <p><strong>Address:</strong> <span id="user-address"></span></p>
            <p><strong>Position:</strong> <span id="user-position"></span></p>
            <p><strong>Username:</strong> <span id="user-username"></span></p>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/script.js') ?>"></script>
</body>
</html>

<?= $this->endSection() ?>