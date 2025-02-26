<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="<?= base_url('/public/assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('/public/assets/css/bootstrap.min.css'); ?>">
    <style>
        body .container {
            margin-top: 0 !important;
        }
    </style>
</head>
<body style="background:linear-gradient(135deg, #003366, #00c6ff);">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="row border rounded-5 p-4 bg-white shadow box-area">
            <div class="col-md-12">
                <div class="header-text mb-4 text-center">
                    <h2>Reset Your Password</h2>
                </div>
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success">
                        <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger">
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('password/reset') ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="token" value="<?= esc($token) ?>">
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                    </div>
                    <div class="d-grid mt-3">
                        <button type="submit" class="btn create-btn btn-lg">Reset Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="<?= base_url('/public/assets/js/jquery-3.7.1.js'); ?>"></script>
    <script src="<?= base_url('/public/assets/js/popper.min.js'); ?>"></script>
    <script src="<?= base_url('/public/assets/js/bootstrap.bundle.min.js'); ?>"></script>
</body>
</html>
