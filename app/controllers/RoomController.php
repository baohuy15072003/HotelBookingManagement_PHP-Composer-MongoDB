<?php

class RoomController extends BaseController
{
    protected string $route = "room";

    private Room $model;
    private Category $categories;
    private Booking $bookings;

    public function __construct(Room $model, Category $categories, Booking $bookings)
    {
        $this->model = $model;
        $this->categories = $categories;
        $this->bookings = $bookings;
    }

    public function index(): void
    {
        $filters = [
            'q' => $this->get('q'),
            'category_id' => $this->get('category_id'),
            'amenity' => $this->get('amenity'),
            'price_min' => $this->get('price_min'),
            'price_max' => $this->get('price_max'),
            'stock' => $this->get('stock'),
        ];
        $sort = $this->get('sort');

        $this->render(
            'room/index',
            [
                'rooms' => $this->model->search($filters, $sort),
                'filters' => $filters,
                'sort' => $sort,
                'categories' => $this->categories->findAll(),
                'amenities' => $this->model->amenities(),
                'title' => 'Phòng',
                'active' => 'room',
            ]);
    }

    public function create(): void
    {
        $this->form(null, [
            'name' => '', 'category_id' => '', 'amenities' => '', 'price_per_night' => '', 'stock' => '1',
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
        $this->model->create($this->toData($form));
        $this->back('Đã thêm phòng "' . $form['name'] . '".');
    }

    public function edit(): void
    {
        $room = $this->model->findById($this->id());
        if ($room === null)
        {
            $this->back('Không tìm thấy phòng.', true);
        }
        $this->form($room, [
            'name' => (string) ($room['name'] ?? ''),
            'category_id' => (string) ($room['category_id'] ?? ''),
            'amenities' => implode(', ', to_list($room['amenities'] ?? [])),
            'price_per_night' => (string) ($room['price_per_night'] ?? ''),
            'stock' => (string) ($room['stock'] ?? '1'),
        ], '');
    }

    public function update(): void
    {
        $room = $this->model->findById($this->id());
        if ($room === null)
        {
            $this->back('Không tìm thấy phòng.', true);
        }
        $form = $this->formInput();
        $error = $this->validate($form);
        if ($error !== '')
        {
            $this->form($room, $form, $error);
            return;
        }
        $this->model->update($this->id(), $this->toData($form));
        $this->back('Đã cập nhật phòng "' . $form['name'] . '".');
    }

    public function delete(): void
    {
        $id = $this->id();
        if ($this->bookings->countByRoom($id) > 0)
        {
            $this->back('Không thể xóa: phòng đã có trong lượt đặt phòng.', true);
        }
        if ($this->model->delete($id))
        {
            $this->back('Đã xóa phòng.');
        }
        $this->back('Không tìm thấy phòng.', true);
    }

    private function formInput(): array
    {
        return [
            'name' => $this->post('name'),
            'category_id' => $this->post('category_id'),
            'amenities' => $this->post('amenities'),
            'price_per_night' => $this->post('price_per_night'),
            'stock' => $this->post('stock'),
        ];
    }

    private function validate(array $form): string
    {
        if ($form['name'] === '')
        {
            return 'Vui lòng nhập tên phòng.';
        }
        if ($this->categories->findById($form['category_id']) === null)
        {
            return 'Vui lòng chọn loại phòng.';
        }
        if (!ctype_digit($form['price_per_night']) || (int) $form['price_per_night'] <= 0)
        {
            return 'Giá mỗi đêm phải là số nguyên dương.';
        }
        if (!ctype_digit($form['stock']))
        {
            return 'Số phòng phải là số nguyên không âm.';
        }
        return '';
    }

    private function toData(array $form): array
    {
        $amenities = array_values(array_filter(
            array_map('trim', explode(',', $form['amenities'])),
            static fn($a) => $a !== ''
        ));

        return [
            'name' => $form['name'],
            'category_id' => $form['category_id'],
            'amenities' => $amenities,
            'price_per_night' => (int) $form['price_per_night'],
            'stock' => (int) $form['stock'],
        ];
    }

    private function form(?object $room, array $form, string $error): void
    {
        $this->render(
            'room/form',
            [
                'room' => $room,
                'form' => $form,
                'error' => $error,
                'categories' => $this->categories->findAll(),
                'title' => $room === null ? 'Thêm phòng' : 'Sửa phòng',
                'active' => 'room',
            ]);
    }
}
