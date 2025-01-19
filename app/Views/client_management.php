<?= $this->extend('templates/layout') ?>

<?= $this->section('content') ?>
<div class="header">Client Management</div>

<div class="content">
    <div class="client-list">
        <h2>Client List</h2>
        <table>
            <thead>
                <tr>
                    <th>Client Name</th>
                    <th>Booking Date</th>
                    <th>Dispatch Date</th>
                    <th>Cargo Type</th>
                    <th>Drop-off Location</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                <tr>
                    <td><?= $client['name'] ?></td>
                    <td><?= $client['booking_date'] ?></td>
                    <td><?= $client['dispatch_date'] ?></td>
                    <td><?= $client['cargo_type'] ?></td>
                    <td><?= $client['drop_off'] ?></td>
                    <td><?= $client['status'] ?></td>
                    <td><a href="<?= base_url('clients/view/' . $client['id']) ?>">View</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
