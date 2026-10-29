<?php
$isEdit = $user !== null;
$action = $isEdit ? url('user', 'update') : url('user', 'store');
?>
<div class="row justify-content-center">
    <div class="col-lg-7">
        <h1 class="h3 mb-3"><?= $isEdit ? 'Sửa khách hàng ' . e($user['_id']) : 'Thêm khách hàng' ?></h1>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= e($action) ?>" class="card card-body">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= e($user['_id']) ?>">
            <?php endif; ?>

            <div class="row g-3">
                <div class="col-12">
                    <label for="name" class="form-label">Họ tên <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" class="form-control" required
                           value="<?= e($form['name']) ?>">
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" required
                           value="<?= e($form['email']) ?>">
                </div>
                <div class="col-md-6">
                    <label for="phone" class="form-label">Điện thoại</label>
                    <input type="text" id="phone" name="phone" class="form-control"
                           value="<?= e($form['phone']) ?>">
                </div>
                <div class="col-12">
                    <label for="address" class="form-label">Địa chỉ</label>
                    <input type="text" id="address" name="address" class="form-control"
                           value="<?= e($form['address']) ?>">
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button class="btn btn-primary"><?= $isEdit ? 'Lưu thay đổi' : 'Thêm khách hàng' ?></button>
                <a href="<?= e(url('user')) ?>" class="btn btn-outline-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
