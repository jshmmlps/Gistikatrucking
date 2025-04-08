<?= $this->extend('templates/client_layout.php') ?>

<?= $this->section('content') ?>
<link href="<?= base_url('public/assets/css/style.css'); ?>" rel="stylesheet">
<title>Report Management</title>

<div class="container-fluid mt-4">
    <h1>Report Management</h1>

    <!-- Flash Messages -->
    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <!-- Report Card -->
    <div class="card">
        <div class="card-header">
            <h4>Create New Report (Trip Ticket)</h4>
        </div>
        <div class="card-body">
            <form action="<?= base_url('client/report/store') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <!-- Booking ID Dropdown -->
                <div class="mb-3">
                    <label for="booking_id" class="form-label">Booking ID</label>
                    <select name="booking_id" id="booking_id" class="form-select" required>
                        <option value="">Select Booking</option>
                        <?php if(!empty($bookings) && is_array($bookings)): ?>
                            <?php foreach($bookings as $booking): ?>
                                <option value="<?= esc($booking['booking_id']) ?>">
                                    <?= esc($booking['booking_id']) ?> - 
                                    <?= esc($booking['pick_up_address'] ?? 'N/A') ?> to 
                                    <?= esc($booking['drop_off_address'] ?? 'N/A') ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Report Type (fixed as "Trip Ticket") -->
                <div class="mb-3">
                    <label class="form-label">Report Type</label>
                    <p class="form-control-plaintext">Trip Ticket</p>
                    <input type="hidden" name="report_type" value="Trip Ticket">
                </div>

                <!-- Additional Fields for Trip Ticket -->
                <div class="mb-3">
                    <label for="trip_date" class="form-label">Trip Date</label>
                    <input type="date" name="trip_date" id="trip_date" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="trip_time" class="form-label">Trip Time</label>
                    <input type="time" name="trip_time" id="trip_time" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="cargo_details" class="form-label">Cargo Details</label>
                    <textarea name="cargo_details" id="cargo_details" class="form-control" rows="3" placeholder="Enter cargo details here"></textarea>
                </div>

                <!-- File Upload -->
                <div class="mb-3">
                    <label for="report_image" class="form-label">Upload Trip Ticket Image</label>
                    <input type="file" name="report_image" id="report_image" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary">Submit Report</button>
            </form>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/script.js') ?>"></script>
<?= $this->endSection() ?>
