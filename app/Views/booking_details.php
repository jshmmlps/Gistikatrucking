<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Details</title>
</head>
<body>

<h1>Booking Details</h1>

<p><strong>Client Name:</strong> <?= esc($booking['client_name']) ?></p>
<p><strong>Booking Date:</strong> <?= esc($booking['booking_date']) ?></p>
<p><strong>Dispatch Date:</strong> <?= esc($booking['dispatch_date']) ?></p>
<p><strong>Status:</strong> <?= esc($booking['status']) ?></p>

<h2>Trip Tickets</h2>
<ul>
    <?php foreach ($tripTickets as $ticket): ?>
        <li>
            <img src="/uploads/trip_tickets/<?= esc($ticket['image_path']) ?>" alt="Trip Ticket" width="200">
        </li>
    <?php endforeach; ?>
</ul>

<h3>Upload New Trip Ticket</h3>
<form action="/booking/uploadTripTicket" method="post" enctype="multipart/form-data">
    <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
    <input type="file" name="trip_image" required>
    <button type="submit">Upload</button>
</form>

</body>
</html>
