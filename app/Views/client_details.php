<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>
<div class="header">Client Details</div>

<div class="content">
    <div class="client-details">
        <h2>Client Information</h2>
        <table>
            <tr><th>Client Name:</th><td><?= $client['name'] ?></td></tr>
            <tr><th>Contact Person:</th><td><?= $client['contact_person'] ?></td></tr>
            <tr><th>Email:</th><td><?= $client['email'] ?></td></tr>
            <tr><th>Contact Number:</th><td><?= $client['contact_number'] ?></td></tr>
            <tr><th>Address:</th><td><?= $client['address'] ?></td></tr>
            <tr><th>Username:</th><td><?= $client['username'] ?></td></tr>
            <tr><th>Business Type:</th><td><?= $client['business_type'] ?></td></tr>
            <tr><th>Preferred Truck:</th><td><?= $client['preferred_truck'] ?></td></tr>
            <tr><th>Cargo Type:</th><td><?= $client['cargo_type'] ?></td></tr>
            <tr><th>Payment Mode:</th><td><?= $client['payment_mode'] ?></td></tr>
            <tr><th>Pickup Location:</th><td><?= $client['pickup_location'] ?></td></tr>
            <tr><th>Drop-off Location:</th><td><?= $client['dropoff_location'] ?></td></tr>
            <tr><th>Client Since:</th><td><?= $client['client_since'] ?></td></tr>
            <tr><th>Notes:</th><td><?= $client['notes'] ?></td></tr>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
