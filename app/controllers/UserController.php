<?php

class UserController extends BaseController
{
    protected string $route = "user";

    private User $model;
    private Booking $bookings;

    public function __construct(User $model, Booking $bookings)
    {
        $this->model = $model;
        $this->bookings = $bookings;
    }

    public function index(): void
    {
        $filters = [
            'q' => $this->get('q'),
            'address' => $this->get('address'),
        ];
        $sort = $this->get('sort');

        $this->render(
            'user/index',
            [
                'users' => $this->model->search($filters, $sort),
                'filters' => $filters,
                'sort' => $sort,
                'addresses' => $this->model->addresses(),
                'title' => 'Khách hàng',
                'active' => 'user',
            ]);
    }

    public function create(): void
    {
        $this->form(null, ['name' => '', 'email' => '', 'phone' => '', 'address' => ''], '');
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
        $this->model->create($form);
        $this->back('Đã thêm khách hàng "' . $form['name'] . '".');
    }

    public function edit(): void
    {
        $user = $this->model->findById($this->id());
        if ($user === null)
        {
            $this->back('Không tìm thấy khách hàng.', true);
        }
        $this->form($user, [
            'name' => (string) ($user['name'] ?? ''),
            'email' => (string) ($user['email'] ?? ''),
            'phone' => (string) ($user['phone'] ?? ''),
            'address' => (string) ($user['address'] ?? ''),
        ], '');
    }

    public function update(): void
    {
        $user = $this->model->findById($this->id());
        if ($user === null)
        {
            $this->back('Không tìm thấy khách hàng.', true);
        }
        $form = $this->formInput();
        $error = $this->validate($form);
        if ($error !== '')
        {
            $this->form($user, $form, $error);
            return;
        }
        $this->model->update($this->id(), $form);
        $this->back('Đã cập nhật khách hàng "' . $form['name'] . '".');
    }

    public function delete(): void
    {
        $id = $this->id();
        if ($this->bookings->countByUser($id) > 0)
        {
            $this->back('Không thể xóa: khách hàng đã có lượt đặt phòng.', true);
        }
        if ($this->model->delete($id))
        {
            $this->back('Đã xóa khách hàng.');
        }
        $this->back('Không tìm thấy khách hàng.', true);
    }

    private function formInput(): array
    {
        return [
            'name' => $this->post('name'),
            'email' => $this->post('email'),
            'phone' => $this->post('phone'),
            'address' => $this->post('address'),
        ];
    }

    private function validate(array $form): string
    {
        if ($form['name'] === '')
        {
            return 'Vui lòng nhập họ tên.';
        }
        if ($form['email'] === '' || !filter_var($form['email'], FILTER_VALIDATE_EMAIL))
        {
            return 'Email không hợp lệ.';
        }
        return '';
    }

    private function form(?object $user, array $form, string $error): void
    {
        $this->render(
            'user/form',
            [
                'user' => $user,
                'form' => $form,
                'error' => $error,
                'title' => $user === null ? 'Thêm khách hàng' : 'Sửa khách hàng',
                'active' => 'user',
            ]);
    }
}
