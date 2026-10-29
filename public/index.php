<?php

use Dotenv\Dotenv;

$root = dirname(__DIR__);

require_once $root . '/vendor/autoload.php';

Dotenv::createImmutable($root)->safeLoad();

date_default_timezone_set('Asia/Ho_Chi_Minh');

require_once $root . '/app/helpers.php';

require_once $root . '/app/models/BaseModel.php';
require_once $root . '/app/models/Category.php';
require_once $root . '/app/models/Room.php';
require_once $root . '/app/models/User.php';
require_once $root . '/app/models/Booking.php';
require_once $root . '/app/models/Report.php';

require_once $root . '/app/controllers/BaseController.php';
require_once $root . '/app/controllers/HomeController.php';
require_once $root . '/app/controllers/CategoryController.php';
require_once $root . '/app/controllers/RoomController.php';
require_once $root . '/app/controllers/UserController.php';
require_once $root . '/app/controllers/BookingController.php';

try
{
    $database = require $root . '/app/config/database.php';

    $category = new Category($database->selectCollection('categories'));
    $room = new Room($database->selectCollection('rooms'));
    $user = new User($database->selectCollection('users'));
    $booking = new Booking($database->selectCollection('bookings'));

    $controllers = [
        'home'     => new HomeController(new Report($database)),
        'category' => new CategoryController($category, $room),
        'room'     => new RoomController($room, $category, $booking),
        'user'     => new UserController($user, $booking),
        'booking'  => new BookingController($booking, $user, $room),
    ];

    $actions = ['index', 'show', 'create', 'store', 'edit', 'update', 'delete', 'export'];

    $c = $_GET['c'] ?? 'home';
    $a = $_GET['a'] ?? 'index';

    if (!is_string($c) || !isset($controllers[$c])
        || !is_string($a) || !in_array($a, $actions, true)
        || !is_callable([$controllers[$c], $a]))
    {
        http_response_code(404);
        render_error('Trang bạn tìm không tồn tại.');
        exit;
    }

    $controllers[$c]->$a();
}
catch (Throwable $e)
{
    http_response_code(500);
    render_error('Lỗi: ' . $e->getMessage());
}
