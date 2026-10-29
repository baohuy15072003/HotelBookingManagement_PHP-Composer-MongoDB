<?php

use MongoDB\BSON\Regex;

class User extends BaseModel
{
    protected string $prefix = "U";

    public const SORTS = [
        "id_asc"        => ["label" => "Mã tăng dần",           "sort" => ["_id" => 1]],
        "name_asc"      => ["label" => "Tên A → Z",             "sort" => ["name" => 1]],
        "name_desc"     => ["label" => "Tên Z → A",             "sort" => ["name" => -1]],
        "bookings_desc" => ["label" => "Đặt nhiều lượt nhất",   "sort" => ["booking_count" => -1, "name" => 1]],
        "spent_desc"    => ["label" => "Giá trị đặt cao nhất",  "sort" => ["total_spent" => -1, "name" => 1]],
    ];

    /**
     * Tìm kiếm + lọc. $filters: q (mã/tên/email/sđt), address
     * Mỗi khách kèm booking_count và total_spent (không tính lượt đã hủy).
     */
    public function search(array $filters, string $sortKey): array
    {
        $pipeline = [];

        $match = [];
        if ($filters["q"] !== "")
        {
            $regex = new Regex(preg_quote($filters["q"], "/"), "i");
            $match['$or'] = [
                ["_id" => $regex],
                ["name" => $regex],
                ["email" => $regex],
                ["phone" => $regex],
            ];
        }
        if ($filters["address"] !== "")
        {
            $match["address"] = $filters["address"];
        }
        if ($match !== [])
        {
            $pipeline[] = ['$match' => $match];
        }

        $pipeline[] = ['$lookup' => [
            "from" => "bookings",
            "localField" => "_id",
            "foreignField" => "user_id",
            "as" => "bookings"
        ]];
        $pipeline[] = ['$addFields' => [
            "valid" => ['$filter' => [
                "input" => '$bookings',
                "as" => "b",
                "cond" => ['$ne' => ['$$b.booking_status', Booking::CANCELLED]],
            ]],
        ]];
        $pipeline[] = ['$addFields' => [
            "booking_count" => ['$size' => '$valid'],
            "total_spent" => ['$sum' => '$valid.total'],
        ]];
        $pipeline[] = ['$project' => ["bookings" => 0, "valid" => 0]];
        $pipeline[] = ['$sort' => (self::SORTS[$sortKey] ?? self::SORTS["id_asc"])["sort"]];

        return $this->aggregate($pipeline);
    }

    public function addresses(): array
    {
        $addresses = array_filter(
            $this->collection->distinct("address"),
            static fn($a) => is_string($a) && $a !== ""
        );
        sort($addresses);
        return array_values($addresses);
    }
}
