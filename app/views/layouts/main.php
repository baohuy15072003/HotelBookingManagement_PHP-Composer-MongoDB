<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($title ?? 'Trang chủ') . ' | NBH Hotel') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

<?php require __DIR__ . '/navbar.php'; ?>

<main class="container py-4 flex-grow-1">
    <?php if (!empty($_GET['msg']) && is_string($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= e($_GET['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($_GET['err']) && is_string($_GET['err'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= e($_GET['err']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php require $contentView; ?>
</main>

<?php require __DIR__ . '/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
