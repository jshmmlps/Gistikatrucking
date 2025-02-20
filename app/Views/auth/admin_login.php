<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="<?= base_url('/public/assets/css/style.css') ?>">
    <link href="<?= base_url('/public/assets/css/bootstrap.min.css');?>" rel="stylesheet"> 
    <link href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.css" rel="stylesheet"> 

</head>
<body>
        <div class="login-container">
            <h1>Admin Login</h1>
            <?php if(session()->getFlashdata('error')): ?>
                <p style="color:red;"><?= session()->getFlashdata('error') ?></p>
            <?php endif; ?>

            <form action="<?= base_url('/admin/login/process') ?>" method="post">
                <?= csrf_field() ?>
                <div class="input-group">
                    <label>Username:</label>
                    <input type="text" name="username" required><br><br>
                </div>
                <div class="input-group">
                    <label>Password:</label>
                    <input type="password" name="password" required><br><br>
                </div>

                <button class="login-btn"type="submit">Login</button>
            </form>
        </div>

    <script src="<?php echo base_url('/public/assets/js/jquery-3.7.1.js'); ?>"></script> 
    <script src="<?php echo base_url('/public/assets/js/popper.min.js'); ?>"></script> 
    <script src="<?php echo base_url('/public/assets/js/bootstrap.bundle.min.js'); ?>"></script>
</body>
</html>
