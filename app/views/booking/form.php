<?php
$isEdit = $booking !== null;
$action = $isEdit ? url('booking', 'update') : url('booking', 'store');
$items = $form['items'] !== [] ? $form['items'] : [['room_id' => '', 'quantity' => '1']];

// Một dòng phòng của lượt đặt (dùng cho cả dòng có sẵn và dòng mẫu)
$renderRow = static function (array $rooms, string $selected, string $quantity): string
{
    ob_start(); ?>
    <tr>
        <td>
            <select name="room_id[]" class="form-select" required>
                <option value="">-- Chọn phòng --</option>
                <?php foreach ($rooms as $room): ?>
                    <option value="<?= e($room['_id']) ?>" data-price="<?= e($room['price_per_night'] ?? 0) ?>"
                        <?= $selected === $room['_id'] ? 'selected' : '' ?>>
                        <?= e(($room['name'] ?? '') . ' — ' . money($room['price_per_night'] ?? 0) . '/đêm') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
        <td style="width:120px">
            <input type="number" name="quantity[]" class="form-control" min="1" value="<?= e($quantity) ?>" required>
        </td>
        <td class="text-end" style="width:60px">
            <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-x-lg"></i></button>
        </td>
    </tr>
    <?php return ob_get_clean();
};
?>
<div class="row justify-content-center">
    <div class="col-lg-9">
        <h1 class="h3 mb-3"><?= $isEdit ? 'Sửa lượt đặt phòng ' . e($booking['_id']) : 'Tạo lượt đặt phòng' ?></h1>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= e($action) ?>" class="card card-body">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= e($booking['_id']) ?>">
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-12">
                    <label for="user_id" class="form-label">Khách hàng <span class="text-danger">*</span></label>
                    <select id="user_id" name="user_id" class="form-select" required>
                        <option value="">-- Chọn khách hàng --</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?= e($user['_id']) ?>" <?= $form['user_id'] === $user['_id'] ? 'selected' : '' ?>>
                                <?= e(($user['name'] ?? '') . ' (' . $user['_id'] . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="check_in_date" class="form-label">Ngày nhận phòng <span class="text-danger">*</span></label>
                    <input type="date" id="check_in_date" name="check_in_date" class="form-control" required
                           value="<?= e($form['check_in_date']) ?>">
                </div>
                <div class="col-md-6">
                    <label for="check_out_date" class="form-label">Ngày trả phòng <span class="text-danger">*</span></label>
                    <input type="date" id="check_out_date" name="check_out_date" class="form-control" required
                           value="<?= e($form['check_out_date']) ?>">
                </div>
                <div class="col-md-6">
                    <label for="payment_status" class="form-label">Thanh toán</label>
                    <select id="payment_status" name="payment_status" class="form-select">
                        <?php foreach (Booking::PAYMENT_STATUSES as $status): ?>
                            <option value="<?= e($status) ?>" <?= $form['payment_status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="booking_status" class="form-label">Trạng thái đặt phòng</label>
                    <select id="booking_status" name="booking_status" class="form-select">
                        <?php foreach (Booking::BOOKING_STATUSES as $status): ?>
                            <option value="<?= e($status) ?>" <?= $form['booking_status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <hr>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h2 class="h6 mb-0">Phòng đặt <span class="text-muted fw-normal">(số phòng)</span></h2>
                <button type="button" id="addRow" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus-lg"></i> Thêm dòng
                </button>
            </div>
            <table class="table align-middle mb-2">
                <tbody id="itemRows">
                    <?php foreach ($items as $item): ?>
                        <?= $renderRow($rooms, (string) $item['room_id'], (string) $item['quantity']) ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="text-end">
                Số đêm: <strong id="nightCount">0</strong>
                &middot; Tổng tiền (tự tính): <strong id="bookingTotal">0 ₫</strong>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button class="btn btn-primary"><?= $isEdit ? 'Lưu thay đổi' : 'Tạo lượt đặt phòng' ?></button>
                <a href="<?= e(url('booking')) ?>" class="btn btn-outline-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>

<template id="rowTemplate"><?= $renderRow($rooms, '', '1') ?></template>

<script>
    (function () {
        var body = document.getElementById('itemRows');
        var template = document.getElementById('rowTemplate');
        var totalEl = document.getElementById('bookingTotal');
        var nightEl = document.getElementById('nightCount');
        var inEl = document.getElementById('check_in_date');
        var outEl = document.getElementById('check_out_date');

        function nights() {
            var a = Date.parse(inEl.value), b = Date.parse(outEl.value);
            if (isNaN(a) || isNaN(b) || b <= a) { return 0; }
            return Math.round((b - a) / 86400000);
        }

        function recalc() {
            var n = nights();
            var total = 0;
            body.querySelectorAll('tr').forEach(function (row) {
                var option = row.querySelector('select').selectedOptions[0];
                var price = option ? parseFloat(option.getAttribute('data-price') || 0) : 0;
                var qty = parseInt(row.querySelector('input').value || 0, 10);
                total += price * (qty > 0 ? qty : 0) * n;
            });
            nightEl.textContent = n;
            totalEl.textContent = total.toLocaleString('vi-VN') + ' ₫';
        }

        document.getElementById('addRow').addEventListener('click', function () {
            body.insertAdjacentHTML('beforeend', template.innerHTML);
            recalc();
        });
        body.addEventListener('click', function (event) {
            var button = event.target.closest('.remove-row');
            if (button) {
                button.closest('tr').remove();
                recalc();
            }
        });
        body.addEventListener('input', recalc);
        body.addEventListener('change', recalc);
        inEl.addEventListener('change', recalc);
        outEl.addEventListener('change', recalc);
        recalc();
    })();
</script>
