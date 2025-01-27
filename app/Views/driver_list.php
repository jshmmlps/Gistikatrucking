<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver and Conductor Management</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/styles.css') ?>">
</head>
<body>

<div class="container">
    <aside class="sidebar">
        <div class="profile">
            <img src="<?= base_url('assets/img/avatar.png') ?>" alt="Profile">
            <h3>Jamaeca Quizon</h3>
            <span>Admin</span>
        </div>
        <ul>
            <li>Dashboard</li>
            <li>User Account</li>
            <li>Client Management</li>
            <li class="active">Driver and Conductor Management</li>
            <li>Booking Management</li>
            <li>Truck Record and Monitoring</li>
            <li>Report Management</li>
        </ul>
        <button id="logout">Logout</button>
    </aside>

    <main>
        <h1>DRIVER AND CONDUCTOR MANAGEMENT</h1>
        <table>
            <thead>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Contact Number</th>
                    <th>Position</th>
                    <th>Home Address</th>
                    <th>Employee ID</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($drivers as $driver): ?>
                <tr>
                    <td><?= $driver['first_name'] ?></td>
                    <td><?= $driver['last_name'] ?></td>
                    <td><?= $driver['contact_number'] ?></td>
                    <td><?= $driver['position'] ?></td>
                    <td><?= $driver['home_address'] ?></td>
                    <td><?= $driver['employee_id'] ?></td>
                    <td><button onclick="viewDetails('<?= $driver['employee_id'] ?>')">View</button></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>

    <aside class="details">
        <h2>Complete Details</h2>
        <p><strong>Name:</strong> <span id="name"></span></p>
        <p><strong>Contact:</strong> <span id="contact"></span></p>
        <p><strong>Position:</strong> <span id="position"></span></p>
        <p><strong>Employee ID:</strong> <span id="employee_id"></span></p>
        <p><strong>Address:</strong> <span id="address"></span></p>
    </aside>
</div>

<script src="<?= base_url('assets/js/script.js') ?>"></script>
</body>
</html>
