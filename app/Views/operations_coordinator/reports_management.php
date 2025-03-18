<?= $this->extend('templates/operations_coordinator_layout') ?>

<?= $this->section('content') ?>
<link href="<?= base_url('public/assets/css/style.css'); ?>" rel="stylesheet">
<title>Reports Management</title>

<div class="container-fluid mt-4">
    <h1>Reports Management</h1>

    <!-- Flash Messages -->
    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <!-- Reports Table -->
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Report Number</th>
                <th>Report Type</th>
                <th>Date</th>
                <th>Booking ID</th>
                <th>Username</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($reports) && is_array($reports)): ?>
                <?php foreach ($reports as $report): ?>
                    <?php
                        $reportNumber = $report['report_number'] ?? '';
                        $reportType   = $report['report_type'] ?? '';
                        $date         = $report['date'] ?? '';
                        $bookingId    = $report['booking_id'] ?? '';
                        // Use the user_id as username; if you have a lookup, update accordingly.
                        $username     = $report['user_id'] ?? '';
                        $imgUrl       = $report['img_url'] ?? '';
                    ?>
                    <tr>
                        <td><?= esc($reportNumber) ?></td>
                        <td><?= esc($reportType) ?></td>
                        <td><?= esc($date) ?></td>
                        <td><?= esc($bookingId) ?></td>
                        <td><?= esc($username) ?></td>
                        <td>
                            <?php if (!empty($imgUrl)): ?>
                                <button class="btn btn-primary btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#viewReportModal" 
                                        data-img="<?= esc($imgUrl) ?>">
                                    View
                                </button>
                            <?php else: ?>
                                <span class="text-muted">No Image</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center">No reports found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal to Display Report Image -->
<div class="modal fade" id="viewReportModal" tabindex="-1" aria-labelledby="viewReportModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewReportModalLabel">Report Image</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <img id="reportImage" src="" alt="Report Image" class="img-fluid">
      </div>
    </div>
  </div>
</div>

<script>
    // When the "View" button is clicked, set the modal image's src attribute.
    const viewReportModal = document.getElementById('viewReportModal');
    viewReportModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const imgUrl = button.getAttribute('data-img');
        const reportImage = document.getElementById('reportImage');
        reportImage.src = imgUrl;
    });
</script>

<?= $this->endSection() ?>
