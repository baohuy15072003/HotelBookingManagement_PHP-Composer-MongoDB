<?php
/** $stats: kết quả Report::dashboard(); $generatedAt: thời điểm xuất */
$year = $stats['year'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo thống kê</title>
    <style>
        body { font-family: "DejaVu Sans", sans-serif; font-size: 11px; color: #333; }
        h1 { text-align: center; color: #1a252f; font-size: 18px; margin: 0 0 4px; }
        h2 { font-size: 13px; color: #1a252f; margin: 18px 0 6px; border-bottom: 2px solid #34495e; padding-bottom: 3px; }
        .sub { text-align: center; color: #777; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        th, td { border: 1px solid #bdc3c7; padding: 5px 7px; text-align: left; }
        th { background-color: #34495e; color: #fff; }
        tr:nth-child(even) td { background-color: #f9f9f9; }
        .c { text-align: center; }
        .r { text-align: right; }
        .note { color: #777; font-size: 9px; margin-top: 3px; }
        .kpi td { width: 25%; text-align: center; border: 1px solid #bdc3c7; padding: 8px; background: #f4f6f7; }
        .kpi .v { font-size: 14px; font-weight: bold; }
        .kpi .l { color: #777; font-size: 9px; }
    </style>
</head>
<body>
    <h1>BÁO CÁO THỐNG KÊ KHÁCH SẠN NBH</h1>
    <div class="sub">
        <?= $year !== '' ? 'Năm ' . e($year) . ' &middot; ' : '' ?>Xuất lúc <?= e($generatedAt) ?>
    </div>

    <table class="kpi">
        <tr>
            <td><div class="v"><?= e(money($stats['revenue'])) ?></div><div class="l">Doanh thu (đã thanh toán)</div></td>
            <td><div class="v"><?= e(money($stats['pending'])) ?></div><div class="l">Chưa thu</div></td>
            <td><div class="v"><?= $stats['validBookings'] ?> / <?= $stats['bookings'] ?></div><div class="l">Lượt đặt (không tính đã hủy)</div></td>
            <td><div class="v"><?= $stats['users'] ?></div><div class="l">Khách hàng</div></td>
        </tr>
    </table>

    <h2>Doanh thu theo năm</h2>
    <table>
        <thead><tr><th>Năm</th><th class="c">Số lượt đặt</th><th class="r">Doanh thu</th></tr></thead>
        <tbody>
            <?php if ($stats['byYear'] === []): ?><tr><td colspan="3" class="c">Chưa có dữ liệu.</td></tr><?php endif; ?>
            <?php foreach ($stats['byYear'] as $row): ?>
                <tr>
                    <td><?= e($row['label']) ?></td>
                    <td class="c"><?= $row['bookings'] ?></td>
                    <td class="r"><?= e(money($row['revenue'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Doanh thu theo tháng<?= $year !== '' ? ' — năm ' . e($year) : '' ?></h2>
    <table>
        <thead><tr><th>Tháng</th><th class="c">Số lượt đặt</th><th class="r">Doanh thu</th></tr></thead>
        <tbody>
            <?php if ($stats['byMonth'] === []): ?><tr><td colspan="3" class="c">Chưa có dữ liệu.</td></tr><?php endif; ?>
            <?php foreach ($stats['byMonth'] as $row): ?>
                <tr>
                    <td><?= e(substr($row['label'], 5, 2) . '/' . substr($row['label'], 0, 4)) ?></td>
                    <td class="c"><?= $row['bookings'] ?></td>
                    <td class="r"><?= e(money($row['revenue'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="note">Doanh thu tính theo ngày nhận phòng; chỉ gồm lượt đặt đã thanh toán và chưa hủy.</div>

    <h2>Top 5 loại phòng được đặt nhiều nhất</h2>
    <table>
        <thead><tr><th class="c" style="width:30px">#</th><th>Loại phòng</th><th class="c">Lượt đặt</th><th class="c">Phòng-đêm</th></tr></thead>
        <tbody>
            <?php if ($stats['topCategories'] === []): ?><tr><td colspan="4" class="c">Chưa có dữ liệu.</td></tr><?php endif; ?>
            <?php foreach ($stats['topCategories'] as $i => $row): ?>
                <tr>
                    <td class="c"><?= $i + 1 ?></td>
                    <td><?= e($row['name']) ?></td>
                    <td class="c"><?= $row['bookings'] ?></td>
                    <td class="c"><?= $row['room_nights'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Top 5 khách hàng đặt nhiều nhất</h2>
    <table>
        <thead><tr><th class="c" style="width:30px">#</th><th>Khách hàng</th><th class="c">Lượt đặt</th><th class="r">Tổng giá trị</th></tr></thead>
        <tbody>
            <?php if ($stats['topUsers'] === []): ?><tr><td colspan="4" class="c">Chưa có dữ liệu.</td></tr><?php endif; ?>
            <?php foreach ($stats['topUsers'] as $i => $row): ?>
                <tr>
                    <td class="c"><?= $i + 1 ?></td>
                    <td><?= e($row['name']) ?></td>
                    <td class="c"><?= $row['bookings'] ?></td>
                    <td class="r"><?= e(money($row['total'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Trạng thái đặt phòng</h2>
    <table>
        <thead><tr><th>Theo xử lý</th><th class="c">Số lượt</th><th>Theo thanh toán</th><th class="c">Số lượt</th></tr></thead>
        <tbody>
            <?php
            $b = array_keys($stats['byBookingStatus']);
            $p = array_keys($stats['byPaymentStatus']);
            $n = max(count($b), count($p), 1);
            for ($i = 0; $i < $n; $i++): ?>
                <tr>
                    <td><?= isset($b[$i]) ? e($b[$i]) : '' ?></td>
                    <td class="c"><?= isset($b[$i]) ? $stats['byBookingStatus'][$b[$i]] : '' ?></td>
                    <td><?= isset($p[$i]) ? e($p[$i]) : '' ?></td>
                    <td class="c"><?= isset($p[$i]) ? $stats['byPaymentStatus'][$p[$i]] : '' ?></td>
                </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <h2>Số phòng theo loại phòng</h2>
    <table>
        <thead><tr><th>Loại phòng</th><th class="c">Số hạng phòng</th><th class="c">Tổng số phòng</th></tr></thead>
        <tbody>
            <?php if ($stats['roomsByCategory'] === []): ?><tr><td colspan="3" class="c">Chưa có dữ liệu.</td></tr><?php endif; ?>
            <?php foreach ($stats['roomsByCategory'] as $row): ?>
                <tr>
                    <td><?= e($row['name']) ?></td>
                    <td class="c"><?= $row['types'] ?></td>
                    <td class="c"><?= $row['stock'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Lịch nhận phòng sắp tới</h2>
    <table>
        <thead><tr><th>Mã</th><th>Khách hàng</th><th>Nhận phòng</th><th>Trả phòng</th><th>Trạng thái</th></tr></thead>
        <tbody>
            <?php if ($stats['upcoming'] === []): ?><tr><td colspan="5" class="c">Không có lượt nhận phòng nào sắp tới.</td></tr><?php endif; ?>
            <?php foreach ($stats['upcoming'] as $booking): ?>
                <tr>
                    <td><?= e($booking['_id']) ?></td>
                    <td><?= e($booking['user_name'] ?? '') ?: e($booking['user_id'] ?? '') ?></td>
                    <td><?= e(format_date($booking['check_in_date'] ?? null)) ?></td>
                    <td><?= e(format_date($booking['check_out_date'] ?? null)) ?></td>
                    <td><?= e($booking['booking_status'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>