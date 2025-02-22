<?= $this->extend('templates/layout.php') ?>

<?= $this->section('content') ?>
<link href=<?= base_url('public/assets/css/style.css'); ?> rel="stylesheet">
<h1 class="title">Report Management</h1>
<title>Report Management</title>
<div class="search-bar">
        <input type="text" placeholder="Search">
    </div>

<div class="content">
    <div>
    <h2>Delivery Report</h2>
    <table class="table table-striped" style="width:100%">
        <thead>
            <tr>
                <th>Report Number</th>
                <th>Report Type</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $record): ?>
                <tr>
                    <td><?= esc($record['report_number']) ?></td>
                    <td><?= esc($record['report_type']) ?></td>
                    <td><?= esc($record['date']) ?></td>
                    <td><a href="<?= site_url('maintenance/view/' . $record['id']) ?>" class="view-button">View</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

<?= $this->endSection() ?>
