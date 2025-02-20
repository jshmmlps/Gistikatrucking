<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" href="<?= base_url('/public/assets/css/login.css') ?>">
    <link href="<?= base_url('/public/assets/css/bootstrap.min.css');?>" rel="stylesheet">
</head>
<body style="background: #cecece;">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="row border rounded-5 p-3 bg-white shadow box-area">
            <div class="col-md-6 rounded-4 d-flex justify-content-center align-items-center flex-column left-box" style="background: #cecece;">
                <div class="featured-img">
                    <img src="trucking_logo.png" alt="#" style="width: 250px;">
                    <p class="text-white fs-2" style="font-family: Arial, sans-serif;">INSERT IMAGE</p>
                    <small class="text-white text-wrap text-center" style="font-family: Arial, sans-serif;">INSERT IMAGE</small>
                </div>
            </div>
            <div class="col-md-6 right-box">
                <div class="row align-items-center">
                    <div class="header-text mb-4">
                        <h2>Admin Login</h2>
                        <?php if(session()->getFlashdata('error')): ?>
                            <p style="color:red;"><?= session()->getFlashdata('error') ?></p>
                        <?php endif; ?>
                    </div>
                    <form action="<?= base_url('/admin/login/process') ?>" method="post"> <?= csrf_field() ?>>     
                        <div class="input-group mb-3">
                            <input type="text" name="username" required autofocus class="form-control form-control-lg bg-light fs-6"id="emailAddress" placeholder="Email address">
                        </div>
                        <div class="input-group mb-1">
                            <input type="password" name="password" required autofocus class="form-control form-control-lg bg-light fs-6"id="Password" placeholder="Password">
                        </div>
                        <div class="input-group mb-5 d-flex justify-content-between">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="formCheck">
                                <label for="formCheck" class="form-check-label text-secondary"><small>Remember me</small></label>
                            </div>
                            <div class="forgot">
                                <small><a href="#">Forgot password?</a></small>
                            </div>
                        </div>
                        <div class="input-group mb-3">
                            <button class="btn btn-lg btn-primary w-100 fs-6">Login</button>
                        </div>
                        <div class="row ">
                            <small>Don't have an account?<a href="#">Sign Up</a></small>
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



<!-- PREVIOUS LOGIN PHP
    <section class="container d-flex justify-content-center align-items-center min-vh-100 bg-secondary" >
        <div class="mt-4 mb-4 text-entry">
            <img src="trucking_logo.png" class="img-fluid" alt="#">
            <h1 class="h3 my-3">Admin Login</h1>
                <?php if(session()->getFlashdata('error')): ?>
                    <p style="color:red;"><?= session()->getFlashdata('error') ?></p>
                <?php endif; ?>

                <form action="<?= base_url('/admin/login/process') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="input-group">
                        <input type="text" name="username" required autofocus class="form-control mb-2"id="emailAddress" placeholder="Email address">
                    </div>
                    <div class="input-group">
                        <input type="password" name="password" required autofocus class="form-control mt-2"id="Password" placeholder="Password">
                    </div>
                    <div class="mt-3 checkbox">
                        <label>
                            <input type="checkbox" value="remember-me"> Remember me
                        </label>
                    </div>
                    <div class="my-3">
                        <button class="btn btn-lg btn-primary submit-btn" type="submit">Login</button>
                    </div>
                </form>
        </div>
    </section>
-->