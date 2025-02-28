<?= $this->extend('templates/operations_coordinator_layout') ?>

<?= $this->section('content') ?>
<link href=<?=base_url('public/assets/css/style.css');?> rel="stylesheet">
<body>
<title>Booking Management</title>
<h1>Booking Management</h1>


    <div class="container mt-4">
        <h2>View Booking</h2>
        <ul class="nav nav-pills mb-3" id="bookingTabs">
            <li class="nav-item">
                <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending">
                    Pending Booking
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved">
                    Approved Booking
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Pending Bookings -->
            <div class="tab-pane fade show active" id="pending">
                <table class="table table-bordered table-hover">
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
                    <tbody id="pendingBookings">
                        <tr>
                            <td>Fresh Farms Corporation</td>
                            <td>2024-11-08</td>
                            <td>2024-11-23</td>
                            <td>Fresh Produce</td>
                            <td>Pasay</td>
                            <td>
                                <button class="btn btn-info btn-sm view-btn" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#bookingModal"
                                    data-client="Fresh Farms Corporation"
                                    data-booking="2024-11-08"
                                    data-dispatch="2024-11-23"
                                    data-cargo="Fresh Produce"
                                    data-dropoff="Pasay">
                                    View
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Approved Bookings -->
            <div class="tab-pane fade" id="approved">
                <table class="table table-bordered table-hover">
                    <thead class="table-success text-dark">
                        <tr>
                            <th>Client Name</th>
                            <th>Booking Date</th>
                            <th>Dispatch Date</th>
                            <th>Cargo Type</th>
                            <th>Drop-off Location</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="approvedBookings">
                        <!-- Approved bookings data will be inserted dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Booking Details Modal -->
    <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="bookingModalLabel">Booking Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h4 class="fw-bold">Load Assignment</h4>
                    <p><strong>Client Name:</strong> <span id="modalClient"></span></p>
                    <p><strong>Booking Date:</strong> <span id="modalBooking"></span></p>
                    <p><strong>Dispatch Date:</strong> <span id="modalDispatch"></span></p>
                    <p><strong>Cargo Type:</strong> <span id="modalCargo"></span></p>
                    <p><strong>Drop Off Location:</strong> <span id="modalDropoff"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" id="approveBooking">Approve</button>
                    <button type="button" class="btn btn-danger" id="declineBooking">Decline</button>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= base_url('/public/assets/js/script.js') ?>"></script>

<?= $this->endSection() ?>
