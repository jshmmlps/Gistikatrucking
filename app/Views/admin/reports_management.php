<?= $this->extend('templates/admin_layout.php') ?>

<?= $this->section('content') ?>
<link href=<?=base_url('public/assets/css/style.css');?> rel="stylesheet">
<body>
<title>Admin Report Management</title>
<h1>Admin Report Management</h1>


    <!-- Search & Controls -->
    <div class="d-flex justify-content-between mb-3">
        <input type="text" id="searchBar" class="form-control w-25" placeholder="Search">
        <div>
            <button class="btn btn-primary" id="createReportBtn">Create</button>
            <button class="btn btn-outline-secondary" id="sortReportBtn">Sort By</button>
        </div>
    </div>

    <!-- Report Table -->
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Report Number</th>
                    <th>Report Type</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="reportTableBody">
                <!-- Data will be inserted here dynamically -->
                <td>
                    <button class="btn btn-link text-primary view-report">View</button>
                    <button class="btn btn-sm btn-warning edit-report">Edit</button>
                </td>
            </tbody>
        </table>
    </div>

    <!-- View Report Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Report Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Report Number:</strong> <span id="viewReportId"></span></p>
                    <p><strong>Delivery To:</strong> <span id="viewDeliveryTo"></span></p>
                    <p><strong>Date:</strong> <span id="viewReportDate"></span></p>
                    <p><strong>Invoice Number:</strong> <span id="viewInvoiceNumber"></span></p>
                    <p><strong>Driver:</strong> <span id="viewDriver"></span></p>
                    <p><strong>Notes:</strong> <span id="viewNotes"></span></p>
                    <p><strong>Report Type:</strong> <span id="viewReportType"></span></p>
                    <img id="viewReportImage" class="img-fluid border p-2" style="display: none;">
                </div>
            </div>
        </div>
    </div>

    <!-- Create Report Section -->
    <div class="container mt-5" id="createReportSection" style="display: none;">
        <h2>Create Report</h2>
        <form id="reportForm">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label>Delivery To</label>
                        <input type="text" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Date</label>
                        <input type="date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Invoice Number</label>
                        <input type="text" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Driver</label>
                        <input type="text" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Notes</label>
                        <textarea class="form-control" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
                <div class="col-md-4 text-center">
                    <h5>Upload DR/RDR</h5>
                    <input type="file" id="uploadImage" class="form-control mb-2">
                    <img id="previewImage" class="img-fluid border p-2" style="display: none;">
                </div>
            </div>
        </form>
    </div>

    <!-- Edit Report Modal -->
    <div class="modal fade" id="editReportModal" tabindex="-1" aria-labelledby="editReportModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editReportModalLabel">Edit Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editReportForm">
                        <input type="hidden" id="editReportId"> <!-- Hidden ID Field -->

                        <div class="mb-3">
                            <label for="editDeliveryTo" class="form-label">Delivery To</label>
                            <input type="text" class="form-control" id="editDeliveryTo" required>
                        </div>

                        <div class="mb-3">
                            <label for="editReportDate" class="form-label">Date</label>
                            <input type="date" class="form-control" id="editReportDate" required>
                        </div>

                        <div class="mb-3">
                            <label for="editInvoiceNumber" class="form-label">Invoice Number</label>
                            <input type="text" class="form-control" id="editInvoiceNumber" required>
                        </div>

                        <div class="mb-3">
                            <label for="editDriver" class="form-label">Driver</label>
                            <input type="text" class="form-control" id="editDriver" required>
                        </div>

                        <div class="mb-3">
                            <label for="editNotes" class="form-label">Notes</label>
                            <textarea class="form-control" id="editNotes" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="editReportType" class="form-label">Report Type</label>
                            <input type="text" class="form-control" id="editReportType" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= base_url('/public/assets/js/script.js') ?>"></script>

<?= $this->endSection() ?>
