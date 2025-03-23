<?= $this->extend('templates/admin_layout.php') ?>

<?= $this->section('content') ?>
<link href="<?= base_url('public/assets/css/style.css'); ?>" rel="stylesheet">
<style>
    /* Define custom badge colors */
    .badge-admin {
        background-color: red !important;
        color: white;
    }
    .badge-resource {
        background-color: #fd7e14 !important;
        color: white;
    }
    .badge-operation {
        background-color: #ffc107 !important;
        color: white;
    }
    .badge-driver, .badge-conductor {
        background-color: #4AA15D !important;
        color: white;
    }
</style>
<body>
<title>User Account</title>
<h1>User Account</h1>

<div class="container-fluid mt-5">
    <!-- Flash messages (success/failure) -->
    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('message') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <!-- Toolbar: Create button + Sort dropdown -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal" style="background-color:#336699">
            <i class="bi bi-person-plus"></i> Create User
        </button>
        <div>
            <label class="me-2 fw-bold">Sort by:</label>
            <select id="sortSelect" class="form-select d-inline-block w-auto">
                <option value="alpha">Alphabetical</option>
                <option value="role">Role (Admin, Resource, Operation, Client)</option>
            </select>
        </div>
    </div>

    <!-- Entries per page dropdown -->
    <div class="mb-3">
        <label class="me-2 fw-bold">Entries per page:</label>
        <select id="entriesSelect" class="form-select d-inline-block w-auto">
            <option value="5" selected>5</option>
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
        </select>
    </div>
    
    <!-- Users List (cards) -->
    <div class="row" id="userCardsContainer">
        <?php if (!empty($users) && is_array($users)): ?>
            <?php foreach ($users as $userKey => $userData): ?>
                <?php
                    // Safely retrieve user fields or fallback to empty
                    $firstName = $userData['first_name'] ?? '';
                    $lastName  = $userData['last_name'] ?? '';
                    $username  = $userData['username'] ?? '';
                    $userLevel = $userData['user_level'] ?? '';

                    // Check if user has a profile picture or use default
                    $profilePicture = $userData['profile_picture'] ?? base_url('public/images/default.jpg');
                ?>
                <div class="col-md-4 mb-4 userCard"
                     data-alpha="<?= strtolower($firstName . ' ' . $lastName) ?>"
                     data-role="<?= strtolower($userLevel) ?>"
                     data-profile="<?= esc($profilePicture) ?>"
                     data-username="<?= esc($username) ?>"
                     data-firstname="<?= esc($firstName) ?>"
                     data-lastname="<?= esc($lastName) ?>">
                    <!-- Card -->
                    <div class="card position-relative">
                        <!-- Card image -->
                        <div class="card-img-top-container">
                            <img src="<?= esc($profilePicture) ?>" class="circular-image" alt="User Image">
                        </div>
                        <!-- Dropdown (kebab menu) in top-right -->
                        <div class="position-absolute top-0 end-0 m-2">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <button class="dropdown-item btnEditUser"
                                                data-user-key="<?= $userKey ?>"
                                                data-user="<?= htmlspecialchars(json_encode($userData), ENT_QUOTES, 'UTF-8') ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editUserModal">
                                            Edit
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item btnDeleteUser"
                                                data-user-key="<?= $userKey ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteUserModal">
                                            Delete
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <!-- Card body -->
                        <div class="card-body">
                            <h5 class="card-title"><?= esc($firstName) . ' ' . esc($lastName) ?></h5>
                            <p class="card-text mb-1"><strong>Username:</strong> <?= esc($username) ?></p>
                            <p class="card-text">
                                <strong>Role:</strong>
                                <span id="roleBadge" class="badge bg-info"><?= esc($userLevel) ?></span>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No users found.</p>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between mt-4">
        <nav id="paginationControls" class="mt-3">
            <!-- This will be populated dynamically using JS -->
        </nav>
    </div>
</div>

<!-- ======================== Create User Modal ======================== -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="<?= base_url('admin/users/create') ?>" method="post">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createUserModalLabel">Create User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Basic User Fields -->
                    <div class="mb-3">
                        <label for="create_first_name" class="form-label">First Name</label>
                        <input type="text" name="first_name" id="create_first_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_last_name" class="form-label">Last Name</label>
                        <input type="text" name="last_name" id="create_last_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_email" class="form-label">Email</label>
                        <input type="email" name="email" id="create_email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_username" class="form-label">Username</label>
                        <input type="text" name="username" id="create_username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_contact_number" class="form-label">Contact Number</label>
                        <input type="text" name="contact_number" id="create_contact_number" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="create_address" class="form-label">Address</label>
                        <input type="text" name="address" id="create_address" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="create_birthday" class="form-label">Birthday</label>
                        <input type="date" name="birthday" id="create_birthday" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="create_gender" class="form-label">Gender</label>
                        <select name="gender" id="create_gender" class="form-select">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="create_user_level" class="form-label">User Level</label>
                        <select name="user_level" id="create_user_level" class="form-select">
                            <option value="admin">admin</option>
                            <option value="resource manager">resource manager</option>
                            <option value="operation manager">operation manager</option>
                            <option value="client">client</option>
                            <option value="driver">driver</option>
                            <option value="conductor">conductor</option>
                        </select>
                    </div>
                    <!-- <div class="mb-3">
                        <label for="create_address_dropoff" class="form-label">Address Dropoff</label>
                        <input type="text" name="address_dropoff" id="create_address_dropoff" class="form-control">
                    </div> -->
                    <!-- Password Fields -->
                    <div class="mb-3">
                        <label for="create_password" class="form-label">Password</label>
                        <input type="password" name="password" id="create_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="create_confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" id="create_confirm_password" class="form-control" required>
                    </div>

                    <!-- Driver/Conductor Specific Fields (hidden by default) -->
                    <div id="driverFields" style="display:none;">
                        <hr>
                        <h6>Driver/Conductor Details</h6>
                        <div class="mb-3">
                            <label for="create_employee_id" class="form-label">Employee ID</label>
                            <input type="text" name="employee_id" id="create_employee_id" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="create_date_of_employment" class="form-label">Date of Employment</label>
                            <input type="date" name="date_of_employment" id="create_date_of_employment" class="form-control">
                        </div>
                        <!-- Truck Assigned field removed as truck assignment is handled separately -->
                        <div class="mb-3">
                            <label for="create_license_number" class="form-label">License Number</label>
                            <input type="text" name="license_number" id="create_license_number" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="create_license_expiry" class="form-label">License Expiry</label>
                            <input type="date" name="license_expiry" id="create_license_expiry" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="create_medical_record" class="form-label">Medical Record</label>
                            <input type="text" name="medical_record" id="create_medical_record" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="create_trips_completed" class="form-label">Trips Completed</label>
                            <input type="number" name="trips_completed" id="create_trips_completed" class="form-control" value="0">
                        </div>
                    </div>
                    <!-- End Driver/Conductor Fields -->

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Script to show/hide driver fields in Create Modal based on user level -->
<script>
    document.getElementById('create_user_level').addEventListener('change', function(){
        var driverFields = document.getElementById('driverFields');
        if (this.value === 'driver' || this.value === 'conductor') {
            driverFields.style.display = 'block';
        } else {
            driverFields.style.display = 'none';
        }
    });
</script>

<!-- ======================== Edit User Modal ======================== -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <!-- We dynamically set the action in JavaScript because we need the user key -->
        <form id="editUserForm" method="post">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Basic User Fields -->
                    <input type="hidden" name="userKey" id="edit_user_key">
                    <div class="mb-3">
                        <label for="edit_first_name" class="form-label">First Name</label>
                        <input type="text" name="first_name" id="edit_first_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_last_name" class="form-label">Last Name</label>
                        <input type="text" name="last_name" id="edit_last_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_username" class="form-label">Username</label>
                        <input type="text" name="username" id="edit_username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_contact_number" class="form-label">Contact Number</label>
                        <input type="text" name="contact_number" id="edit_contact_number" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="edit_address" class="form-label">Address</label>
                        <input type="text" name="address" id="edit_address" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="edit_birthday" class="form-label">Birthday</label>
                        <input type="date" name="birthday" id="edit_birthday" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="edit_gender" class="form-label">Gender</label>
                        <select name="gender" id="edit_gender" class="form-select">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_user_level" class="form-label">User Level</label>
                        <select name="user_level" id="edit_user_level" class="form-select">
                            <option value="admin">admin</option>
                            <option value="resource manager">resource manager</option>
                            <option value="operation manager">operation manager</option>
                            <option value="client">client</option>
                            <option value="driver">driver</option>
                            <option value="conductor">conductor</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_address_dropoff" class="form-label">Address Dropoff</label>
                        <input type="text" name="address_dropoff" id="edit_address_dropoff" class="form-control">
                    </div>
                    
                    <!-- Driver/Conductor Specific Fields for Edit (hidden by default) -->
                    <div id="editDriverFields" style="display:none;">
                        <hr>
                        <h6>Driver/Conductor Details</h6>
                        <div class="mb-3">
                            <label for="edit_employee_id" class="form-label">Employee ID</label>
                            <input type="text" name="employee_id" id="edit_employee_id" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="edit_date_of_employment" class="form-label">Date of Employment</label>
                            <input type="date" name="date_of_employment" id="edit_date_of_employment" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="edit_license_number" class="form-label">License Number</label>
                            <input type="text" name="license_number" id="edit_license_number" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="edit_license_expiry" class="form-label">License Expiry</label>
                            <input type="date" name="license_expiry" id="edit_license_expiry" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="edit_medical_record" class="form-label">Medical Record</label>
                            <input type="text" name="medical_record" id="edit_medical_record" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="edit_trips_completed" class="form-label">Trips Completed</label>
                            <input type="number" name="trips_completed" id="edit_trips_completed" class="form-control" value="0">
                        </div>
                    </div>
                    <!-- End Driver/Conductor Fields for Edit -->

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ======================== Delete User Modal ======================== -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="deleteUserForm" method="post">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteUserModalLabel">Delete User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this user?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Sorting functionality
    const sortSelect = document.getElementById('sortSelect');
    sortSelect.addEventListener('change', function() {
        const sortMode = this.value;
        const container = document.getElementById('userCardsContainer');
        const cards = Array.from(container.getElementsByClassName('userCard'));

        if (sortMode === 'alpha') {
            // Alphabetical sort by data-alpha attribute (firstName + lastName).
            cards.sort((a, b) => {
                let nameA = a.getAttribute('data-alpha').toLowerCase();
                let nameB = b.getAttribute('data-alpha').toLowerCase();
                return nameA.localeCompare(nameB);
            });
        } else if (sortMode === 'role') {
            // Define a custom role order. Ensure these match the roles in your data exactly (case-insensitive).
            const roleOrder = [
                'admin',
                'resource manager',
                'operation manager',
                'operations coordinator',
                'client',
                'driver',
                'conductor',
            ];

            cards.sort((a, b) => {
                let roleA = a.getAttribute('data-role').toLowerCase();
                let roleB = b.getAttribute('data-role').toLowerCase();

                let indexA = roleOrder.indexOf(roleA);
                let indexB = roleOrder.indexOf(roleB);

                // If role not in array, place it last
                if (indexA === -1 && indexB === -1) {
                    return 0;
                } else if (indexA === -1) {
                    return 1;
                } else if (indexB === -1) {
                    return -1;
                } else {
                    return indexA - indexB;
                }
            });
        }
        // Re-append the sorted cards
        cards.forEach(card => container.appendChild(card));
    });

    // Pagination code
    const userCardsContainer = document.getElementById("userCardsContainer");
    const paginationControls = document.getElementById("paginationControls");
    let allUsers = Array.from(userCardsContainer.children);
    let currentPage = 1;
    let usersPerPage = document.getElementById("entriesSelect").value;

    function paginate() {
        const totalUsers = allUsers.length;
        const totalPages = Math.ceil(totalUsers / usersPerPage);

        allUsers.forEach(user => user.style.display = "none");

        const start = (currentPage - 1) * usersPerPage;
        const end = start + usersPerPage;
        for (let i = start; i < end && i < totalUsers; i++) {
            allUsers[i].style.display = "block";
        }

        let paginationHtml = '<ul class="pagination">';
        for (let i = 1; i <= totalPages; i++) {
            paginationHtml += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="goToPage(${i})">${i}</a>
            </li>`;
        }
        paginationHtml += '</ul>';
        paginationControls.innerHTML = paginationHtml;
    }

    function goToPage(page) {
        currentPage = page;
        paginate();
    }

    document.getElementById("entriesSelect").addEventListener("change", function() {
        usersPerPage = parseInt(this.value);
        paginate();
    });

    paginate();

    // Edit modal script: pre-fill basic fields and toggle driver fields display
    document.querySelectorAll('.btnEditUser').forEach(btn => {
        btn.addEventListener('click', function() {
            const userKey = this.getAttribute('data-user-key');
            const userDataRaw = this.getAttribute('data-user');
            const userData = JSON.parse(userDataRaw);

            document.getElementById('edit_user_key').value = userKey;
            document.getElementById('edit_first_name').value = userData.first_name || '';
            document.getElementById('edit_last_name').value = userData.last_name || '';
            document.getElementById('edit_email').value = userData.email || '';
            document.getElementById('edit_username').value = userData.username || '';
            document.getElementById('edit_contact_number').value = userData.contact_number || '';
            document.getElementById('edit_address').value = userData.address || '';
            document.getElementById('edit_birthday').value = userData.birthday || '';
            document.getElementById('edit_gender').value = userData.gender || '';
            document.getElementById('edit_user_level').value = userData.user_level || '';
            document.getElementById('edit_address_dropoff').value = userData.address_dropoff || '';

            // Pre-fill driver fields if they exist
            document.getElementById('edit_employee_id').value = userData.employee_id || '';
            document.getElementById('edit_date_of_employment').value = userData.date_of_employment || '';
            document.getElementById('edit_license_number').value = userData.license_number || '';
            document.getElementById('edit_license_expiry').value = userData.license_expiry || '';
            document.getElementById('edit_medical_record').value = userData.medical_record || '';
            document.getElementById('edit_trips_completed').value = userData.trips_completed || '0';

            // Toggle driver fields display based on user level
            var editDriverFields = document.getElementById('editDriverFields');
            if (userData.user_level === 'driver' || userData.user_level === 'conductor') {
                editDriverFields.style.display = 'block';
            } else {
                editDriverFields.style.display = 'none';
            }

            const form = document.getElementById('editUserForm');
            form.action = `<?= base_url('admin/users') ?>/${userKey}/edit`;
        });
    });

    // Toggle driver fields in Edit Modal when user level changes
    document.getElementById('edit_user_level').addEventListener('change', function(){
        var editDriverFields = document.getElementById('editDriverFields');
        if (this.value === 'driver' || this.value === 'conductor') {
            editDriverFields.style.display = 'block';
        } else {
            editDriverFields.style.display = 'none';
        }
    });

    // Delete modal script
    document.querySelectorAll('.btnDeleteUser').forEach(btn => {
        btn.addEventListener('click', function() {
            const userKey = this.getAttribute('data-user-key');
            const form = document.getElementById('deleteUserForm');
            form.action = `<?= base_url('admin/users') ?>/${userKey}/delete`;
        });
    });
</script>

<?= $this->endSection() ?>
