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
                <div class="row mb-3">
                    <label for="booking_id" class="col-sm-3 col-form-label">Booking ID</label>
                    <div class="col-sm-9">
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
                </div>

                <!-- Report Type (fixed as "Trip Ticket") -->
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Report Type</label>
                    <div class="col-sm-9">
                        <p class="form-control-plaintext">Trip Ticket</p>
                        <input type="hidden" name="report_type" value="Trip Ticket">
                    </div>
                </div>

                <!-- Trip Date -->
                <div class="row mb-3">
                    <label for="trip_date" class="col-sm-3 col-form-label">Trip Date</label>
                    <div class="col-sm-9">
                        <input type="date" name="trip_date" id="trip_date" class="form-control" required>
                    </div>
                </div>

                <!-- Trip Time -->
                <div class="row mb-3">
                    <label for="trip_time" class="col-sm-3 col-form-label">Trip Time</label>
                    <div class="col-sm-9">
                        <input type="time" name="trip_time" id="trip_time" class="form-control" required>
                    </div>
                </div>

                <!-- Cargo Details -->
                <div class="row mb-3">
                    <label for="cargo_details" class="col-sm-3 col-form-label">Cargo Details</label>
                    <div class="col-sm-9">
                        <textarea name="cargo_details" id="cargo_details" class="form-control" rows="3" placeholder="Enter cargo details here"></textarea>
                    </div>
                </div>

                <!-- File Upload -->
                <div class="row mb-3">
                    <label for="report_image" class="col-sm-3 col-form-label">Upload Trip Ticket Image</label>
                    <div class="col-sm-9">
                        <input type="file" name="report_image" id="report_image" class="form-control" required>
                    </div>
                </div>

                <!-- Submit Button (aligned with input fields) -->
                <div class="row">
                    <div class="col-sm-9 offset-sm-3">
                        <button type="submit" class="btn btn-primary">Submit Report</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/script.js') ?>"></script>
<?= $this->endSection() ?>
