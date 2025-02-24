<!DOCTYPE html>
<html>
<head>
    <title>Create User Account</title>
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
                    <h2>Create User Account</h2>
                </div>
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success">
                        <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $field => $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('register/create') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" value="<?= old('first_name') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="<?= old('last_name') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= old('email') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" value="<?= old('username') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="contact_number" class="form-label">Contact Number</label>
                                <input type="text" name="contact_number" class="form-control" value="<?= old('contact_number') ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" name="address" class="form-control" value="<?= old('address') ?>">
                            </div>

                            <div class="mb-3">
                                <label for="address_dropoff" class="form-label">Address Dropoff (customer only)</label>
                                <input type="text" name="address_dropoff" class="form-control" value="<?= old('address_dropoff') ?>">
                            </div>

                            <div class="mb-3">
                                <label for="birthday" class="form-label">Birthday</label>
                                <input type="date" name="birthday" class="form-control" value="<?= old('birthday') ?>">
                            </div>

                            <div class="mb-3">
                                <label for="gender" class="form-label">Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="" <?= old('gender') === '' ? 'selected' : '' ?>>--Select--</option>
                                    <option value="male" <?= old('gender') === 'male' ? 'selected' : '' ?>>Male</option>
                                    <option value="female" <?= old('gender') === 'female' ? 'selected' : '' ?>>Female</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="user_level" class="form-label">User Level</label>
                                <select name="user_level" class="form-select" required>
                                    <option value="" disabled selected>--Select Role--</option>
                                    <option value=" " <?= old('user_level') === 'admin' ? 'selected' : '' ?>>Admin</option>
                                    <option value="staff_op" <?= old('user_level') === 'staff_op' ? 'selected' : '' ?>>Operation Coordinator</option>
                                    <option value="staff_res" <?= old('user_level') === 'staff_res' ? 'selected' : '' ?>>Resource Manager</option>
                                    <option value="customer" <?= old('user_level') === 'customer' ? 'selected' : '' ?>>Customer</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="d-grid mt-3">
                        <button type="submit" class="btn create-btn btn-lg">Create Account</button>
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




<!-- <!DOCTYPE html>
<html>
<head>
    <title>Create User Account</title>
</head>
<body>
    <h1>Create User Account</h1>

    <?php if (session()->getFlashdata('success')): ?>
        <p style="color: green;"><?= session()->getFlashdata('success') ?></p>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div style="color: red;">
            <ul>
                <?php foreach ($errors as $field => $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('register/create') ?>" method="post">
        <?= csrf_field() ?>

        <p>
            <label for="first_name">First Name:</label><br>
            <input type="text" name="first_name" value="<?= old('first_name') ?>" required>
        </p>

        <p>
            <label for="last_name">Last Name:</label><br>
            <input type="text" name="last_name" value="<?= old('last_name') ?>" required>
        </p>

        <p>
            <label for="email">Email:</label><br>
            <input type="email" name="email" value="<?= old('email') ?>" required>
        </p>

        <p>
            <label for="username">Username:</label><br>
            <input type="text" name="username" value="<?= old('username') ?>" required>
        </p>

        <p>
            <label for="password">Password:</label><br>
            <input type="password" name="password" required>
        </p>

        <p>
            <label for="contact_number">Contact Number:</label><br>
            <input type="text" name="contact_number" value="<?= old('contact_number') ?>">
        </p>

        <p>
            <label for="address">Address:</label><br>
            <input type="text" name="address" value="<?= old('address') ?>">
        </p>

        <p>
            <label for="address_dropoff">Address Dropoff (customer only):</label><br>
            <input type="text" name="address_dropoff" value="<?= old('address_dropoff') ?>">
        </p>

        <p>
            <label for="birthday">Birthday:</label><br>
            <input type="date" name="birthday" value="<?= old('birthday') ?>">
        </p>

        <p>
            <label for="gender">Gender:</label><br>
            <select name="gender">
                <option value="" <?= old('gender') === '' ? 'selected' : '' ?>>--Select--</option>
                <option value="male" <?= old('gender') === 'male' ? 'selected' : '' ?>>Male</option>
                <option value="female" <?= old('gender') === 'female' ? 'selected' : '' ?>>Female</option>
            </select>
        </p>

        <p>
            <label for="user_level">User Level:</label><br>
            <select name="user_level" required>
                <option value="" disabled selected>--Select Role--</option>
                <option value="admin" <?= old('user_level') === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="staff_op" <?= old('user_level') === 'staff_op' ? 'selected' : '' ?>>Operation Coordinator</option>
                <option value="staff_res" <?= old('user_level') === 'staff_res' ? 'selected' : '' ?>>Resource Manager</option>
                <option value="customer" <?= old('user_level') === 'customer' ? 'selected' : '' ?>>Customer</option>
            </select>
        </p>

        <button type="submit">Create Account</button>
    </form>
</body>
</html> -->
