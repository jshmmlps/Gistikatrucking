<?= $this->extend('templates/operations_coordinator_layout') ?>

<?= $this->section('content') ?>
<link href="<?= base_url('public/assets/css/style.css'); ?>" rel="stylesheet">
<title>Reports Management</title>

<div class="container-fluid mt-4">
  <h1>Reports Management</h1>

  <?php if(session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
  <?php endif; ?>
  <?php if(session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
  <?php endif; ?>

  <div class="row mb-3">
    <div class="col-md-6 mb-2 mb-md-0">
      <input type="text" class="form-control" id="searchReportNumber" placeholder="Search by Report Number">
    </div>
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
        <th>Manage</th>
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
              <td><?= esc($remarkStatus) ?></td>
              <td>
                <button class="btn btn-secondary btn-sm manageRemarkBtn"
                        data-bs-toggle="modal"
                        data-bs-target="#remarkModal"
                        data-report="<?= esc($reportNumber) ?>"
                        data-remark="<?= esc($remark) ?>"
                        data-status="<?= esc($remarkStatus) ?>">
                  Manage Remark
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="8" class="text-center">No reports found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="modal fade" id="viewReportModal" tabindex="-1" aria-labelledby="viewReportModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewReportModalLabel">Report Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="card">
          <div class="card-header">Report Details</div>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6 text-center" id="imageCol">
                <img id="reportImage" src="" alt="Report Image" class="img-fluid">
              </div>
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
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="remarkModal" tabindex="-1" aria-labelledby="remarkModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?= base_url('operations/reports/saveRemark') ?>" method="post">
        <div class="modal-header">
          <h5 class="modal-title" id="remarkModalLabel">Manage Remark</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="report_number" id="remarkReportNumber">
          <div class="mb-3">
            <label for="remarkText" class="form-label">Remark</label>
            <textarea class="form-control" id="remarkText" name="remark" rows="4" placeholder="Enter remark here..."></textarea>
          </div>
          <div class="mb-3">
            <label for="remarkStatus" class="form-label">Status</label>
            <select class="form-select" id="remarkStatus" name="remark_status">
              <option value="Pending" selected>Pending</option>
              <option value="Approved">Approved</option>
              <option value="Rejected">Rejected</option>
            </select>
          </div>
          <div class="text-muted">
            Leave the remark blank to remove any existing remark.
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="action" value="delete" class="btn btn-danger">Delete</button>
          <button type="submit" name="action" value="save" class="btn btn-primary">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
          detailsCol.style.display = 'none';
          imageCol.className = 'col-md-12 text-center';
      }
  });

  const remarkModal = document.getElementById('remarkModal');
  remarkModal.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      const reportNumber = button.getAttribute('data-report');
      const remark = button.getAttribute('data-remark') || '';
      const status = button.getAttribute('data-status') || 'Pending';
      document.getElementById('remarkReportNumber').value = reportNumber;
      document.getElementById('remarkText').value = remark;
      document.getElementById('remarkStatus').value = status;
  });

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
</script>

<?= $this->endSection() ?>
