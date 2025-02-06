<?= $this->extend('templates/layout.php') ?>

<?= $this->section('content') ?>
<link href=<?= base_url('public/assets/css/style.css'); ?> rel="stylesheet">

<div class="container">
    <h1 class="title">Report Management</h1>
    
    <h2 class="subtitle">Delivery Report</h2>

    <div class="search-bar">
        <input type="text" placeholder="Search">
    </div>

    <table class="report-table">
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

<style>
.container { text-align: center; }
.title { font-size: 24px; margin: 20px 0; }
.subtitle { font-size: 20px; margin-bottom: 15px; }
.search-bar { margin-bottom: 20px; }
.search-bar input { width: 200px; padding: 5px; border: 1px solid #ccc; border-radius: 5px; }
.report-table { width: 100%; border-collapse: collapse; }
.report-table th, .report-table td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
.view-button { color: blue; text-decoration: none; }
</style>

<?= $this->endSection() ?>
