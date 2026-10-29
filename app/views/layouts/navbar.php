<?php
$links = [
    'home'     => ['Tổng quan', 'bi-speedometer2'],
    'booking'  => ['Đặt phòng', 'bi-calendar-check'],
    'room'     => ['Phòng', 'bi-door-open'],
    'category' => ['Loại phòng', 'bi-tags'],
    'user'     => ['Khách hàng', 'bi-people'],
];
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="<?= e(url()) ?>">
            <i class="bi bi-building me-1"></i>NBH Hotel
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <?php foreach ($links as $route => [$label, $icon]): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= ($active ?? '') === $route ? 'active' : '' ?>"
                           href="<?= e(url($route)) ?>">
                            <i class="bi <?= $icon ?>"></i> <?= e($label) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</nav>
