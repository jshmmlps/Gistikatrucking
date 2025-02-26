<!DOCTYPE html>
<html>
<head>
    <title>OTP Verification</title>
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
                    <h2>OTP Verification</h2>
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
                
                <p class="text-center">An OTP has been sent to your email: <strong><?= esc($email) ?></strong>. Please enter it below.</p>

                <form action="<?= base_url('register/verifyOTP') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label for="otp" class="form-label">Enter OTP</label>
                        <input type="text" name="otp" id="otp" class="form-control" required>
                    </div>
                    <div class="d-grid mt-3">
                        <button type="submit" class="btn create-btn btn-lg">Verify OTP</button>
                    </div>
                </form>
                <br>
                <div class="text-center">
                    <button id="resendBtn" onclick="location.href='<?= base_url('register/resendOTP') ?>'" class="btn btn-secondary">Resend OTP</button>
                    <p id="timer"></p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="<?= base_url('/public/assets/js/jquery-3.7.1.js'); ?>"></script>
    <script src="<?= base_url('/public/assets/js/popper.min.js'); ?>"></script>
    <script src="<?= base_url('/public/assets/js/bootstrap.bundle.min.js'); ?>"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const resendBtn = document.getElementById("resendBtn");
            const timerEl   = document.getElementById("timer");
            let timeLeft    = 60; // 60 seconds cooldown

            // Disable resend button initially
            resendBtn.disabled = true;

            const countdown = setInterval(() => {
                timeLeft--;
                timerEl.innerText = "Resend available in " + timeLeft + " seconds";
                if(timeLeft <= 0) {
                    clearInterval(countdown);
                    timerEl.innerText = "";
                    resendBtn.disabled = false;
                }
            }, 1000);
        });
    </script>
</body>
</html>
