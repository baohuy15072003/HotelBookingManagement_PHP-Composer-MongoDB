<?php

use MongoDB\BSON\UTCDateTime;
use MongoDB\Database;

/**
 * Thống kê cho trang Tổng quan.
 *
 * Quy ước:
 *  - Lượt đặt hợp lệ  = lượt đặt không ở trạng thái "Đã Hủy".
 *  - Doanh thu        = tổng tiền các lượt đặt hợp lệ và "Đã thanh toán".
 *  - Doanh thu theo tháng / năm tính theo ngày nhận phòng (check_in_date).
 *  - Top 5 loại phòng / khách hàng xếp theo số lượt đặt hợp lệ.
 */
class Report
{
    private Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function dashboard(string $year = ''): array
    {
        $bookings = $this->db->selectCollection("bookings");

        $valid = ['$ne' => ['$booking_status', Booking::CANCELLED]];
        $paid = ['$eq' => ['$payment_status', "Đã thanh toán"]];
        $revenueExpr = ['$cond' => [['$and' => [$valid, $paid]], '$total', 0]];
        $countExpr = ['$cond' => [$valid, 1, 0]];

        // Tổng quan
        $revenue = 0;
        $pending = 0;
        $validCount = 0;
        $rows = $bookings->aggregate([
            ['$group' => [
                "_id" => null,
                "revenue" => ['$sum' => $revenueExpr],
                "pending" => ['$sum' => ['$cond' => [['$and' => [$valid, ['$eq' => ['$payment_status', "Chưa thanh toán"]]]], '$total', 0]]],
                "valid" => ['$sum' => $countExpr],
            ]],
        ]);
        foreach ($rows as $row)
        {
            $revenue = (int) $row["revenue"];
            $pending = (int) $row["pending"];
            $validCount = (int) $row["valid"];
        }

        // Doanh thu theo năm
        $byYear = $this->revenueBy($bookings, "%Y", $revenueExpr, $countExpr);

        // Doanh thu theo tháng (nếu chọn năm thì hiện đủ 12 tháng của năm đó)
        $byMonth = $this->revenueBy($bookings, "%Y-%m", $revenueExpr, $countExpr);
        if ($year !== '')
        {
            $indexed = [];
            foreach ($byMonth as $row)
            {
                $indexed[$row["label"]] = $row;
            }
            $byMonth = [];
            for ($m = 1; $m <= 12; $m++)
            {
                $label = sprintf("%s-%02d", $year, $m);
                $byMonth[] = $indexed[$label] ?? ["label" => $label, "revenue" => 0, "bookings" => 0];
            }
        }

        return [
            "categories"      => $this->db->selectCollection("categories")->countDocuments([]),
            "rooms"           => $this->db->selectCollection("rooms")->countDocuments([]),
            "users"           => $this->db->selectCollection("users")->countDocuments([]),
            "bookings"        => $bookings->countDocuments([]),
            "validBookings"   => $validCount,
            "revenue"         => $revenue,
            "pending"         => $pending,
            "year"            => $year,
            "years"           => array_column($byYear, "label"),
            "byYear"          => $byYear,
            "byMonth"         => $byMonth,
            "byBookingStatus" => $this->countBy($bookings, "booking_status"),
            "byPaymentStatus" => $this->countBy($bookings, "payment_status"),
            "topCategories"   => $this->topCategories($bookings),
            "topUsers"        => $this->topUsers($bookings),
            "roomsByCategory" => $this->roomsByCategory(),
            "upcoming"        => $this->upcoming($bookings),
        ];
    }

    // Doanh thu + số lượt đặt hợp lệ, gom theo định dạng ngày ($format)
    private function revenueBy($bookings, string $format, array $revenueExpr, array $countExpr): array
    {
        $result = [];
        $rows = $bookings->aggregate([
            ['$group' => [
                "_id" => ['$dateToString' => [
                    "format" => $format,
                    "date" => '$check_in_date',
                    "timezone" => "Asia/Ho_Chi_Minh"
                ]],
                "revenue" => ['$sum' => $revenueExpr],
                "bookings" => ['$sum' => $countExpr],
            ]],
            ['$sort' => ["_id" => 1]],
        ]);
        foreach ($rows as $row)
        {
            $result[] = [
                "label" => (string) $row["_id"],
                "revenue" => (int) $row["revenue"],
                "bookings" => (int) $row["bookings"],
            ];
        }
        return $result;
    }

    // Top 5 loại phòng: số lượt đặt (mỗi lượt đặt tính 1 lần cho mỗi loại) và tổng số phòng-đêm
    private function topCategories($bookings): array
    {
        $result = [];
        $rows = $bookings->aggregate([
            ['$match' => ["booking_status" => ['$ne' => Booking::CANCELLED]]],
            ['$unwind' => '$items'],
            ['$lookup' => [
                "from" => "rooms",
                "localField" => "items.room_id",
                "foreignField" => "_id",
                "as" => "room"
            ]],
            ['$unwind' => '$room'],
            ['$group' => [
                "_id" => ["booking" => '$_id', "category" => '$room.category_id'],
                "room_nights" => ['$sum' => ['$multiply' => ['$items.quantity', '$items.nights']]],
            ]],
            ['$group' => [
                "_id" => '$_id.category',
                "bookings" => ['$sum' => 1],
                "room_nights" => ['$sum' => '$room_nights'],
            ]],
            ['$lookup' => [
                "from" => "categories",
                "localField" => "_id",
                "foreignField" => "_id",
                "as" => "category"
            ]],
            ['$unwind' => ["path" => '$category', "preserveNullAndEmptyArrays" => true]],
            ['$project' => [
                "name" => ['$ifNull' => ['$category.name', '$_id']],
                "bookings" => 1,
                "room_nights" => 1,
            ]],
            ['$sort' => ["bookings" => -1, "room_nights" => -1, "_id" => 1]],
            ['$limit' => 5],
        ]);
        foreach ($rows as $row)
        {
            $result[] = [
                "id" => (string) $row["_id"],
                "name" => (string) $row["name"],
                "bookings" => (int) $row["bookings"],
                "room_nights" => (int) $row["room_nights"],
            ];
        }
        return $result;
    }

    // Top 5 khách hàng: số lượt đặt và tổng giá trị
    private function topUsers($bookings): array
    {
        $result = [];
        $rows = $bookings->aggregate([
            ['$match' => ["booking_status" => ['$ne' => Booking::CANCELLED]]],
            ['$group' => [
                "_id" => '$user_id',
                "bookings" => ['$sum' => 1],
                "total" => ['$sum' => '$total'],
            ]],
            ['$lookup' => [
                "from" => "users",
                "localField" => "_id",
                "foreignField" => "_id",
                "as" => "user"
            ]],
            ['$unwind' => ["path" => '$user', "preserveNullAndEmptyArrays" => true]],
            ['$project' => [
                "name" => ['$ifNull' => ['$user.name', '$_id']],
                "bookings" => 1,
                "total" => 1,
            ]],
            ['$sort' => ["bookings" => -1, "total" => -1, "_id" => 1]],
            ['$limit' => 5],
        ]);
        foreach ($rows as $row)
        {
            $result[] = [
                "id" => (string) $row["_id"],
                "name" => (string) $row["name"],
                "bookings" => (int) $row["bookings"],
                "total" => (int) $row["total"],
            ];
        }
        return $result;
    }

    // Số loại phòng và tổng số phòng theo từng loại
    private function roomsByCategory(): array
    {
        $result = [];
        $rows = $this->db->selectCollection("rooms")->aggregate([
            ['$group' => [
                "_id" => '$category_id',
                "types" => ['$sum' => 1],
                "stock" => ['$sum' => '$stock'],
            ]],
            ['$lookup' => [
                "from" => "categories",
                "localField" => "_id",
                "foreignField" => "_id",
                "as" => "category"
            ]],
            ['$unwind' => ["path" => '$category', "preserveNullAndEmptyArrays" => true]],
            ['$project' => [
                "name" => ['$ifNull' => ['$category.name', '$_id']],
                "types" => 1,
                "stock" => 1,
            ]],
            ['$sort' => ["_id" => 1]],
        ]);
        foreach ($rows as $row)
        {
            $result[] = [
                "name" => (string) $row["name"],
                "types" => (int) $row["types"],
                "stock" => (int) $row["stock"],
            ];
        }
        return $result;
    }

    // 5 lượt nhận phòng sắp tới (từ hôm nay, chưa hủy, chưa trả phòng)
    private function upcoming($bookings): array
    {
        $today = new UTCDateTime(strtotime(date('Y-m-d') . " 00:00:00 UTC") * 1000);

        return iterator_to_array($bookings->aggregate([
            ['$match' => [
                "check_in_date" => ['$gte' => $today],
                "booking_status" => ['$nin' => [Booking::CANCELLED, "Đã trả phòng"]],
            ]],
            ['$sort' => ["check_in_date" => 1, "_id" => 1]],
            ['$limit' => 5],
            ['$lookup' => [
                "from" => "users",
                "localField" => "user_id",
                "foreignField" => "_id",
                "as" => "user"
            ]],
            ['$unwind' => ["path" => '$user', "preserveNullAndEmptyArrays" => true]],
            ['$addFields' => ["user_name" => ['$ifNull' => ['$user.name', ""]]]],
            ['$project' => ["user" => 0]],
        ]), false);
    }

    private function countBy($collection, string $field): array
    {
        $result = [];
        $rows = $collection->aggregate([
            ['$group' => ["_id" => '$' . $field, "count" => ['$sum' => 1]]],
            ['$sort' => ["count" => -1, "_id" => 1]],
        ]);
        foreach ($rows as $row)
        {
            $result[(string) $row["_id"]] = (int) $row["count"];
        }
        return $result;
    }
}
