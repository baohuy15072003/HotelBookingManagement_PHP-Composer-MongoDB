<?php

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Tạo URL: index.php?c=<controller>&a=<action>&...
 */
function url(string $controller = 'home', string $action = 'index', array $params = []): string
{
    return 'index.php?' . http_build_query(['c' => $controller, 'a' => $action] + $params);
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function money($value): string
{
    return number_format((float) $value, 0, ',', '.') . ' ₫';
}

function format_date($value, string $format = 'd/m/Y'): string
{
    if ($value instanceof MongoDB\BSON\UTCDateTime)
    {
        return $value->toDateTime()
            ->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh'))
            ->format($format);
    }
    return '';
}

/**
 * Số đêm giữa ngày nhận phòng và ngày trả phòng.
 */
function nights_between($checkIn, $checkOut): int
{
    if ($checkIn instanceof MongoDB\BSON\UTCDateTime && $checkOut instanceof MongoDB\BSON\UTCDateTime)
    {
        $seconds = $checkOut->toDateTime()->getTimestamp() - $checkIn->toDateTime()->getTimestamp();
        return max(0, (int) round($seconds / 86400));
    }
    return 0;
}

/**
 * Chuyển mảng BSON (BSONArray) hoặc mảng PHP thành mảng PHP thường.
 */
function to_list($value): array
{
    if (is_array($value))
    {
        return array_values($value);
    }
    if ($value instanceof Traversable)
    {
        return array_values(iterator_to_array($value, false));
    }
    return [];
}

/**
 * Màu badge Bootstrap theo trạng thái đặt phòng / thanh toán.
 */
function badge_class(string $status): string
{
    $map = [
        'Đã thanh toán'   => 'bg-success',
        'Chưa thanh toán' => 'bg-warning text-dark',
        'Đang xử lý'      => 'bg-warning text-dark',
        'Đã xác nhận'     => 'bg-primary',
        'Đã nhận phòng'   => 'bg-info text-dark',
        'Đã trả phòng'    => 'bg-success',
        'Đã Hủy'          => 'bg-secondary',
    ];
    return $map[$status] ?? 'bg-light text-dark';
}

function render_error(string $message): void
{
    $title = 'Lỗi';
    $active = '';
    $error = $message;
    $contentView = __DIR__ . '/views/error.php';
    require __DIR__ . '/views/layouts/main.php';
}
