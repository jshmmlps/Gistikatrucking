<?= $this->extend('templates/layout.php') ?>

<?= $this->section('content') ?>
<link href=<?=base_url('public/assets/css/style.css');?> rel="stylesheet">
<body>
<title>User Account</title>
<h1>User Account</h1>


<div class="container-fluid">
    <div class="profile-container">
        <div class="row"> <!-- Added row to make columns work properly -->
            <!-- Profile Section (Left) -->
            <div class="col-md-4 d-flex justify-content-center">
                <div class="user-profile">
                    <?php foreach ($users as $user): ?>
                        <div class="user-card text-center">
                            <div class="user-icon">
                                <img src="<?= base_url('public/images/luffy.jpg') ?>" alt="User Icon">
                            </div>
                            <p class="user-name"><?= strtoupper($user['first_name'] . ' ' . $user['last_name']) ?></p>
                            <button onclick="viewUser(<?= $user['id'] ?>)" class="btn btn-primary">Upload</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Account Information (Right Side) -->
            <div class="col-md-8">
                <div class="user-container">
                    <div class="user-details">
                        <h2>Account Information</h2>
                        <p><strong>Name:</strong> <span id="user-name"></span></p>
                        <p><strong>Email:</strong> <span id="user-email"></span></p>
                        <p><strong>Contact:</strong> <span id="user-contact"></span></p>
                        <p><strong>Address:</strong> <span id="user-address"></span></p>
                        <p><strong>Position:</strong> <span id="user-position"></span></p>
                        <p><strong>Username:</strong> <span id="user-username"></span></p>
                        <button class="btn btn-warning btn-sm mt-3 px-4 d-block mx-auto" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editAccountModal">
                            Edit
                        </button>
                    </div>
                </div>
            </div>
        </div> <!-- Closing row -->
    </div>
</div>

<!-- Edit Account Modal -->
<div class="modal fade" id="editAccountModal" tabindex="-1" aria-labelledby="editAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAccountModalLabel">Edit Account Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editAccountForm" method="POST" action="<?= site_url('user_account/update') ?>">
                    <div class="mb-3">
                        <label for="editName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="editName" name="name" value="<?= esc($user['first_name'] . ' ' . $user['last_name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="editEmail" class="form-label">Email</label>
                        <input type="email" class="form-control" id="editEmail" name="email" value="<?= esc($user['email']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="editAddress" class="form-label">Address</label>
                        <input type="text" class="form-control" id="editAddress" name="address" value="<?= esc($user['address']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="editPosition" class="form-label">Position</label>
                        <input type="text" class="form-control" id="editPosition" name="position" value="<?= esc($user['position']) ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="editUsername" class="form-label">Username</label>
                        <input type="text" class="form-control" id="editUsername" name="username" value="<?= esc($user['username']) ?>" readonly>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/script.js') ?>"></script>
</body>
</html>

<?= $this->endSection() ?>