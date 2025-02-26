<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
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
                    <h2>Forgot Password</h2>
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

                <form action="<?= base_url('password/forgot') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="email" class="form-label">Enter Your Email</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="you@example.com" required>
                    </div>
                    <div class="d-grid mt-3">
                        <button type="submit" class="btn create-btn btn-lg">Send Reset Instructions</button>
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
