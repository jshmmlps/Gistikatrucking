<?= $this->extend('templates/resource_manager_layout') ?>

<?= $this->section('content') ?>
<link href=<?=base_url('public/assets/css/style.css');?> rel="stylesheet">
<body>
<title>Resource Manager User Account</title>
<h1>Resource Manager User Account</h1>
    <div class="row">
        <!-- Profile Card -->
        <div class="col-md-4">
            <div class="card text-center p-3 shadow-sm">
                <img src="<?= base_url('assets/images/luffy.jpg') ?>" class="rounded-circle profile-img mb-3" id="profileImage">
                <h5><?= esc($user['name']) ?></h5>
                <p class="text-muted"><?= esc($user['position']) ?></p>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadProfileModal">Change Profile Picture</button>
            </div>
        </div>

        <!-- Account Information -->
        <div class="col-md-8">
            <div class="card p-4 shadow-sm">
                <h4 class="fw-bold">Account Information</h4>
                <p><strong>Name:</strong> <?= esc($user['name']) ?></p>
                <p><strong>Email:</strong> <?= esc($user['email']) ?></p>
                <p><strong>Contact:</strong> <?= esc($user['contact']) ?></p>
                <p><strong>Address:</strong> <?= esc($user['address']) ?></p>
                <p><strong>Position:</strong> <?= esc($user['position']) ?></p>
                <p><strong>Username:</strong> <?= esc($user['username']) ?></p>

                <button class="btn btn-warning btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#editProfileModal">Edit Profile</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Account Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editProfileForm">
                    <input type="hidden" id="editUserId" value="<?= esc($user['id']) ?>">

                    <div class="mb-3">
                        <label for="editName">Name</label>
                        <input type="text" class="form-control" id="editName" value="<?= esc($user['name']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="editEmail">Email</label>
                        <input type="email" class="form-control" id="editEmail" value="<?= esc($user['email']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="editContact">Contact</label>
                        <input type="text" class="form-control" id="editContact" value="<?= esc($user['contact']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="editAddress">Address</label>
                        <input type="text" class="form-control" id="editAddress" value="<?= esc($user['address']) ?>" required>
                    </div>

                    <button type="submit" class="btn btn-success">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Upload Profile Picture Modal -->
<div class="modal fade" id="uploadProfileModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Profile Picture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="uploadProfileForm" enctype="multipart/form-data">
                    <input type="file" class="form-control mb-3" id="profilePicture" required>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Handle Profile Image Upload
    document.getElementById("uploadProfileForm").addEventListener("submit", function (event) {
        event.preventDefault();
        let profileImage = document.getElementById("profilePicture").files[0];
        if (profileImage) {
            let reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById("profileImage").src = e.target.result;
            };
            reader.readAsDataURL(profileImage);
        }
        new bootstrap.Modal(document.getElementById("uploadProfileModal")).hide();
    });

    // Handle Edit Profile Save
    document.getElementById("editProfileForm").addEventListener("submit", function (event) {
        event.preventDefault();
        alert("Profile updated successfully!");
        new bootstrap.Modal(document.getElementById("editProfileModal")).hide();
    });
});
</script>

<?= $this->endSection() ?>
