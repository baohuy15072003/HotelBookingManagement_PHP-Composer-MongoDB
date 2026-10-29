<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Khách hàng</h1>
    <a href="<?= e(url('user', 'create')) ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Thêm khách hàng</a>
</div>

<form method="get" action="index.php" class="card card-body mb-3">
    <input type="hidden" name="c" value="user">
    <div class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label mb-1">Từ khóa</label>
            <input type="search" name="q" class="form-control" placeholder="Mã, tên, email hoặc số điện thoại"
                   value="<?= e($filters['q']) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label mb-1">Địa chỉ</label>
            <select name="address" class="form-select">
                <option value="">Tất cả</option>
                <?php foreach ($addresses as $address): ?>
                    <option value="<?= e($address) ?>" <?= $filters['address'] === $address ? 'selected' : '' ?>><?= e($address) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label mb-1">Sắp xếp</label>
            <select name="sort" class="form-select">
                <?php foreach (User::SORTS as $key => $option): ?>
                    <option value="<?= e($key) ?>" <?= $sort === $key ? 'selected' : '' ?>><?= e($option['label']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-dark"><i class="bi bi-search"></i> Lọc</button>
            <a href="<?= e(url('user')) ?>" class="btn btn-outline-secondary">Xóa</a>
        </div>
    </div>
</form>

<div class="text-muted small mb-2">Tìm thấy <strong><?= count($users) ?></strong> khách hàng</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>Điện thoại</th>
                    <th>Địa chỉ</th>
                    <th class="text-center">Lượt đặt</th>
                    <th class="text-end">Tổng giá trị đặt</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) === 0): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">Không có khách hàng nào</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= e($user['_id']) ?></td>
                            <td><?= e($user['name'] ?? '') ?></td>
                            <td><?= e($user['email'] ?? '') ?></td>
                            <td><?= e($user['phone'] ?? '') ?></td>
                            <td><?= e($user['address'] ?? '') ?></td>
                            <td class="text-center">
                                <a class="text-decoration-none"
                                   href="<?= e(url('booking', 'index', ['q' => $user['_id']])) ?>"><?= e($user['booking_count'] ?? 0) ?></a>
                            </td>
                            <td class="text-end"><?= e(money($user['total_spent'] ?? 0)) ?></td>
                            <td class="text-end">
                                <a href="<?= e(url('user', 'edit', ['id' => $user['_id']])) ?>"
                                   class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <form method="post" action="<?= e(url('user', 'delete')) ?>" class="d-inline"
                                      onsubmit="return confirm('Xóa khách hàng này?');">
                                    <input type="hidden" name="id" value="<?= e($user['_id']) ?>">
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
<div class="small text-muted mt-2">Lượt đặt và tổng giá trị không tính các lượt đã hủy.</div>
