<?= $this->extend('templates/layout.php') ?>

<?= $this->section('content') ?>
<link href=<?=base_url('public/assets/css/style.css');?> rel="stylesheet">
<h1>Client Management</h1>

<div class="content">
    <div class="client-list">
        <h2>Client list</h2>
        <table>
            <thead>
                <tr>
                    <th>Client Name</th>
                    <th>Booking Date</th>
                    <th>Dispatch Date</th>
                    <th>Cargo Type</th>
                    <th>Drop-off Location</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                <tr>
                    <td><?= $client['name'] ?></td>
                    <td><?= $client['booking_date'] ?></td>
                    <td><?= $client['dispatch_date'] ?></td>
                    <td><?= $client['cargo_type'] ?></td>
                    <td><?= $client['drop_off'] ?></td>
                    <td><?= $client['status'] ?></td>
                    <td><button type="button" class="btn btn-secondary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight"><a href="#" class="view-client" data-id="<?= $client['id'] ?>">View</a></button>
                        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
                        <div class="offcanvas-header">
                            <h5 class="offcanvas-title" id="offcanvasRightLabel">Client Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            <!-- Client Details Section -->
                            <div id="client-info">
                                <p><strong>CLIENT NAME:</strong> <span id="client-name"></span></p>
                                <p><strong>EMAIL:</strong> <span id="client-email"></span></p>
                                <p><strong>ADDRESS:</strong> <span id="client-address"></span></p>
                                <p><strong>BUSINESS TYPE:</strong> <span id="client-business"></span></p>
                                <p><strong>CARGO TYPE:</strong> <span id="client-cargo"></span></p>
                                <p><strong>PICK-UP LOCATION:</strong> <span id="client-pickup"></span></p>
                                <p><strong>DROP-OFF LOCATION:</strong> <span id="client-dropoff"></span></p>
                                <p><strong>CLIENT SINCE:</strong> <span id="client-since"></span></p>
                                <p><strong>NOTES:</strong> <textarea id="client-notes"></textarea></p>
                            </div>
                        </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<!-- JavaScript to Handle Click Event -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".view-client").forEach(button => {
        button.addEventListener("click", function(event) {
            event.preventDefault();
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
