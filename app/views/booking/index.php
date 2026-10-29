<?php
$sumTotal = 0;
foreach ($bookings as $booking)
{
    $sumTotal += (int) ($booking['total'] ?? 0);
}
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Đặt phòng</h1>
    <a href="<?= e(url('booking', 'create')) ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tạo lượt đặt phòng</a>
</div>

<form method="get" action="index.php" class="card card-body mb-3">
    <input type="hidden" name="c" value="booking">
    <div class="row g-2 align-items-end">
        <div class="col-lg-4">
            <label class="form-label mb-1">Từ khóa</label>
            <input type="search" name="q" class="form-control" placeholder="Mã đặt phòng, mã khách hoặc tên khách"
                   value="<?= e($filters['q']) ?>">
        </div>
        <div class="col-6 col-lg-2">
            <label class="form-label mb-1">Thanh toán</label>
            <select name="payment_status" class="form-select">
                <option value="">Tất cả</option>
                <?php foreach (Booking::PAYMENT_STATUSES as $status): ?>
                    <option value="<?= e($status) ?>" <?= $filters['payment_status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-lg-2">
            <label class="form-label mb-1">Trạng thái</label>
            <select name="booking_status" class="form-select">
                <option value="">Tất cả</option>
                <?php foreach (Booking::BOOKING_STATUSES as $status): ?>
                    <option value="<?= e($status) ?>" <?= $filters['booking_status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-lg-2">
            <label class="form-label mb-1">Nhận phòng từ</label>
            <input type="date" name="date_from" class="form-control" value="<?= e($filters['date_from']) ?>">
        </div>
        <div class="col-6 col-lg-2">
            <label class="form-label mb-1">Nhận phòng đến</label>
            <input type="date" name="date_to" class="form-control" value="<?= e($filters['date_to']) ?>">
        </div>
        <div class="col-6 col-lg-3">
            <label class="form-label mb-1">Tổng tiền từ (₫)</label>
            <input type="number" name="total_min" class="form-control" min="0" step="1000000"
                   value="<?= e($filters['total_min']) ?>">
        </div>
        <div class="col-6 col-lg-3">
            <label class="form-label mb-1">Tổng tiền đến (₫)</label>
            <input type="number" name="total_max" class="form-control" min="0" step="1000000"
                   value="<?= e($filters['total_max']) ?>">
        </div>
        <div class="col-6 col-lg-3">
            <label class="form-label mb-1">Sắp xếp</label>
            <select name="sort" class="form-select">
                <?php foreach (Booking::SORTS as $key => $option): ?>
                    <option value="<?= e($key) ?>" <?= $sort === $key ? 'selected' : '' ?>><?= e($option['label']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-lg-3 d-flex gap-2">
            <button class="btn btn-dark"><i class="bi bi-search"></i> Tìm / Lọc</button>
            <a href="<?= e(url('booking')) ?>" class="btn btn-outline-secondary">Xóa bộ lọc</a>
        </div>
    </div>
</form>

<div class="text-muted small mb-2">
    Tìm thấy <strong><?= count($bookings) ?></strong> lượt đặt &middot; tổng giá trị <strong><?= e(money($sumTotal)) ?></strong>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Mã</th>
                    <th>Khách hàng</th>
                    <th>Nhận phòng</th>
                    <th>Trả phòng</th>
                    <th class="text-center">Đêm</th>
                    <th class="text-center">Phòng</th>
                    <th class="text-end">Tổng tiền</th>
                    <th>Thanh toán</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($bookings) === 0): ?>
                    <tr><td colspan="10" class="text-center text-muted py-4">Không có lượt đặt phòng nào</td></tr>
                <?php else: ?>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><a class="text-decoration-none fw-semibold"
                                   href="<?= e(url('booking', 'show', ['id' => $booking['_id']])) ?>"><?= e($booking['_id']) ?></a></td>
                            <td><?= e($booking['user_name'] ?? '') ?: e($booking['user_id'] ?? '') ?></td>
                            <td><?= e(format_date($booking['check_in_date'] ?? null)) ?></td>
                            <td><?= e(format_date($booking['check_out_date'] ?? null)) ?></td>
                            <td class="text-center"><?= nights_between($booking['check_in_date'] ?? null, $booking['check_out_date'] ?? null) ?></td>
                            <td class="text-center"><?= e($booking['room_count'] ?? 0) ?></td>
                            <td class="text-end"><?= e(money($booking['total'] ?? 0)) ?></td>
                            <td><span class="badge <?= badge_class((string) ($booking['payment_status'] ?? '')) ?>"><?= e($booking['payment_status'] ?? '') ?></span></td>
                            <td><span class="badge <?= badge_class((string) ($booking['booking_status'] ?? '')) ?>"><?= e($booking['booking_status'] ?? '') ?></span></td>
                            <td class="text-end">
                                <a href="<?= e(url('booking', 'show', ['id' => $booking['_id']])) ?>"
                                   class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
                                <a href="<?= e(url('booking', 'edit', ['id' => $booking['_id']])) ?>"
                                   class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <form method="post" action="<?= e(url('booking', 'delete')) ?>" class="d-inline"
                                      onsubmit="return confirm('Xóa lượt đặt phòng này?');">
                                    <input type="hidden" name="id" value="<?= e($booking['_id']) ?>">
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
