<?php

class CategoryController extends BaseController
{
    protected string $route = "category";

    private Category $model;
    private Room $rooms;

    public function __construct(Category $model, Room $rooms)
    {
        $this->model = $model;
        $this->rooms = $rooms;
    }

    public function index(): void
    {
        $filters = [
            'q' => $this->get('q'),
            'rooms' => $this->get('rooms'),
        ];
        $sort = $this->get('sort');

        $this->render(
            'category/index',
            [
                'categories' => $this->model->search($filters, $sort),
                'filters' => $filters,
                'sort' => $sort,
                'title' => 'Loại phòng',
                'active' => 'category',
            ]);
    }

    public function create(): void
    {
        $this->form(null, ['name' => ''], '');
    }

    public function store(): void
    {
        $data = $this->collect();
        if ($data['name'] === '')
        {
            $this->form(null, $data, 'Vui lòng nhập tên loại phòng.');
            return;
        }
        $this->model->create($data);
        $this->back('Đã thêm loại phòng "' . $data['name'] . '".');
    }

    public function edit(): void
    {
        $category = $this->model->findById($this->id());
        if ($category === null)
        {
            $this->back('Không tìm thấy loại phòng.', true);
        }
        $this->form($category, ['name' => (string) ($category['name'] ?? '')], '');
    }

    public function update(): void
    {
        $category = $this->model->findById($this->id());
        if ($category === null)
        {
            $this->back('Không tìm thấy loại phòng.', true);
        }
        $data = $this->collect();
        if ($data['name'] === '')
        {
            $this->form($category, $data, 'Vui lòng nhập tên loại phòng.');
            return;
        }
        $this->model->update($this->id(), $data);
        $this->back('Đã cập nhật loại phòng "' . $data['name'] . '".');
    }

    public function delete(): void
    {
        $id = $this->id();
        if ($this->rooms->countByCategory($id) > 0)
        {
            $this->back('Không thể xóa: loại phòng này vẫn còn phòng.', true);
        }
        if ($this->model->delete($id))
        {
            $this->back('Đã xóa loại phòng.');
        }
        $this->back('Không tìm thấy loại phòng.', true);
    }

    private function collect(): array
    {
        return ['name' => $this->post('name')];
    }

    private function form(?object $category, array $form, string $error): void
    {
        $this->render(
            'category/form',
            [
                'category' => $category,
                'form' => $form,
                'error' => $error,
                'title' => $category === null ? 'Thêm loại phòng' : 'Sửa loại phòng',
                'active' => 'category',
            ]);
    }
}
