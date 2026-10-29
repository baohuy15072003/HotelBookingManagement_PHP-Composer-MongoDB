<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Đặt phòng <?= e($booking['_id']) ?></h1>
    <div class="d-flex gap-2">
        <a href="<?= e(url('booking', 'edit', ['id' => $booking['_id']])) ?>" class="btn btn-primary"><i class="bi bi-pencil"></i> Sửa</a>
        <a href="<?= e(url('booking')) ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Quay lại</a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">Thông tin đặt phòng</div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Nhận phòng</dt>
                    <dd class="col-sm-7"><?= e(format_date($booking['check_in_date'] ?? null)) ?></dd>
                    <dt class="col-sm-5">Trả phòng</dt>
                    <dd class="col-sm-7"><?= e(format_date($booking['check_out_date'] ?? null)) ?></dd>
                    <dt class="col-sm-5">Số đêm</dt>
                    <dd class="col-sm-7"><?= nights_between($booking['check_in_date'] ?? null, $booking['check_out_date'] ?? null) ?></dd>
                    <dt class="col-sm-5">Thanh toán</dt>
                    <dd class="col-sm-7"><span class="badge <?= badge_class((string) ($booking['payment_status'] ?? '')) ?>"><?= e($booking['payment_status'] ?? '') ?></span></dd>
                    <dt class="col-sm-5">Trạng thái</dt>
                    <dd class="col-sm-7"><span class="badge <?= badge_class((string) ($booking['booking_status'] ?? '')) ?>"><?= e($booking['booking_status'] ?? '') ?></span></dd>
                    <dt class="col-sm-5">Tổng tiền</dt>
                    <dd class="col-sm-7 fw-semibold"><?= e(money($booking['total'] ?? 0)) ?></dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">Khách hàng</div>
            <div class="card-body">
                <?php if ($customer === null): ?>
                    <div class="text-muted">Khách hàng <?= e($booking['user_id'] ?? '') ?> không còn tồn tại.</div>
                <?php else: ?>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Họ tên</dt>
                        <dd class="col-sm-8"><?= e($customer['name'] ?? '') ?> <span class="text-muted">(<?= e($customer['_id']) ?>)</span></dd>
                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8"><?= e($customer['email'] ?? '') ?></dd>
                        <dt class="col-sm-4">Điện thoại</dt>
                        <dd class="col-sm-8"><?= e($customer['phone'] ?? '') ?></dd>
                        <dt class="col-sm-4">Địa chỉ</dt>
                        <dd class="col-sm-8"><?= e($customer['address'] ?? '') ?></dd>
                    </dl>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header fw-semibold">Phòng đã đặt</div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Mã</th>
                    <th>Phòng</th>
                    <th class="text-end">Giá/đêm</th>
                    <th class="text-center">Số phòng</th>
                    <th class="text-center">Số đêm</th>
                    <th class="text-end">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lines as $line): ?>
                    <tr>
                        <td><?= e($line['room_id']) ?></td>
                        <td><?= e($line['name']) ?></td>
                        <td class="text-end"><?= e(money($line['price'])) ?></td>
                        <td class="text-center"><?= $line['quantity'] ?></td>
                        <td class="text-center"><?= $line['nights'] ?></td>
                        <td class="text-end"><?= e(money($line['subtotal'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="5" class="text-end">Tổng cộng</th>
                    <th class="text-end"><?= e(money($booking['total'] ?? 0)) ?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<div class="small text-muted mt-2">Thành tiền tính theo giá/đêm hiện tại của phòng; tổng cộng là số tiền đã lưu khi đặt.</div>
