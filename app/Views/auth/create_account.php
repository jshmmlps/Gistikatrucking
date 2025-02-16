<!DOCTYPE html>
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
</html>
