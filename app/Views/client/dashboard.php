<?= $this->extend('templates/client_layout.php') ?>
<?= $this->section('content') ?>

<link href="<?= base_url('public/assets/css/style.css'); ?>" rel="stylesheet">
<title>Client Dashboard</title>

<style>
    /* Notification pop-up container styles */
    .notification-popup {
        position: fixed;
        top: 1%;
        right: 2%;
        width: 300px;
        max-height: 80%;
        overflow-y: auto;
        z-index: 1050;
        background:rgb(189, 189, 189);
        box-shadow: -2px 0 8px rgba(0,0,0,0.2);
        padding: 10px;
    }
    .notification-popup .notification-card {
        margin-bottom: 10px;
    }
</style>

<div class="container-fluid mt-4">
    <h1 class="mb-4">Client Dashboard</h1>

    <!-- Flash messages -->
    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <!-- Top row: 4 Stats Cards -->
    <div class="row g-3 mb-4">
        <!-- Pending -->
        <div class="col-md-3">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h5 class="card-title text-secondary">Pending</h5>
                    <p class="display-5 fw-bold text-primary"><?= esc($pendingCount) ?></p>
                </div>
            </div>
        </div>
        <!-- Ongoing -->
        <div class="col-md-3">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h5 class="card-title text-secondary">Ongoing</h5>
                    <p class="display-5 fw-bold text-primary"><?= esc($ongoingCount) ?></p>
                </div>
            </div>
        </div>
        <!-- Completed -->
        <div class="col-md-3">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h5 class="card-title text-secondary">Completed</h5>
                    <p class="display-5 fw-bold text-success"><?= esc($completedCount) ?></p>
                </div>
            </div>
        </div>
        <!-- Rejected -->
        <div class="col-md-3">
            <div class="card shadow-sm text-center">
                <div class="card-body">
                    <h5 class="card-title text-secondary">Rejected</h5>
                    <p class="display-5 fw-bold text-danger"><?= esc($rejectedCount) ?></p>
                </div>
            </div>
        </div>
    </div><!-- end row -->

    <!-- Next row: side-by-side -> monthly bookings vs. booking history -->
    <div class="row">
        <!-- LEFT COLUMN: Monthly Bookings -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Bookings for <?= esc($currentYearMonth) ?></h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($monthlyBookings)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Booking #</th>
                                        <th>Date</th>
                                        <th>Cargo Type</th>
                                        <th>Weight</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($monthlyBookings as $b): ?>
                                        <tr>
                                            <td><?= esc($b['booking_id'] ?? '') ?></td>
                                            <td><?= esc($b['booking_date'] ?? '') ?></td>
                                            <td><?= esc($b['cargo_type'] ?? '') ?></td>
                                            <td><?= esc($b['cargo_weight'] ?? '') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="p-3 mb-0">No bookings for this month.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- RIGHT COLUMN: Booking History -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Booking History</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($historyBookings) && is_array($historyBookings)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Cargo Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($historyBookings as $hb): ?>
                                        <tr>
                                            <td><?= esc($hb['booking_id'] ?? '') ?></td>
                                            <td><?= esc($hb['booking_date'] ?? '') ?></td>
                                            <td><?= esc($hb['status'] ?? '') ?></td>
                                            <td><?= esc($hb['cargo_type'] ?? '') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="p-3 mb-0">No completed or rejected bookings.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div><!-- end row -->
</div>

<!-- Notification Popup - Fixed to the side -->
<?php if (!empty($notifications) && is_array($notifications)): ?>
<div class="notification-popup">
    <h5>Notifications</h5>
    <?php foreach ($notifications as $notifId => $notif): ?>
        <div class="card notification-card">
            <div class="card-body p-2">
                <p class="mb-1"><?= esc($notif['message']) ?></p>
                <small class="text-muted"><?= date('M d, Y H:i', strtotime($notif['timestamp'])) ?></small>
                <a href="<?= base_url('client/notifications/dismiss/' . $notifId) ?>" class="btn btn-sm btn-outline-secondary float-end">Dismiss</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
