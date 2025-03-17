<?= $this->extend('templates/client_layout.php') ?>

<?= $this->section('content') ?>
<link href="<?= base_url('public/assets/css/style.css'); ?>" rel="stylesheet">
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
                    <!-- Display profile picture if set, else a default image -->
                    <img src="<?= esc($user['profile_picture'] ?? base_url('public/images/default.jpg')) ?>" 
                         class="circular-image" alt="Profile Picture">     
                </div>
                <div class="card-body text-center">
                    <!-- Show either upload or edit option depending on the picture status -->
                    <?php if ($user['profile_picture'] === base_url('public/images/default.jpg')): ?>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadProfilePictureModal">Upload Picture</button>
                    <?php else: ?>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfilePictureModal">Edit Picture</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Account Details Card -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header">ACCOUNT DETAILS</div>
                <div class="card-body text-start">
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <p><strong>First Name:</strong> <?= esc($user['first_name'] ?? '') ?></p>
                            <p><strong>Email:</strong> <?= esc($user['email'] ?? '') ?></p>
                            <p><strong>Username:</strong> <?= esc($user['username'] ?? '') ?></p>
                            <p><strong>Address:</strong> <?= esc($user['address'] ?? '') ?></p>
                        </div>
                        <!-- Right Column -->
                        <div class="col-md-6">
                            <p><strong>Last Name:</strong> <?= esc($user['last_name'] ?? '') ?></p>
                            <p><strong>Contact Number:</strong> <?= esc($user['contact_number'] ?? '') ?></p>
                            <p><strong>Birthday:</strong> <?= esc($user['birthday'] ?? '') ?></p>
                            <p><strong>Gender:</strong> <?= esc($user['gender'] ?? '') ?></p>
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

<!-- Modal for Uploading Profile Picture -->
<div class="modal fade" id="uploadProfilePictureModal" tabindex="-1" aria-labelledby="uploadProfilePictureModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('client/uploadProfilePicture') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadProfilePictureModalLabel">Upload Profile Picture</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="profile_image" class="form-label">Select Profile Picture</label>
                        <input type="file" name="profile_image" id="profile_image" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload Picture</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for Editing Profile Picture -->
<div class="modal fade" id="editProfilePictureModal" tabindex="-1" aria-labelledby="editProfilePictureModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?= base_url('client/editProfilePicture') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="editProfilePictureModalLabel">Edit Profile Picture</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="profile_image" class="form-label">Select New Profile Picture</label>
                        <input type="file" name="profile_image" id="profile_image" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Picture</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/script.js') ?>"></script>
<?= $this->endSection() ?>
