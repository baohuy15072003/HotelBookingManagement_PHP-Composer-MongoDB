<?php
$isEdit = $room !== null;
$action = $isEdit ? url('room', 'update') : url('room', 'store');
?>
<div class="row justify-content-center">
    <div class="col-lg-7">
        <h1 class="h3 mb-3"><?= $isEdit ? 'Sửa phòng ' . e($room['_id']) : 'Thêm phòng' ?></h1>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= e($action) ?>" class="card card-body">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= e($room['_id']) ?>">
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-12">
                    <label for="name" class="form-label">Tên phòng <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" class="form-control" required
                           value="<?= e($form['name']) ?>">
                </div>
                <div class="col-md-6">
                    <label for="category_id" class="form-label">Loại phòng <span class="text-danger">*</span></label>
                    <select id="category_id" name="category_id" class="form-select" required>
                        <option value="">-- Chọn loại phòng --</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= e($category['_id']) ?>" <?= $form['category_id'] === $category['_id'] ? 'selected' : '' ?>>
                                <?= e($category['name'] ?? '') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="amenities" class="form-label">Tiện nghi</label>
                    <input type="text" id="amenities" name="amenities" class="form-control"
                           placeholder="Điều hòa, Wi-Fi miễn phí, Tivi" value="<?= e($form['amenities']) ?>">
                    <div class="form-text">Ngăn cách nhiều tiện nghi bằng dấu phẩy.</div>
                </div>
                <div class="col-md-6">
                    <label for="price_per_night" class="form-label">Giá mỗi đêm (₫) <span class="text-danger">*</span></label>
                    <input type="number" id="price_per_night" name="price_per_night" class="form-control" min="1" required
                           value="<?= e($form['price_per_night']) ?>">
                </div>
                <div class="col-md-6">
                    <label for="stock" class="form-label">Số phòng</label>
                    <input type="number" id="stock" name="stock" class="form-control" min="0"
                           value="<?= e($form['stock']) ?>">
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button class="btn btn-primary"><?= $isEdit ? 'Lưu thay đổi' : 'Thêm phòng' ?></button>
                <a href="<?= e(url('room')) ?>" class="btn btn-outline-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
