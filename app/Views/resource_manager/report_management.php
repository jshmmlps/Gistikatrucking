<?= $this->extend('templates/resource_manager_layout') ?>

<?= $this->section('content') ?>
<link href=<?=base_url('public/assets/css/style.css');?> rel="stylesheet">
<body>
<title>Resource Manager Report Management</title>
<h1>Resource Manager Report Management</h1>

    <h2 class="fw-bold">Create Maintenance Report</h2>

    <div class="container">
        <div class="report-card">
            <form id="reportForm">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Truck ID</label>
                        <input type="text" class="form-control mb-3">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Plate Number</label>
                        <input type="text" class="form-control mb-3">
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="button" class="btn btn-primary" id="submitReport">Submit Report</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="submissionModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Report Submitted</h5>
                </div>
                <div class="modal-body">
                    <i class="fas fa-check-circle success-icon"></i>
                    <p>Your maintenance report has been successfully submitted.</p>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById("submitReport").addEventListener("click", function() {
            let submissionModal = new bootstrap.Modal(document.getElementById("submissionModal"));
            submissionModal.show();
        });
    </script>

    <script src="<?= base_url('/public/assets/js/script.js') ?>"></script>
<?= $this->endSection() ?>
