<?= $this->extend('templates/admin_layout.php') ?>

<?= $this->section('content') ?>
<link href=<?=base_url('public/assets/css/style.css');?> rel="stylesheet">
<body>
<title>Profile Account</title>
<h1>Profile Account</h1>


<div class="container-fluid mt-4">
    <!-- Display any flash messages -->
    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- Profile Picture Card -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-img-top-container">
                    <img src="<?= base_url('public/images/luffy.jpg') ?>" class="circular-image" alt="Profile Picture">     
                </div>
            
                <div class="card-body text-center">
                    <button class="btn btn-primary" disabled>Edit Picture</button>
                </div>
            </div>
        </div>
        <!-- Account Details Card -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header">
                    ACCOUNT DETAILS
                </div>
                <div class="card-body text-start">
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <p><strong>First Name:</strong> <?= esc($user['first_name']) ?></p>
                            <p><strong>Email:</strong> <?= esc($user['email']) ?></p>
                            <p><strong>Username:</strong> <?= esc($user['username']) ?></p>
                            <p><strong>Address:</strong> <?= esc($user['address']) ?></p>
                        </div>
                        <!-- Right Column -->
                        <div class="col-md-6">
                            <p><strong>Last Name:</strong> <?= esc($user['last_name']) ?></p>
                            <p><strong>Contact Number:</strong> <?= esc($user['contact_number']) ?></p>
                            <p><strong>Birthday:</strong> <?= esc($user['birthday']) ?></p>
                            <p><strong>Gender:</strong> <?= esc($user['gender']) ?></p>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit</button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Modal for Editing Account Details -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?= base_url('admin/updateProfile') ?>" method="post">
        <?= csrf_field() ?>
        <div class="modal-header">
          <h5 class="modal-title" id="editProfileModalLabel">Edit Account Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <!-- First Name and Last Name -->
            <div class="mb-3">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" name="first_name" id="first_name" class="form-control" value="<?= esc($user['first_name']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" name="last_name" id="last_name" class="form-control" value="<?= esc($user['last_name']) ?>" required>
            </div>
            <!-- Email and Username -->
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="<?= esc($user['email']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" id="username" class="form-control" value="<?= esc($user['username']) ?>" required>
            </div>
            <!-- Contact and Address -->
            <div class="mb-3">
                <label for="contact_number" class="form-label">Contact Number</label>
                <input type="text" name="contact_number" id="contact_number" class="form-control" value="<?= esc($user['contact_number']) ?>">
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <input type="text" name="address" id="address" class="form-control" value="<?= esc($user['address']) ?>">
            </div>
            <!-- Birthday and Gender -->
            <div class="mb-3">
                <label for="birthday" class="form-label">Birthday</label>
                <input type="date" name="birthday" id="birthday" class="form-control" value="<?= esc($user['birthday']) ?>">
            </div>
            <div class="mb-3">
                <label for="gender" class="form-label">Gender</label>
                <select name="gender" id="gender" class="form-select">
                    <option value="male" <?= (isset($user['gender']) && strtolower($user['gender']) == 'male') ? 'selected' : '' ?>>Male</option>
                    <option value="female" <?= (isset($user['gender']) && strtolower($user['gender']) == 'female') ? 'selected' : '' ?>>Female</option>
                    <option value="other" <?= (isset($user['gender']) && strtolower($user['gender']) == 'other') ? 'selected' : '' ?>>Other</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>



<script src="<?= base_url('assets/js/script.js') ?>"></script>
</body>
</html>

<?= $this->endSection() ?>