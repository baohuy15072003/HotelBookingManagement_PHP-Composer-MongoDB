<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Loại phòng</h1>
    <a href="<?= e(url('category', 'create')) ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Thêm loại phòng</a>
</div>

<form method="get" action="index.php" class="card card-body mb-3">
    <input type="hidden" name="c" value="category">
    <div class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label mb-1">Từ khóa</label>
            <input type="search" name="q" class="form-control" placeholder="Mã hoặc tên loại phòng"
                   value="<?= e($filters['q']) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label mb-1">Phòng</label>
            <select name="rooms" class="form-select">
                <option value="">Tất cả</option>
                <option value="has" <?= $filters['rooms'] === 'has' ? 'selected' : '' ?>>Đã có phòng</option>
                <option value="none" <?= $filters['rooms'] === 'none' ? 'selected' : '' ?>>Chưa có phòng</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label mb-1">Sắp xếp</label>
            <select name="sort" class="form-select">
                <?php foreach (Category::SORTS as $key => $option): ?>
                    <option value="<?= e($key) ?>" <?= $sort === $key ? 'selected' : '' ?>><?= e($option['label']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-dark"><i class="bi bi-search"></i> Lọc</button>
            <a href="<?= e(url('category')) ?>" class="btn btn-outline-secondary">Xóa</a>
        </div>
    </div>
</form>

<div class="text-muted small mb-2">Tìm thấy <strong><?= count($categories) ?></strong> loại phòng</div>

<div class="card">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Loại phòng</th>
                <th class="text-center">Số hạng phòng</th>
                <th class="text-end">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($categories) === 0): ?>
                <tr><td colspan="4" class="text-center text-muted py-4">Không có loại phòng nào</td></tr>
            <?php else: ?>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?= e($category["_id"]) ?></td>
                        <td><?= e($category["name"] ?? '') ?></td>
                        <td class="text-center">
                            <a class="text-decoration-none"
                               href="<?= e(url('room', 'index', ['category_id' => $category['_id']])) ?>">
                                <?= e($category["room_count"] ?? 0) ?>
                            </a>
                        </td>
                        <td class="text-end">
                            <a href="<?= e(url('category', 'edit', ['id' => $category['_id']])) ?>"
                               class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form method="post" action="<?= e(url('category', 'delete')) ?>" class="d-inline"
                                  onsubmit="return confirm('Xóa loại phòng này?');">
                                <input type="hidden" name="id" value="<?= e($category['_id']) ?>">
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
