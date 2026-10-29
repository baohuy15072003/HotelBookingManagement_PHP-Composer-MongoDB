<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Phòng</h1>
    <a href="<?= e(url('room', 'create')) ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Thêm phòng</a>
</div>

<form method="get" action="index.php" class="card card-body mb-3">
    <input type="hidden" name="c" value="room">
    <div class="row g-2 align-items-end">
        <div class="col-lg-4">
            <label class="form-label mb-1">Từ khóa</label>
            <input type="search" name="q" class="form-control" placeholder="Mã hoặc tên phòng"
                   value="<?= e($filters['q']) ?>">
        </div>
        <div class="col-6 col-lg-2">
            <label class="form-label mb-1">Loại phòng</label>
            <select name="category_id" class="form-select">
                <option value="">Tất cả</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= e($category['_id']) ?>" <?= $filters['category_id'] === $category['_id'] ? 'selected' : '' ?>>
                        <?= e($category['name'] ?? '') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-lg-2">
            <label class="form-label mb-1">Tiện nghi</label>
            <select name="amenity" class="form-select">
                <option value="">Tất cả</option>
                <?php foreach ($amenities as $amenity): ?>
                    <option value="<?= e($amenity) ?>" <?= $filters['amenity'] === $amenity ? 'selected' : '' ?>><?= e($amenity) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-lg-2">
            <label class="form-label mb-1">Giá/đêm từ (₫)</label>
            <input type="number" name="price_min" class="form-control" min="0" step="100000"
                   value="<?= e($filters['price_min']) ?>">
        </div>
        <div class="col-6 col-lg-2">
            <label class="form-label mb-1">Giá/đêm đến (₫)</label>
            <input type="number" name="price_max" class="form-control" min="0" step="100000"
                   value="<?= e($filters['price_max']) ?>">
        </div>
        <div class="col-6 col-lg-3">
            <label class="form-label mb-1">Số phòng</label>
            <select name="stock" class="form-select">
                <option value="">Tất cả</option>
                <?php foreach (Room::STOCK_FILTERS as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= $filters['stock'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-lg-3">
            <label class="form-label mb-1">Sắp xếp</label>
            <select name="sort" class="form-select">
                <?php foreach (Room::SORTS as $key => $option): ?>
                    <option value="<?= e($key) ?>" <?= $sort === $key ? 'selected' : '' ?>><?= e($option['label']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-lg-auto d-flex gap-2">
            <button class="btn btn-dark"><i class="bi bi-search"></i> Tìm / Lọc</button>
            <a href="<?= e(url('room')) ?>" class="btn btn-outline-secondary">Xóa bộ lọc</a>
        </div>
    </div>
</form>

<div class="text-muted small mb-2">Tìm thấy <strong><?= count($rooms) ?></strong> phòng</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Phòng</th>
                    <th>Loại phòng</th>
                    <th>Tiện nghi</th>
                    <th class="text-end">Giá/đêm</th>
                    <th class="text-center">Số phòng</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($rooms) === 0): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">Không có phòng nào</td></tr>
                <?php else: ?>
                    <?php foreach ($rooms as $room): ?>
                        <tr>
                            <td><?= e($room['_id']) ?></td>
                            <td><?= e($room['name'] ?? '') ?></td>
                            <td><?= e($room['category_name'] ?? '') ?: '—' ?></td>
                            <td>
                                <?php foreach (to_list($room['amenities'] ?? []) as $amenity): ?>
                                    <span class="badge bg-light text-dark border"><?= e($amenity) ?></span>
                                <?php endforeach; ?>
                            </td>
                            <td class="text-end"><?= e(money($room['price_per_night'] ?? 0)) ?></td>
                            <td class="text-center"><span class="badge bg-secondary"><?= e($room['stock'] ?? 0) ?></span></td>
                            <td class="text-end">
                                <a href="<?= e(url('room', 'edit', ['id' => $room['_id']])) ?>"
                                   class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <form method="post" action="<?= e(url('room', 'delete')) ?>" class="d-inline"
                                      onsubmit="return confirm('Xóa phòng này?');">
                                    <input type="hidden" name="id" value="<?= e($room['_id']) ?>">
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
