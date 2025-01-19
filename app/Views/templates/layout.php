<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?></title>
    <link href=<?=base_url('public/assets/css/style.css');?> rel="stylesheet">
</head>
<body style="margin: 0; display: flex;">

    <!-- Sidebar -->
    <?= $this->include('templates/sidebar') ?>

    <!-- Main Content -->
    <div style="margin-left: 250px; padding: 20px; width: 100%;">
        <?= $this->renderSection('content') ?>
    </div>

</body>
</html>
