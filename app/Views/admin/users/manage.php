<?= $this->extend('templates/admin_layout.php') ?>

<?= $this->section('content') ?>
<link href=<?=base_url('public/assets/css/style.css');?> rel="stylesheet">
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
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
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

                    // Placeholder image
                    $placeholderImg = base_url('public/images/luffy.jpg'); 
                ?>
                <div class="col-md-4 mb-4 userCard"
                    data-alpha="<?= strtolower($firstName . ' ' . $lastName) ?>"
                    data-role="<?= strtolower($userLevel) ?>">

                    <!-- Card with relative positioning -->
                    <div class="card position-relative">
                        
                        <!-- Card image -->
                        <div class="card-img-top-container">
                            <img src="<?= $placeholderImg ?>" class="circular-image" alt="User Image">
                        </div>
                        
                        <!-- Dropdown (kebab menu) in the top-right corner -->
                        <div class="position-absolute top-0 end-0 m-2">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-secondary dropdown-toggle" 
                                        type="button" 
                                        data-bs-toggle="dropdown" 
                                        aria-expanded="false">
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
                            <h5 class="card-title">
                                <?= esc($firstName) . ' ' . esc($lastName) ?>
                            </h5>
                            <p class="card-text mb-1">
                                <strong>Username:</strong> <?= esc($username) ?>
                            </p>
                            <p class="card-text">
                                <strong>Role:</strong>
                                <span class="badge bg-info"><?= esc($userLevel) ?></span>
                            </p>
                        </div>
                    </div>
                    
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No users found.</p>
        <?php endif; ?>
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
            <!-- Form Fields -->
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
                </select>
            </div>
            <div class="mb-3">
                <label for="create_address_dropoff" class="form-label">Address Dropoff</label>
                <input type="text" name="address_dropoff" id="create_address_dropoff" class="form-control">
            </div>
            
            <!-- New Password Fields -->
            <div class="mb-3">
                <label for="create_password" class="form-label">Password</label>
                <input type="password" name="password" id="create_password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="create_confirm_password" class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" id="create_confirm_password" class="form-control" required>
            </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Create</button>
        </div>
      </div>
    </form>
  </div>
</div>



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
            <!-- Same fields as create, but pre-filled -->
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
                </select>
            </div>
            <div class="mb-3">
                <label for="edit_address_dropoff" class="form-label">Address Dropoff</label>
                <input type="text" name="address_dropoff" id="edit_address_dropoff" class="form-control">
            </div>
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


<!-- ======================== JavaScript ======================== -->
<script>
    // Handle passing user data to the Edit modal
    document.querySelectorAll('.btnEditUser').forEach(btn => {
        btn.addEventListener('click', function() {
            const userKey = this.getAttribute('data-user-key');
            const userDataRaw = this.getAttribute('data-user');
            const userData = JSON.parse(userDataRaw);

            // Populate form fields
            document.getElementById('edit_user_key').value        = userKey; // hidden
            document.getElementById('edit_first_name').value      = userData.first_name || '';
            document.getElementById('edit_last_name').value       = userData.last_name || '';
            document.getElementById('edit_email').value           = userData.email || '';
            document.getElementById('edit_username').value        = userData.username || '';
            document.getElementById('edit_contact_number').value  = userData.contact_number || '';
            document.getElementById('edit_address').value         = userData.address || '';
            document.getElementById('edit_birthday').value        = userData.birthday || '';
            document.getElementById('edit_gender').value          = userData.gender || '';
            document.getElementById('edit_user_level').value      = userData.user_level || '';
            document.getElementById('edit_address_dropoff').value = userData.address_dropoff || '';

            // Set the form action to include the user key
            const form = document.getElementById('editUserForm');
            form.action = `<?= base_url('admin/users') ?>/${userKey}/edit`;
        });
    });

    // Handle passing user key to the Delete modal
    document.querySelectorAll('.btnDeleteUser').forEach(btn => {
        btn.addEventListener('click', function() {
            const userKey = this.getAttribute('data-user-key');
            const form = document.getElementById('deleteUserForm');
            form.action = `<?= base_url('admin/users') ?>/${userKey}/delete`;
        });
    });

    // Sorting functionality (by alphabetical or by role)
    //   - We rely on data attributes: data-alpha, data-role
    const sortSelect = document.getElementById('sortSelect');
    sortSelect.addEventListener('change', function() {
        const sortMode = this.value;
        const container = document.getElementById('userCardsContainer');
        const cards = Array.from(container.getElementsByClassName('userCard'));

        if (sortMode === 'alpha') {
            // Sort by data-alpha
            cards.sort((a, b) => {
                let nameA = a.getAttribute('data-alpha');
                let nameB = b.getAttribute('data-alpha');
                return nameA.localeCompare(nameB);
            });
        } else if (sortMode === 'role') {
            // Define a custom order: admin, resource manager, operation manager, client
            const roleOrder = ['admin', 'resource manager', 'operation manager', 'client'];
            cards.sort((a, b) => {
                let roleA = a.getAttribute('data-role');
                let roleB = b.getAttribute('data-role');
                let indexA = roleOrder.indexOf(roleA);
                let indexB = roleOrder.indexOf(roleB);
                return indexA - indexB;
            });
        }

        // Re-append in sorted order
        cards.forEach(card => container.appendChild(card));
    });
</script>

<!-- <script src="<?= base_url('public/assets/js/script.js') ?>"></script> -->

<?= $this->endSection() ?>
