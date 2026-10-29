<?php

use Dompdf\Dompdf;
use Dompdf\Options;

class HomeController extends BaseController
{
    protected string $route = "home";

    private Report $report;

    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    public function index(): void
    {
        $this->render(
            'home/index',
            [
                'stats' => $this->report->dashboard($this->year()),
                'title' => 'Tổng quan',
                'active' => 'home',
            ]);
    }

    // Xuất báo cáo thống kê ra PDF (theo năm đang chọn, nếu có)
    public function export(): void
    {
        $year = $this->year();
        $html = $this->capture(
            'home/report_pdf',
            [
                'stats' => $this->report->dashboard($year),
                'generatedAt' => date('d/m/Y H:i'),
            ]);

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $fileName = 'bao_cao_thong_ke' . ($year !== '' ? '_' . $year : '') . '_' . date('Ymd') . '.pdf';

        // Xóa mọi output còn sót để file PDF không bị hỏng
        while (ob_get_level() > 0)
        {
            ob_end_clean();
        }
        $dompdf->stream($fileName, ["Attachment" => 0]); // 0: mở trong trình duyệt, 1: tải về
        exit();
    }

    // Năm lọc từ URL, chỉ nhận đúng 4 chữ số
    private function year(): string
    {
        $year = $this->get('year');
        return preg_match('/^\d{4}$/', $year) === 1 ? $year : '';
    }

    // Chạy một view (không có layout) và trả về chuỗi HTML
    private function capture(string $view, array $data): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require dirname(__DIR__) . '/views/' . $view . '.php';
        return (string) ob_get_clean();
    }
}