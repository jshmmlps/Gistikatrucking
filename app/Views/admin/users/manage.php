<?= $this->extend('templates/admin_layout.php') ?>

<?= $this->section('content') ?>
<link href=<?= base_url('public/assets/css/style.css'); ?> rel="stylesheet">
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
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal" style="background-color:#336699" >
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

    <div class=mb-3>
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

                    <!-- Card with relative positioning -->
                    <div class="card position-relative">
                        
                        <!-- Card image -->
                        <div class="card-img-top-container">
                            <img src="<?= esc($profilePicture) ?>" class="circular-image" alt="User Image">
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
      

        <!-- Pagination links -->
        <nav id="paginationControls" class="mt-3">
            <!-- This will be populated dynamically using JS -->
        </nav>
    </div>
</div>

<script>
    const usersPerPageOptions = [10, 25, 50];
    const userCardsContainer = document.getElementById("userCardsContainer");
    const paginationControls = document.getElementById("paginationControls");
    let allUsers = Array.from(userCardsContainer.children);
    let currentPage = 1;
    let usersPerPage = document.getElementById("entriesSelect").value;

    // Function to update pagination controls and visible users
    function paginate() {
        const totalUsers = allUsers.length;
        const totalPages = Math.ceil(totalUsers / usersPerPage);

        // Hide all users first
        allUsers.forEach(user => user.style.display = "none");

        // Calculate the range of users to show for the current page
        const start = (currentPage - 1) * usersPerPage;
        const end = start + usersPerPage;

        // Show the users for the current page
        for (let i = start; i < end && i < totalUsers; i++) {
            allUsers[i].style.display = "block";
        }

        // Update pagination controls
        let paginationHtml = '<ul class="pagination">';
        for (let i = 1; i <= totalPages; i++) {
            paginationHtml += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="goToPage(${i})">${i}</a>
            </li>`;
        }
        paginationHtml += '</ul>';
        paginationControls.innerHTML = paginationHtml;
    }

    // Go to a specific page
    function goToPage(page) {
        currentPage = page;
        paginate();
    }

    // Handle entries per page change
    document.getElementById("entriesSelect").addEventListener("change", function() {
        usersPerPage = parseInt(this.value);
        paginate();
    });

    // Initialize pagination
    paginate();
</script>

<?= $this->endSection() ?>
