<?= $this->extend('templates/admin_layout.php') ?>

<?= $this->section('content') ?>
<link href="<?= base_url('public/assets/css/style.css'); ?>" rel="stylesheet">
<style>
    .role-badge {
    display: inline-block;
    padding: 0.35em 0.65em;
    font-size: 0.75em;
    font-weight: 700;
    line-height: 1;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 0.375rem;
}

/* Custom badge colors */
    .badge-pending {
    background-color: #ff6d01 !important;
    color: white;
}
    .badge-approved {
    background-color: #674ea7 !important;
    color: white;
}
    .badge-intransit {
    background-color: #4285f4 !important;
    color: white;
}
    .badge-rejected {
    background-color: #ea4335 !important;
    color: white;
}
    .badge-completed {
    background-color: #34a853 !important;
    color: white;
}
</style>
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

    <!-- Search and Filter Controls -->
    <div class="row mb-3">
        <!-- Search by Report Number -->
        <div class="col-md-6 mb-2 mb-md-0">
            <input type="text" class="form-control" id="searchReportNumber" placeholder="Search by Report Number">
        </div>
        <!-- Filter by Report Type -->
        <div class="col-md-6">
            <select class="form-select" id="filterReportType">
                <option value="">All Report Types</option>
                <?php
                    $types = [];
                    if (!empty($reports) && is_array($reports)) {
                        foreach ($reports as $report) {
                            if (isset($report['report_type']) && !in_array($report['report_type'], $types)) {
                                $types[] = $report['report_type'];
                            }
                        }
                    }
                    sort($types);
                    foreach ($types as $type):
                ?>
                    <option value="<?= esc(strtolower($type)) ?>"><?= esc($type) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Reports Table -->
    <table class="table table-bordered table-striped" id="reportsTable">
        <thead class="table-light">
            <tr>
                <th>Report Number</th>
                <th>Report Type</th>
                <th>Date</th>
                <th>Booking ID</th>
                <th>Action</th>
                <th>Remark</th>
                <th>Status</th>
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
                        $imgUrl       = $report['img_url'] ?? '';
                        $remark       = $report['remark'] ?? '';
                        $remarkStatus = $report['remark_status'] ?? 'Pending';

                        // Additional details for detailed view
                        $driverName   = $report['driver_name'] ?? '';
                        $plateNumber  = $report['plate_number'] ?? '';
                        $origin       = $report['origin'] ?? '';
                        $destination  = $report['destination'] ?? '';
                        $tripDate     = $report['trip_date'] ?? '';
                        $tripTime     = $report['trip_time'] ?? '';
                        $cargoDetails = $report['cargo_details'] ?? '';
                        $inspectionDate = $report['inspection_date'] ?? '';
                        $mileageAfter   = $report['mileage_after_inspection'] ?? '';
                        $actionNeeded   = $report['action_needed'] ?? '';
                        $serviceType    = $report['service_type'] ?? '';
                        $technicianName = $report['technician_name'] ?? '';
                        $estimateNext   = $report['estimate_next_service_mileage'] ?? '';
                        $expectedNext   = $report['expected_next_service_time'] ?? '';
                        $truckId        = $report['truck_id'] ?? '';
                    ?>
                    <tr>
                        <td><?= esc($reportNumber) ?></td>
                        <td><?= esc($reportType) ?></td>
                        <td><?= esc($date) ?></td>
                        <td><?= esc($bookingId) ?></td>
                        <td>
                            <?php if (!empty($imgUrl)): ?>
                                <button class="btn btn-primary btn-sm viewReportBtn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#viewReportModal"
                                        data-img="<?= esc($imgUrl) ?>"
                                        data-type="<?= esc($reportType) ?>"
                                        data-reportnumber="<?= esc($reportNumber) ?>"
                                        data-date="<?= esc($date) ?>"
                                        data-bookingid="<?= esc($bookingId) ?>"
                                        data-driver="<?= esc($driverName) ?>"
                                        data-plate="<?= esc($plateNumber) ?>"
                                        data-origin="<?= esc($origin) ?>"
                                        data-destination="<?= esc($destination) ?>"
                                        data-tripdate="<?= esc($tripDate) ?>"
                                        data-triptime="<?= esc($tripTime) ?>"
                                        data-cargodetails="<?= esc($cargoDetails) ?>"
                                        data-inspectiondate="<?= esc($inspectionDate) ?>"
                                        data-mileageafter="<?= esc($mileageAfter) ?>"
                                        data-actionneeded="<?= esc($actionNeeded) ?>"
                                        data-servicetype="<?= esc($serviceType) ?>"
                                        data-technician="<?= esc($technicianName) ?>"
                                        data-estimate="<?= esc($estimateNext) ?>"
                                        data-expected="<?= esc($expectedNext) ?>"
                                        data-truckid="<?= esc($truckId) ?>">
                                    View
                                </button>
                            <?php else: ?>
                                <span class="text-muted">No Image</span>
                            <?php endif; ?>
                        </td>
                        <td><?= esc($remark) ?></td>
                        <td>
                            <span class="role-badge" data-role="<?= esc($remarkStatus) ?>"><?= esc($remarkStatus) ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center">No reports found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal to Display Detailed Report Info -->
<div class="modal fade" id="viewReportModal" tabindex="-1" aria-labelledby="viewReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Report Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-header">Report Details</div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Left Column: Image -->
                            <div class="col-md-6 text-center" id="imageCol">
                                <img id="reportImage" src="" alt="Report Image" class="img-fluid">
                            </div>
                            <!-- Right Column: Detailed Report Info -->
                            <div class="col-md-6" id="detailsCol">
                                <div id="tripTicketDetails" style="display: none;">
                                    <h5>Trip Ticket Details</h5>
                                    <p><strong>Report Number:</strong> <span id="ticketReportNumber"></span></p>
                                    <p><strong>Report Type:</strong> <span id="ticketReportType"></span></p>
                                    <p><strong>Date:</strong> <span id="ticketDate"></span></p>
                                    <p><strong>Booking ID:</strong> <span id="ticketBookingId"></span></p>
                                    <p><strong>Driver Name:</strong> <span id="reportDriver"></span></p>
                                    <p><strong>Plate Number:</strong> <span id="reportPlate"></span></p>
                                    <p><strong>Origin:</strong> <span id="reportOrigin"></span></p>
                                    <p><strong>Destination:</strong> <span id="reportDestination"></span></p>
                                    <p><strong>Trip Date:</strong> <span id="reportTripDate"></span></p>
                                    <p><strong>Trip Time:</strong> <span id="reportTripTime"></span></p>
                                    <p><strong>Cargo Details:</strong> <span id="reportCargoDetails"></span></p>
                                </div>
                                <div id="maintenanceDetails" style="display: none;">
                                    <h5>Maintenance Report Details</h5>
                                    <p><strong>Report Number:</strong> <span id="maintReportNumber"></span></p>
                                    <p><strong>Report Type:</strong> <span id="maintReportType"></span></p>
                                    <p><strong>Date:</strong> <span id="maintDate"></span></p>
                                    <p><strong>Truck ID:</strong> <span id="maintTruckId"></span></p>
                                    <p><strong>Inspection Date:</strong> <span id="maintInspectionDate"></span></p>
                                    <p><strong>Action Needed:</strong> <span id="maintActionNeeded"></span></p>
                                    <p><strong>Service Type:</strong> <span id="maintServiceType"></span></p>
                                    <p><strong>Technician Name:</strong> <span id="maintTechnician"></span></p>
                                    <p><strong>Mileage After Inspection:</strong> <span id="maintMileageAfter"></span></p>
                                    <p><strong>Estimate Next Service (Mileage):</strong> <span id="maintEstimateNext"></span></p>
                                    <p><strong>Expected Next Service (Time):</strong> <span id="maintExpectedNext"></span></p>
                                </div>
                            </div><!-- end detailsCol -->
                        </div><!-- end row -->
                    </div><!-- end card-body -->
                </div><!-- end card -->
            </div><!-- end modal-body -->
        </div><!-- end modal-content -->
    </div><!-- end modal-dialog -->
</div><!-- end modal -->

<script>
    const viewReportModal = document.getElementById('viewReportModal');
    viewReportModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const imgUrl = button.getAttribute('data-img');
        const reportType = button.getAttribute('data-type');
        const reportNumber = button.getAttribute('data-reportnumber') || '';
        const date = button.getAttribute('data-date') || '';
        const bookingId = button.getAttribute('data-bookingid') || '';

        document.getElementById('reportImage').src = imgUrl;

        const imageCol = document.getElementById('imageCol');
        const detailsCol = document.getElementById('detailsCol');
        const tripTicketDetails = document.getElementById('tripTicketDetails');
        const maintenanceDetails = document.getElementById('maintenanceDetails');

        // Hide both detail sections initially
        tripTicketDetails.style.display = 'none';
        maintenanceDetails.style.display = 'none';
        detailsCol.style.display = 'block';
        imageCol.className = 'col-md-6 text-center';
        detailsCol.className = 'col-md-6';

        if (reportType === 'Trip Ticket') {
            tripTicketDetails.style.display = 'block';
            document.getElementById('ticketReportNumber').textContent = reportNumber;
            document.getElementById('ticketReportType').textContent = reportType;
            document.getElementById('ticketDate').textContent = date;
            document.getElementById('ticketBookingId').textContent = bookingId;
            document.getElementById('reportDriver').textContent = button.getAttribute('data-driver') || '';
            document.getElementById('reportPlate').textContent = button.getAttribute('data-plate') || '';
            document.getElementById('reportOrigin').textContent = button.getAttribute('data-origin') || '';
            document.getElementById('reportDestination').textContent = button.getAttribute('data-destination') || '';
            document.getElementById('reportTripDate').textContent = button.getAttribute('data-tripdate') || '';
            document.getElementById('reportTripTime').textContent = button.getAttribute('data-triptime') || '';
            document.getElementById('reportCargoDetails').textContent = button.getAttribute('data-cargodetails') || '';
        } else if (reportType === 'Maintenance Report') {
            maintenanceDetails.style.display = 'block';
            document.getElementById('maintReportNumber').textContent = reportNumber;
            document.getElementById('maintReportType').textContent = reportType;
            document.getElementById('maintDate').textContent = date;
            document.getElementById('maintTruckId').textContent = button.getAttribute('data-truckid') || '';
            document.getElementById('maintInspectionDate').textContent = button.getAttribute('data-inspectiondate') || '';
            document.getElementById('maintActionNeeded').textContent = button.getAttribute('data-actionneeded') || '';
            document.getElementById('maintServiceType').textContent = button.getAttribute('data-servicetype') || '';
            document.getElementById('maintTechnician').textContent = button.getAttribute('data-technician') || '';
            document.getElementById('maintMileageAfter').textContent = button.getAttribute('data-mileageafter') || '';
            document.getElementById('maintEstimateNext').textContent = button.getAttribute('data-estimate') || '';
            document.getElementById('maintExpectedNext').textContent = button.getAttribute('data-expected') || '';
        } else {
            // Fallback: only show the image if the type is unrecognized
            detailsCol.style.display = 'none';
            imageCol.className = 'col-md-12 text-center';
        }
    });

    // Filter function for the reports table
    function filterReports() {
        const searchReportNumber = document.getElementById('searchReportNumber').value.toLowerCase().trim();
        const filterReportType = document.getElementById('filterReportType').value.toLowerCase().trim();
        const rows = document.querySelectorAll('#reportsTable tbody tr');

        rows.forEach((row) => {
            const reportNumber = row.cells[0].textContent.toLowerCase().trim();
            const reportType = row.cells[1].textContent.toLowerCase().trim();
            const matchesReportNumber = reportNumber.indexOf(searchReportNumber) > -1;
            const matchesReportType = filterReportType ? (reportType === filterReportType) : true;
            row.style.display = (matchesReportNumber && matchesReportType) ? '' : 'none';
        });
    }

    document.getElementById('searchReportNumber').addEventListener('keyup', filterReports);
    document.getElementById('filterReportType').addEventListener('change', filterReports);

    document.addEventListener('DOMContentLoaded', () => {
    const badges = document.querySelectorAll('.role-badge');

    badges.forEach(badge => {
        const role = badge.getAttribute('data-role');

        badge.classList.remove('badge-pending', 'badge-approved', 'badge-intransit', 'badge-rejected', 'badge-completed');

        switch (role) {
            case 'Pending':
                badge.classList.add('badge-pending');
                break;
            case 'Approved':
                badge.classList.add('badge-approved');
                break;
            case 'In-transit':
                badge.classList.add('badge-intransit');
                break;
            case 'Rejected':
                badge.classList.add('badge-rejected');
                break;
            case 'Completed':
                badge.classList.add('badge-completed');
                break;
            default:
                break;
        }
    });
});
</script>

<?= $this->endSection() ?>
