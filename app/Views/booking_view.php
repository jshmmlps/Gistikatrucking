<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Management</title>
</head>
<body>

<h1>Booking Management</h1>

<table border="1">
    <tr>
        <th>Client Name</th>
        <th>Booking Date</th>
        <th>Dispatch Date</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($bookings as $booking): ?>
        <tr>
            <td><?= esc($booking['client_name']) ?></td>
            <td><?= esc($booking['booking_date']) ?></td>
            <td><?= esc($booking['dispatch_date']) ?></td>
            <td><?= esc($booking['status']) ?></td>
            <td><a href="/booking/view/<?= $booking['id'] ?>">View</a></td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
