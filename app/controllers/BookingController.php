<?php

use MongoDB\BSON\UTCDateTime;

class BookingController extends BaseController
{
    protected string $route = "booking";

    private Booking $model;
    private User $users;
    private Room $rooms;

    public function __construct(Booking $model, User $users, Room $rooms)
    {
        $this->model = $model;
        $this->users = $users;
        $this->rooms = $rooms;
    }

    public function index(): void
    {
        $filters = [
            'q' => $this->get('q'),
            'payment_status' => $this->get('payment_status'),
            'booking_status' => $this->get('booking_status'),
            'date_from' => $this->get('date_from'),
            'date_to' => $this->get('date_to'),
            'total_min' => $this->get('total_min'),
            'total_max' => $this->get('total_max'),
        ];
        $sort = $this->get('sort');

        $this->render(
            'booking/index',
            [
                'bookings' => $this->model->search($filters, $sort),
                'filters' => $filters,
                'sort' => $sort,
                'title' => 'Đặt phòng',
                'active' => 'booking',
            ]);
    }

    // Xem chi tiết lượt đặt phòng
    public function show(): void
    {
        $booking = $this->model->findById($this->id());
        if ($booking === null)
        {
            $this->back('Không tìm thấy lượt đặt phòng.', true);
        }

        $lines = [];
        foreach (to_list($booking['items'] ?? []) as $item)
        {
            $room = $this->rooms->findById((string) $item['room_id']);
            $price = $room !== null ? (int) $room['price_per_night'] : 0;
            $quantity = (int) $item['quantity'];
            $nights = (int) $item['nights'];
            $lines[] = [
                'room_id' => (string) $item['room_id'],
                'name' => $room !== null ? (string) $room['name'] : '(phòng đã bị xóa)',
                'price' => $price,
                'quantity' => $quantity,
                'nights' => $nights,
                'subtotal' => $price * $quantity * $nights,
            ];
        }

        $this->render(
            'booking/show',
            [
                'booking' => $booking,
                'customer' => $this->users->findById((string) ($booking['user_id'] ?? '')),
                'lines' => $lines,
                'title' => 'Đặt phòng ' . $booking['_id'],
                'active' => 'booking',
            ]);
    }

    public function create(): void
    {
        $this->form(null, [
            'user_id' => '',
            'check_in_date' => date('Y-m-d'),
            'check_out_date' => date('Y-m-d', strtotime('+1 day')),
            'payment_status' => Booking::PAYMENT_STATUSES[0],
            'booking_status' => Booking::BOOKING_STATUSES[0],
            'items' => [],
        ], '');
    }

    public function store(): void
    {
        $form = $this->formInput();
        $error = $this->validate($form);
        if ($error !== '')
        {
            $this->form(null, $form, $error);
            return;
        }
        $id = $this->model->create($this->toData($form));
        $this->back('Đã tạo lượt đặt phòng ' . $id . '.');
    }

    public function edit(): void
    {
        $booking = $this->model->findById($this->id());
        if ($booking === null)
        {
            $this->back('Không tìm thấy lượt đặt phòng.', true);
        }

        $items = [];
        foreach (to_list($booking['items'] ?? []) as $item)
        {
            $items[] = [
                'room_id' => (string) $item['room_id'],
                'quantity' => (string) $item['quantity'],
            ];
        }

        $this->form($booking, [
            'user_id' => (string) ($booking['user_id'] ?? ''),
            'check_in_date' => format_date($booking['check_in_date'] ?? null, 'Y-m-d'),
            'check_out_date' => format_date($booking['check_out_date'] ?? null, 'Y-m-d'),
            'payment_status' => (string) ($booking['payment_status'] ?? ''),
            'booking_status' => (string) ($booking['booking_status'] ?? ''),
            'items' => $items,
        ], '');
    }

    public function update(): void
    {
        $booking = $this->model->findById($this->id());
        if ($booking === null)
        {
            $this->back('Không tìm thấy lượt đặt phòng.', true);
        }
        $form = $this->formInput();
        $error = $this->validate($form);
        if ($error !== '')
        {
            $this->form($booking, $form, $error);
            return;
        }
        $this->model->update($this->id(), $this->toData($form));
        $this->back('Đã cập nhật lượt đặt phòng ' . $this->id() . '.');
    }

    public function delete(): void
    {
        if ($this->model->delete($this->id()))
        {
            $this->back('Đã xóa lượt đặt phòng.');
        }
        $this->back('Không tìm thấy lượt đặt phòng.', true);
    }

    private function formInput(): array
    {
        $ids = $_POST['room_id'] ?? [];
        $quantities = $_POST['quantity'] ?? [];
        $items = [];
        if (is_array($ids))
        {
            foreach ($ids as $i => $roomId)
            {
                $roomId = is_string($roomId) ? trim($roomId) : '';
                $quantity = is_array($quantities) && is_string($quantities[$i] ?? null)
                    ? trim($quantities[$i])
                    : '';
                if ($roomId === '' && $quantity === '')
                {
                    continue;
                }
                $items[] = ['room_id' => $roomId, 'quantity' => $quantity];
            }
        }

        return [
            'user_id' => $this->post('user_id'),
            'check_in_date' => $this->post('check_in_date'),
            'check_out_date' => $this->post('check_out_date'),
            'payment_status' => $this->post('payment_status'),
            'booking_status' => $this->post('booking_status'),
            'items' => $items,
        ];
    }

    // Ngày nhận/trả phòng lưu ở 00:00 UTC (giống dữ liệu mẫu)
    private function timestamp(string $date): ?int
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) !== 1)
        {
            return null;
        }
        $time = strtotime($date . ' 00:00:00 UTC');
        return $time === false ? null : $time;
    }

    private function validate(array $form): string
    {
        if ($this->users->findById($form['user_id']) === null)
        {
            return 'Vui lòng chọn khách hàng.';
        }

        $in = $this->timestamp($form['check_in_date']);
        $out = $this->timestamp($form['check_out_date']);
        if ($in === null || $out === null)
        {
            return 'Ngày nhận phòng / trả phòng không hợp lệ.';
        }
        if ($out <= $in)
        {
            return 'Ngày trả phòng phải sau ngày nhận phòng.';
        }

        if (!in_array($form['payment_status'], Booking::PAYMENT_STATUSES, true))
        {
            return 'Trạng thái thanh toán không hợp lệ.';
        }
        if (!in_array($form['booking_status'], Booking::BOOKING_STATUSES, true))
        {
            return 'Trạng thái đặt phòng không hợp lệ.';
        }
        if ($form['items'] === [])
        {
            return 'Lượt đặt cần ít nhất một phòng.';
        }
        foreach ($form['items'] as $item)
        {
            $room = $this->rooms->findById($item['room_id']);
            if ($room === null)
            {
                return 'Có dòng phòng chưa được chọn.';
            }
            if (!ctype_digit($item['quantity']) || (int) $item['quantity'] < 1)
            {
                return 'Số phòng đặt phải là số nguyên từ 1 trở lên.';
            }
            if ((int) $item['quantity'] > (int) ($room['stock'] ?? 0))
            {
                return 'Số phòng đặt vượt quá số phòng hiện có của "' . $room['name'] . '".';
            }
        }
        return '';
    }

    // Số đêm lấy từ ngày nhận/trả phòng; tổng tiền = giá/đêm x số phòng x số đêm
    private function toData(array $form): array
    {
        $in = $this->timestamp($form['check_in_date']);
        $out = $this->timestamp($form['check_out_date']);
        $nights = (int) round(($out - $in) / 86400);

        $items = [];
        $total = 0;
        foreach ($form['items'] as $item)
        {
            $room = $this->rooms->findById($item['room_id']);
            $quantity = (int) $item['quantity'];
            $items[] = [
                'room_id' => $item['room_id'],
                'quantity' => $quantity,
                'nights' => $nights,
            ];
            $total += (int) $room['price_per_night'] * $quantity * $nights;
        }

        return [
            'user_id' => $form['user_id'],
            'items' => $items,
            'total' => $total,
            'check_in_date' => new UTCDateTime($in * 1000),
            'check_out_date' => new UTCDateTime($out * 1000),
            'payment_status' => $form['payment_status'],
            'booking_status' => $form['booking_status'],
        ];
    }

    private function form(?object $booking, array $form, string $error): void
    {
        $this->render(
            'booking/form',
            [
                'booking' => $booking,
                'form' => $form,
                'error' => $error,
                'users' => $this->users->findAll(),
                'rooms' => $this->rooms->findAll(),
                'title' => $booking === null ? 'Tạo lượt đặt phòng' : 'Sửa lượt đặt phòng',
                'active' => 'booking',
            ]);
    }
}
