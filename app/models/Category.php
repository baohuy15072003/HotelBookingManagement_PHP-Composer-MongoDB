<?php

use MongoDB\BSON\Regex;

class Category extends BaseModel
{
    protected string $prefix = "C";

    public const SORTS = [
        "id_asc"     => ["label" => "Mã tăng dần",        "sort" => ["_id" => 1]],
        "name_asc"   => ["label" => "Tên A → Z",          "sort" => ["name" => 1]],
        "name_desc"  => ["label" => "Tên Z → A",          "sort" => ["name" => -1]],
        "count_desc" => ["label" => "Nhiều phòng nhất",   "sort" => ["room_count" => -1, "name" => 1]],
        "count_asc"  => ["label" => "Ít phòng nhất",      "sort" => ["room_count" => 1, "name" => 1]],
    ];

    /**
     * Tìm kiếm + lọc. $filters: q (mã/tên loại phòng), rooms (has | none)
     * Mỗi loại phòng kèm room_count (số phòng thuộc loại này).
     */
    public function search(array $filters, string $sortKey): array
    {
        $pipeline = [];

        if ($filters["q"] !== "")
        {
            $regex = new Regex(preg_quote($filters["q"], "/"), "i");
            $pipeline[] = ['$match' => ['$or' => [["name" => $regex], ["_id" => $regex]]]];
        }

        $pipeline[] = ['$lookup' => [
            "from" => "rooms",
            "localField" => "_id",
            "foreignField" => "category_id",
            "as" => "rooms"
        ]];
        $pipeline[] = ['$addFields' => ["room_count" => ['$size' => '$rooms']]];
        $pipeline[] = ['$project' => ["rooms" => 0]];

        if ($filters["rooms"] === "has")
        {
            $pipeline[] = ['$match' => ["room_count" => ['$gt' => 0]]];
        }
        elseif ($filters["rooms"] === "none")
        {
            $pipeline[] = ['$match' => ["room_count" => 0]];
        }

        $pipeline[] = ['$sort' => (self::SORTS[$sortKey] ?? self::SORTS["id_asc"])["sort"]];

        return $this->aggregate($pipeline);
    }
}
