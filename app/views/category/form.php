<?php
$isEdit = $category !== null;
$action = $isEdit ? url('category', 'update') : url('category', 'store');
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <h1 class="h3 mb-3"><?= $isEdit ? 'Sửa loại phòng ' . e($category['_id']) : 'Thêm loại phòng' ?></h1>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= e($action) ?>" class="card card-body">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= e($category['_id']) ?>">
            <?php endif; ?>

            <label for="name" class="form-label">Tên loại phòng <span class="text-danger">*</span></label>
            <input type="text" id="name" name="name" class="form-control" required
                   value="<?= e($form['name']) ?>">

            <div class="d-flex gap-2 mt-4">
                <button class="btn btn-primary"><?= $isEdit ? 'Lưu thay đổi' : 'Thêm loại phòng' ?></button>
                <a href="<?= e(url('category')) ?>" class="btn btn-outline-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
