<?= $this->extend('templates/admin_layout.php') ?>

<?= $this->section('content') ?>
<link href="<?= base_url('public/assets/css/style.css'); ?>" rel="stylesheet">
<!-- DataTables Bootstrap 5 CSS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css"/>

<body>
<title>Client Management</title>
<h1>Client Management</h1>

<div class="container-fluid mt-4">
    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <!-- The table ID "clientTable" is used by DataTables -->
    <table id="clientTable" class="table table-bordered table-striped">
        <thead>
            <tr>
                <!-- Hidden column for sorting by booking existence -->
                <th class="d-none">HasBooking</th>
                <th>Client Name</th>
                <th>Booking Date</th>
                <th>Dispatch Date</th>
                <th>Cargo Type</th>
                <th>Drop-off Location</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if(!empty($clients)): ?>
            <?php foreach($clients as $client): ?>
            <tr>
                <!-- Hidden cell: 1 if booking exists, 0 if not -->
                <td class="d-none"><?= ($client['booking_date'] !== 'N/A') ? 1 : 0 ?></td>
                <td><?= esc($client['clientName']) ?></td>
                <td><?= esc($client['booking_date']) ?></td>
                <td><?= esc($client['dispatch_date']) ?></td>
                <td><?= esc($client['cargo_type']) ?></td>
                <td><?= esc($client['drop_off_address']) ?></td>
                <td><?= esc($client['status']) ?></td>
                <td>
                    <button type="button" class="btn btn-info btn-sm view-client" data-client-id="<?= esc($client['clientId']) ?>">View</button>
                    <button type="button" class="btn btn-warning btn-sm edit-client" data-client-id="<?= esc($client['clientId']) ?>">Edit</button>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8" class="text-center">No clients found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Single Modal for Client Details -->
<div class="modal fade" id="clientViewModal" tabindex="-1" aria-labelledby="clientViewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="clientViewModalLabel">Client Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="clientViewBody">
        <!-- Content will be populated dynamically -->
      </div>
      <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Single Modal for Client Edit -->
<div class="modal fade" id="clientEditModal" tabindex="-1" aria-labelledby="clientEditModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <form id="clientEditForm" method="post" action="<?= site_url('admin/clientEdit') ?>"> 
      <!-- The clientId will be appended to the form action on click -->
      <div class="modal-header">
        <h5 class="modal-title text-center w-100" id="clientEditModalLabel">Edit Client</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="clientEditBody">
          <?= csrf_field() ?>
          <div class="mb-3">
              <label for="edit_business_type" class="form-label">Business Type</label>
              <input type="text" class="form-control" name="business_type" id="edit_business_type" value="">
          </div>
          <div class="mb-3">
              <label for="edit_payment_mode" class="form-label">Payment Mode</label>
              <input type="text" class="form-control" name="payment_mode" id="edit_payment_mode" value="">
          </div>
      </div>
      <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Update</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
      </form>
    </div>
  </div>
</div>


<!-- Embed detailed client data as a JavaScript variable -->
<script>
var clientDetails = <?= json_encode($clientDetails) ?>;
</script>

<!-- jQuery and DataTables scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
// Initialize DataTables for pagination, search, and sorting
$(document).ready(function() {
    $('#clientTable').DataTable({
        pageLength: 10,
        // Order by the hidden "HasBooking" column descending, then Client Name ascending
        order: [[0, 'desc'], [1, 'asc']],
        // Optional: if you want to disable ordering on the Actions column (last column)
        columnDefs: [
            { orderable: false, targets: 7 }
        ]
    });
});

document.addEventListener('DOMContentLoaded', function() {
    // --- Populate and show Client Details Modal ---
    document.querySelectorAll('.view-client').forEach(function(button) {
        button.addEventListener('click', function() {
            var clientId = this.getAttribute('data-client-id');
            var data = clientDetails[clientId];
            if (data) {
                var client = data.client;
                var lastBooking = data.lastBooking;
                var html = '';
                html += '<div class="p-3 rounded-3 shadow-sm bg-light"><h6 class="fw-bold mb-3 text-primary">Client Information</h6><div class="d-flex flex-column gap-3"><div class="d-flex justify-content-between border-bottom pb-2"><span class="fw-bold text-secondary">Email:</span><span class="text-muted">' + (client.email ? client.email : 'N/A') + '</span></div>';
                html += '<div class="d-flex justify-content-between border-bottom pb-2"><span class="fw-bold text-secondary">Address:</span><span class="text-muted">' + (client.address ? client.address : 'N/A') + '</span></div>';
                html += '<div class="d-flex justify-content-between border-bottom pb-2"><span class="fw-bold text-secondary">Business Type:</span><span class="text-muted">' + (client.business_type ? client.business_type : 'N/A') + '</span></div>';
                html += '<div class="d-flex justify-content-between border-bottom pb-2"><span class="fw-bold text-secondary">Cargo Type:</span><span class="text-muted">' + (lastBooking && lastBooking.cargo_type ? lastBooking.cargo_type : 'N/A') + '</span></div>';
                html += '<div class="d-flex justify-content-between border-bottom pb-2"><span class="fw-bold text-secondary">Pick up Location:</span><span class="text-muted">' + (lastBooking && lastBooking.pick_up_address ? lastBooking.pick_up_address : 'N/A') + '</span></div>';
                html += '<div class="d-flex justify-content-between border-bottom pb-2"><span class="fw-bold text-secondary">Contact Person:</span><span class="text-muted">' + (lastBooking && lastBooking.person_of_contact ? lastBooking.person_of_contact : 'N/A') + '</span></div>';
                html += '<div class="d-flex justify-content-between border-bottom pb-2"><span class="fw-bold text-secondary">Contact Number:</span><span class="text-muted">' + (lastBooking && lastBooking.contact_number ? lastBooking.contact_number : 'N/A') + '</span></div>';
                html += '<div class="d-flex justify-content-between border-bottom pb-2"><span class="fw-bold text-secondary">Username:</span><span class="text-muted">' + (client.username ? client.username : 'N/A') + '</span></div>';
                html += '<div class="d-flex justify-content-between border-bottom pb-2"><span class="fw-bold text-secondary">Preferred Truck:</span><span class="text-muted">' + (lastBooking && lastBooking.truck_model ? lastBooking.truck_model : 'N/A') + '</span></div>';
                html += '<div class="d-flex justify-content-between border-bottom pb-2"><span class="fw-bold text-secondary">Payment Mode:</span><span class="text-muted">' + (client.payment_mode ? client.payment_mode : 'N/A') + '</span></div>';
                html += '<div class="d-flex justify-content-between border-bottom pb-2"><span class="fw-bold text-secondary">Drop off Location:</span><span class="text-muted">' + (lastBooking && lastBooking.drop_off_address ? lastBooking.drop_off_address : 'N/A') + '</span></div>';
                html += '<div class="d-flex justify-content-between"><span class="fw-bold text-secondary">Client Since:</span><span class="text-muted">' + (lastBooking && lastBooking.booking_date ? lastBooking.booking_date : 'N/A') + '</span></div></div></div>';
                document.getElementById('clientViewBody').innerHTML = html;
                var viewModal = new bootstrap.Modal(document.getElementById('clientViewModal'));
                viewModal.show();
            }
        });
    });

    // --- Populate and show Client Edit Modal ---
    document.querySelectorAll('.edit-client').forEach(function(button) {
        button.addEventListener('click', function() {
            var clientId = this.getAttribute('data-client-id');
            var data = clientDetails[clientId];
            if (data) {
                var client = data.client;
                document.getElementById('edit_business_type').value = client.business_type ? client.business_type : '';
                document.getElementById('edit_payment_mode').value = client.payment_mode ? client.payment_mode : '';
                // Update the form action to include the clientId
                document.getElementById('clientEditForm').action = "<?= site_url('admin/clientEdit') ?>/" + clientId;
                var editModal = new bootstrap.Modal(document.getElementById('clientEditModal'));
                editModal.show();
            }
        });
    });

    // --- Handle AJAX submission for the Edit Form ---
    document.getElementById('clientEditForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var form = this;
        var url = form.action;
        var formData = new FormData(form);
        fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                var editModalEl = document.getElementById('clientEditModal');
                var modal = bootstrap.Modal.getInstance(editModalEl);
                modal.hide();
                location.reload();
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
