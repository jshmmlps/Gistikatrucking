<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
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
            <div class="row border rounded-5 p-3 bg-white shadow box-area">
                <div class="col-md-6 rounded-4 d-flex justify-content-center align-items-center flex-column left-box" >
                    <div class="featured-img">
                        <img src="<?= base_url('/public/images/icons/logo.png'); ?>" alt="Logo" style="width: 300px;">
                    </div>
                </div>
                <div class="col-md-6 right-box">
                    <div class="row align-items-center">
                        <div class="header-text mb-4">  
                            <h2>Login</h2>
                            <?php if(session()->getFlashdata('error')): ?>
                                <p style="color:red;"><?= session()->getFlashdata('error') ?></p>
                            <?php endif; ?>

                            <?php if(session()->getFlashdata('success')): ?>
                                <p style="color:green;"><?= session()->getFlashdata('success') ?></p>
                            <?php endif; ?>
                        </div>
                        <form action="<?= base_url('/login/process') ?>" method="post"> <?= csrf_field() ?>
                            <div class="input-group mb-3">
                                <input type="text" name="username" required autofocus class="form-control form-control-lg bg-light fs-6"id="emailAddress" placeholder="Username or Email">
                            </div>
                            <div class="input-group mb-1">
                                <input type="password" name="password" required autofocus class="form-control form-control-lg bg-light fs-6"id="Password" placeholder="Password">
                            </div>
                            <div class="input-group mb-5 d-flex justify-content-between">
                                <div class="form-check">
                                    <!-- <input type="checkbox" class="form-check-input" id="formCheck"> -->
                                    <!-- <label for="formCheck" class="form-check-label text-secondary"><small>Remember me</small></label> -->
                                </div>
                                <div class="forgot">
                                    <small><a href="<?= base_url('/password/forgot') ?>">Forgot password?</a></small>
                                </div>
                            </div>
                            <div class="input-group mb-3">
                                <button class="btn btn-lg login-btn w-100 fs-6">Login</button>
                            </div>
                            <div class="row ">
                                <small>Don't have an account? <a href="<?= base_url('/register') ?>">Sign Up</a></small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
     
    <script src="<?php echo base_url('/public/assets/js/jquery-3.7.1.js'); ?>"></script> 
    <script src="<?php echo base_url('/public/assets/js/popper.min.js'); ?>"></script> 
    <script src="<?php echo base_url('/public/assets/js/bootstrap.bundle.min.js'); ?>"></script>
</body>
</html>

