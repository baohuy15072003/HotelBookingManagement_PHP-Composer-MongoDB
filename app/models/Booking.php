<?php

use MongoDB\BSON\Regex;
use MongoDB\BSON\UTCDateTime;

class Booking extends BaseModel
{
    protected string $prefix = "BK";

    public const CANCELLED = "Đã Hủy";

    public const PAYMENT_STATUSES = ["Chưa thanh toán", "Đã thanh toán"];
    public const BOOKING_STATUSES = ["Đang xử lý", "Đã xác nhận", "Đã nhận phòng", "Đã trả phòng", self::CANCELLED];

    public const SORTS = [
        "checkin_desc" => ["label" => "Nhận phòng: mới nhất",  "sort" => ["check_in_date" => -1, "_id" => -1]],
        "checkin_asc"  => ["label" => "Nhận phòng: sớm nhất",  "sort" => ["check_in_date" => 1, "_id" => 1]],
        "total_desc"   => ["label" => "Tổng tiền giảm dần",    "sort" => ["total" => -1, "_id" => 1]],
        "total_asc"    => ["label" => "Tổng tiền tăng dần",    "sort" => ["total" => 1, "_id" => 1]],
        "id_asc"       => ["label" => "Mã tăng dần",           "sort" => ["_id" => 1]],
    ];

    /**
     * Tìm kiếm + lọc. $filters: q (mã đặt phòng / mã khách / tên khách), payment_status,
     * booking_status, date_from, date_to (lọc theo ngày nhận phòng, Y-m-d), total_min, total_max
     * Mỗi lượt đặt kèm user_name và room_count (tổng số phòng đặt).
     */
    public function search(array $filters, string $sortKey): array
    {
        $pipeline = [
            ['$lookup' => [
                "from" => "users",
                "localField" => "user_id",
                "foreignField" => "_id",
                "as" => "user"
            ]],
            ['$unwind' => ["path" => '$user', "preserveNullAndEmptyArrays" => true]],
            ['$addFields' => [
                "user_name"  => ['$ifNull' => ['$user.name', ""]],
                "room_count" => ['$sum' => '$items.quantity'],
            ]],
            ['$project' => ["user" => 0]],
        ];

        $match = [];
        if ($filters["q"] !== "")
        {
            $regex = new Regex(preg_quote($filters["q"], "/"), "i");
            $match['$or'] = [
                ["_id" => $regex],
                ["user_id" => $regex],
                ["user_name" => $regex],
            ];
        }
        if ($filters["payment_status"] !== "")
        {
            $match["payment_status"] = $filters["payment_status"];
        }
        if ($filters["booking_status"] !== "")
        {
            $match["booking_status"] = $filters["booking_status"];
        }

        // Ngày lưu dạng UTC 00:00 nên so sánh theo UTC
        $date = [];
        if ($this->isDate($filters["date_from"]))
        {
            $date['$gte'] = new UTCDateTime(strtotime($filters["date_from"] . " 00:00:00 UTC") * 1000);
        }
        if ($this->isDate($filters["date_to"]))
        {
            $date['$lte'] = new UTCDateTime(strtotime($filters["date_to"] . " 23:59:59 UTC") * 1000);
        }
        if ($date !== [])
        {
            $match["check_in_date"] = $date;
        }

        $total = [];
        if (is_numeric($filters["total_min"]))
        {
            $total['$gte'] = (int) $filters["total_min"];
        }
        if (is_numeric($filters["total_max"]))
        {
            $total['$lte'] = (int) $filters["total_max"];
        }
        if ($total !== [])
        {
            $match["total"] = $total;
        }

        if ($match !== [])
        {
            $pipeline[] = ['$match' => $match];
        }

        $pipeline[] = ['$sort' => (self::SORTS[$sortKey] ?? self::SORTS["checkin_desc"])["sort"]];

        return $this->aggregate($pipeline);
    }

    public function countByUser(string $userId): int
    {
        return $this->collection->countDocuments(["user_id" => $userId]);
    }

    public function countByRoom(string $roomId): int
    {
        return $this->collection->countDocuments(["items.room_id" => $roomId]);
    }

    private function isDate(string $value): bool
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 && strtotime($value . " UTC") !== false;
    }
}
