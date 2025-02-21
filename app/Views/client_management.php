<?= $this->extend('templates/layout.php') ?>

<?= $this->section('content') ?>
<link href=<?=base_url('public/assets/css/style.css');?> rel="stylesheet">
<title>Client Management</title>
<h1>Client Management</h1>

<div class="content">
    <div class="client-list">
        <h2>Client list</h2>
        <table class="table table-bordered table-hover align-middle text-center">
            <thead class="table-light text-dark">
                <tr>
                    <th>Client Name</th>
                    <th>Booking Date</th>
                    <th>Dispatch Date</th>
                    <th>Cargo Type</th>
                    <th>Drop-off Location</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                <tr>
                    <td><?= esc($client['name']) ?></td>
                    <td><?= esc($client['booking_date']) ?></td>
                    <td><?= esc($client['dispatch_date']) ?></td>
                    <td><?= esc($client['cargo_type']) ?></td>
                    <td><?= esc($client['drop_off']) ?></td>
                    <td>
                        <button type="button" class="btn btn-warning btn-sm fw-bold px-4 view-client text-dark"
                                data-bs-toggle="modal" 
                                data-bs-target="#clientDetailsModal"
                                data-id="<?= esc($client['id']) ?>">
                            View
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Client Details Modal -->
<div class="modal fade" id="clientDetailsModal" tabindex="-1" aria-labelledby="clientDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-center w-100" id="offcanvasRightLabel">Client Details</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="client-info">
                    <p><strong>Client Name:</strong> <span id="client-name"></span></p>
                    <p><strong>Email:</strong> <span id="client-email"></span></p>
                    <p><strong>Address:</strong> <span id="client-address"></span></p>
                    <p><strong>Business Type:</strong> <span id="client-business"></span></p>
                    <p><strong>Cargo Type:</strong> <span id="client-cargo"></span></p>
                    <p><strong>Pick-up Location:</strong> <span id="client-pickup"></span></p>
                    <p><strong>Drop-off Location:</strong> <span id="client-dropoff"></span></p>
                    <p><strong>Client Since:</strong> <span id="client-since"></span></p>
                    <p><strong>Notes:</strong> <textarea id="client-notes" class="form-control" rows="3"></textarea></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-warning" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript to Fetch Client Details -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".view-client").forEach(button => {
        button.addEventListener("click", function() {
            let clientId = this.getAttribute("data-id");

            fetch("<?= base_url('clients/details/') ?>" + clientId)
                .then(response => response.json())
                .then(data => {
                    document.getElementById("client-name").innerText = data.name;
                    document.getElementById("client-email").innerText = data.email;
                    document.getElementById("client-address").innerText = data.address;
                    document.getElementById("client-business").innerText = data.business_type;
                    document.getElementById("client-cargo").innerText = data.cargo_type;
                    document.getElementById("client-pickup").innerText = data.pickup_location;
                    document.getElementById("client-dropoff").innerText = data.drop_off;
                    document.getElementById("client-since").innerText = data.client_since;
                    document.getElementById("client-notes").value = data.notes || "";
                })
                .catch(error => console.error("Error fetching client details:", error));
        });
    });
});
</script>


<?= $this->endSection() ?>
