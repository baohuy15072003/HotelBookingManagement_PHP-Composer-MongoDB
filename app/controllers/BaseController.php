<?php

abstract class BaseController
{
    // Tên controller trên URL (dùng cho redirect)
    protected string $route = "home";

    protected function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $contentView = dirname(__DIR__) . '/views/' . $view . '.php';
        require dirname(__DIR__) . '/views/layouts/main.php';
    }

    protected function get(string $key): string
    {
        $value = $_GET[$key] ?? '';
        return is_string($value) ? trim($value) : '';
    }

    protected function post(string $key): string
    {
        $value = $_POST[$key] ?? '';
        return is_string($value) ? trim($value) : '';
    }

    protected function id(): string
    {
        $id = $_POST['id'] ?? $_GET['id'] ?? '';
        return is_string($id) ? trim($id) : '';
    }

    // Quay về danh sách kèm thông báo (thành công hoặc lỗi)
    protected function back(string $message, bool $isError = false): void
    {
        redirect(url($this->route, 'index', [$isError ? 'err' : 'msg' => $message]));
    }
}
