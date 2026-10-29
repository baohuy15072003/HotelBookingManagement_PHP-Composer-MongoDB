<?php
/** $stats: kết quả Report::dashboard() */
$maxYear = max(array_merge([1], array_column($stats['byYear'], 'revenue')));
$maxMonth = max(array_merge([1], array_column($stats['byMonth'], 'revenue')));
$maxCategory = max(array_merge([1], array_column($stats['topCategories'], 'bookings')));
$maxUser = max(array_merge([1], array_column($stats['topUsers'], 'bookings')));
$maxStock = max(array_merge([1], array_column($stats['roomsByCategory'], 'stock')));
$selectedYear = $stats['year'];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Tổng quan khách sạn</h1>
    <a href="<?= e(url('home', 'export', $selectedYear !== '' ? ['year' => $selectedYear] : [])) ?>"
       target="_blank" class="btn btn-danger">
        <i class="bi bi-file-earmark-pdf"></i> Xuất PDF<?= $selectedYear !== '' ? ' (năm ' . e($selectedYear) . ')' : '' ?>
    </a>
</div>

<div class="row g-3 mb-3">
    <?php
    $cards = [
        ['Doanh thu (đã thanh toán)', money($stats['revenue']), 'text-success', 'booking'],
        ['Chưa thu (đã đặt, chưa thanh toán)', money($stats['pending']), 'text-warning', 'booking'],
        ['Lượt đặt phòng (không tính đã hủy)', $stats['validBookings'] . ' / ' . $stats['bookings'], '', 'booking'],
        ['Khách hàng', $stats['users'], '', 'user'],
    ];
    ?>
    <?php foreach ($cards as [$label, $value, $class, $route]): ?>
        <div class="col-6 col-lg-3">
            <a href="<?= e(url($route)) ?>" class="text-decoration-none text-reset">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small"><?= e($label) ?></div>
                    <div class="fs-4 fw-semibold <?= $class ?>"><?= e($value) ?></div>
                </div></div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<!-- Doanh thu theo năm / theo tháng -->
<div class="row g-3 mb-3">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header fw-semibold">Doanh thu theo năm</div>
            <div class="card-body">
                <?php if ($stats['byYear'] === []): ?><div class="text-muted">Chưa có dữ liệu.</div><?php endif; ?>
                <?php foreach ($stats['byYear'] as $row): ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small">
                            <a class="text-decoration-none fw-semibold"
                               href="<?= e(url('home', 'index', ['year' => $row['label']])) ?>">Năm <?= e($row['label']) ?></a>
                            <span><?= e(money($row['revenue'])) ?> <span class="text-muted">(<?= $row['bookings'] ?> lượt)</span></span>
                        </div>
                        <div class="progress" style="height:10px">
                            <div class="progress-bar bg-success" style="width: <?= round($row['revenue'] / $maxYear * 100) ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="small text-muted">Bấm vào một năm để xem doanh thu 12 tháng của năm đó.</div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold">
                    Doanh thu theo tháng<?= $selectedYear !== '' ? ' — năm ' . e($selectedYear) : '' ?>
                </span>
                <form method="get" action="index.php" class="d-flex gap-2">
                    <input type="hidden" name="c" value="home">
                    <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">Tất cả các tháng</option>
                        <?php foreach ($stats['years'] as $y): ?>
                            <option value="<?= e($y) ?>" <?= $selectedYear === $y ? 'selected' : '' ?>>Năm <?= e($y) ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            <div class="card-body">
                <?php if ($stats['byMonth'] === []): ?><div class="text-muted">Chưa có dữ liệu.</div><?php endif; ?>
                <?php foreach ($stats['byMonth'] as $row): ?>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between small">
                            <span>Tháng <?= e(substr($row['label'], 5, 2) . '/' . substr($row['label'], 0, 4)) ?>
                                <span class="text-muted">(<?= $row['bookings'] ?> lượt)</span></span>
                            <span><?= e(money($row['revenue'])) ?></span>
                        </div>
                        <div class="progress" style="height:8px">
                            <div class="progress-bar bg-success" style="width: <?= round($row['revenue'] / $maxMonth * 100) ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="small text-muted mt-3">
                    Doanh thu tính theo ngày nhận phòng; chỉ gồm lượt đặt đã thanh toán và chưa hủy.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top 5 loại phòng / khách hàng -->
<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">Top 5 loại phòng được đặt nhiều nhất</div>
            <div class="card-body">
                <?php if ($stats['topCategories'] === []): ?><div class="text-muted">Chưa có dữ liệu.</div><?php endif; ?>
                <?php foreach ($stats['topCategories'] as $i => $row): ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small">
                            <span><span class="badge bg-dark me-1">#<?= $i + 1 ?></span><?= e($row['name']) ?></span>
                            <span><strong><?= $row['bookings'] ?></strong> lượt đặt
                                <span class="text-muted">&middot; <?= $row['room_nights'] ?> phòng-đêm</span></span>
                        </div>
                        <div class="progress" style="height:8px">
                            <div class="progress-bar" style="width: <?= round($row['bookings'] / $maxCategory * 100) ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="small text-muted">Một lượt đặt có nhiều phòng cùng loại chỉ tính 1 lượt; phòng-đêm = số phòng × số đêm.</div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">Top 5 khách hàng đặt nhiều nhất</div>
            <div class="card-body">
                <?php if ($stats['topUsers'] === []): ?><div class="text-muted">Chưa có dữ liệu.</div><?php endif; ?>
                <?php foreach ($stats['topUsers'] as $i => $row): ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small">
                            <span><span class="badge bg-dark me-1">#<?= $i + 1 ?></span>
                                <a class="text-decoration-none" href="<?= e(url('booking', 'index', ['q' => $row['id']])) ?>"><?= e($row['name']) ?></a></span>
                            <span><strong><?= $row['bookings'] ?></strong> lượt đặt
                                <span class="text-muted">&middot; <?= e(money($row['total'])) ?></span></span>
                        </div>
                        <div class="progress" style="height:8px">
                            <div class="progress-bar bg-info" style="width: <?= round($row['bookings'] / $maxUser * 100) ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="small text-muted">Không tính các lượt đặt đã hủy. Cùng số lượt thì xếp theo tổng giá trị.</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">Trạng thái đặt phòng</div>
            <div class="card-body">
                <div class="small text-muted mb-1">Theo xử lý</div>
                <div class="mb-3">
                    <?php foreach ($stats['byBookingStatus'] as $status => $count): ?>
                        <a href="<?= e(url('booking', 'index', ['booking_status' => $status])) ?>"
                           class="badge <?= badge_class($status) ?> fs-6 text-decoration-none me-1 mb-1">
                            <?= e($status) ?>: <?= $count ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <div class="small text-muted mb-1">Theo thanh toán</div>
                <div>
                    <?php foreach ($stats['byPaymentStatus'] as $status => $count): ?>
                        <a href="<?= e(url('booking', 'index', ['payment_status' => $status])) ?>"
                           class="badge <?= badge_class($status) ?> fs-6 text-decoration-none me-1 mb-1">
                            <?= e($status) ?>: <?= $count ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">Số phòng theo loại phòng</div>
            <div class="card-body">
                <?php if ($stats['roomsByCategory'] === []): ?><div class="text-muted">Chưa có dữ liệu.</div><?php endif; ?>
                <?php foreach ($stats['roomsByCategory'] as $row): ?>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between small">
                            <span><?= e($row['name']) ?></span>
                            <span><?= $row['types'] ?> hạng phòng &middot; <?= $row['stock'] ?> phòng</span>
                        </div>
                        <div class="progress" style="height:8px">
                            <div class="progress-bar bg-secondary" style="width: <?= round($row['stock'] / $maxStock * 100) ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header fw-semibold">Lịch nhận phòng sắp tới</div>
    <ul class="list-group list-group-flush">
        <?php if ($stats['upcoming'] === []): ?><li class="list-group-item text-muted">Không có lượt nhận phòng nào sắp tới.</li><?php endif; ?>
        <?php foreach ($stats['upcoming'] as $booking): ?>
            <li class="list-group-item d-flex justify-content-between flex-wrap gap-2">
                <span>
                    <a class="text-decoration-none fw-semibold" href="<?= e(url('booking', 'show', ['id' => $booking['_id']])) ?>"><?= e($booking['_id']) ?></a>
                    &middot; <?= e($booking['user_name'] ?? '') ?: e($booking['user_id'] ?? '') ?>
                </span>
                <span class="text-muted small">
                    <?= e(format_date($booking['check_in_date'] ?? null)) ?> → <?= e(format_date($booking['check_out_date'] ?? null)) ?>
                    <span class="badge <?= badge_class((string) ($booking['booking_status'] ?? '')) ?> ms-1"><?= e($booking['booking_status'] ?? '') ?></span>
                </span>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
