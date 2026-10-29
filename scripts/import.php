<?php

/**
 * Nhập dữ liệu từ các file JSON (định dạng xuất của MongoDB, có {"$date": ...})
 * vào 4 collection: categories, rooms, users, bookings.
 *
 * Mặc định đọc từ thư mục data/ (hoặc truyền đường dẫn thư mục khác làm tham số).
 *
 *   php scripts/import.php            (chỉ chạy khi các collection đang trống)
 *   php scripts/import.php --force    (xóa dữ liệu cũ rồi nhập lại)
 *   php scripts/import.php duong/dan/thu/muc --force
 */

use Dotenv\Dotenv;

if (PHP_SAPI !== 'cli')
{
    exit('Script này chỉ chạy từ dòng lệnh.');
}

$root = dirname(__DIR__);

require_once $root . '/vendor/autoload.php';

Dotenv::createImmutable($root)->safeLoad();

$database = require $root . '/app/config/database.php';

$force = in_array('--force', $argv, true);
$dir = $root . '/data';
foreach (array_slice($argv, 1) as $arg)
{
    if ($arg !== '--force')
    {
        $dir = rtrim($arg, '/\\');
    }
}

$names = ['categories', 'rooms', 'users', 'bookings'];

// Kiểm tra file và dữ liệu hiện có trước khi ghi
foreach ($names as $name)
{
    if (!is_file("$dir/$name.json"))
    {
        fwrite(STDERR, "Không tìm thấy $dir/$name.json\n");
        exit(1);
    }
    if (!$force && $database->selectCollection($name)->countDocuments([]) > 0)
    {
        fwrite(STDERR, "Collection '$name' đã có dữ liệu. Dùng --force để xóa và nhập lại.\n");
        exit(1);
    }
}

foreach ($names as $name)
{
    $json = file_get_contents("$dir/$name.json");

    // File gốc là một mảng JSON: bọc vào object để MongoDB đọc được Extended JSON ($date...)
    $bson = MongoDB\BSON\fromJSON('{"docs":' . $json . '}');
    $parsed = MongoDB\BSON\toPHP($bson, [
        'root' => 'array',
        'document' => 'array',
        'array' => 'array',
    ]);

    $collection = $database->selectCollection($name);
    $collection->deleteMany([]);
    if ($parsed['docs'] !== [])
    {
        $collection->insertMany($parsed['docs']);
    }

    echo "$name: đã nhập " . count($parsed['docs']) . " bản ghi\n";
}
