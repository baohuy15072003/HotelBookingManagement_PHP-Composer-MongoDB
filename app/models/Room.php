<?php

use MongoDB\BSON\Regex;

class Room extends BaseModel
{
    protected string $prefix = "R";

    public const STOCK_FILTERS = [
        "many" => "Nhiều phòng (từ 5 phòng)",
        "few"  => "Ít phòng (1 - 4 phòng)",
    ];

    public const SORTS = [
        "id_asc"     => ["label" => "Mã tăng dần",      "sort" => ["_id" => 1]],
        "name_asc"   => ["label" => "Tên A → Z",        "sort" => ["name" => 1]],
        "name_desc"  => ["label" => "Tên Z → A",        "sort" => ["name" => -1]],
        "price_asc"  => ["label" => "Giá thấp → cao",   "sort" => ["price_per_night" => 1, "name" => 1]],
        "price_desc" => ["label" => "Giá cao → thấp",   "sort" => ["price_per_night" => -1, "name" => 1]],
        "stock_asc"  => ["label" => "Số phòng tăng dần", "sort" => ["stock" => 1, "name" => 1]],
        "stock_desc" => ["label" => "Số phòng giảm dần", "sort" => ["stock" => -1, "name" => 1]],
    ];

    /**
     * Tìm kiếm + lọc. $filters: q, category_id, amenity, price_min, price_max, stock
     * Mỗi phòng kèm category_name.
     */
    public function search(array $filters, string $sortKey): array
    {
        $pipeline = [
            ['$lookup' => [
                "from" => "categories",
                "localField" => "category_id",
                "foreignField" => "_id",
                "as" => "category"
            ]],
            ['$unwind' => ["path" => '$category', "preserveNullAndEmptyArrays" => true]],
            ['$addFields' => ["category_name" => ['$ifNull' => ['$category.name', ""]]]],
            ['$project' => ["category" => 0]],
        ];

        $match = [];
        if ($filters["q"] !== "")
        {
            $regex = new Regex(preg_quote($filters["q"], "/"), "i");
            $match['$or'] = [["name" => $regex], ["_id" => $regex]];
        }
        if ($filters["category_id"] !== "")
        {
            $match["category_id"] = $filters["category_id"];
        }
        if ($filters["amenity"] !== "")
        {
            $match["amenities"] = $filters["amenity"]; // khớp phần tử trong mảng amenities
        }

        $price = [];
        if (is_numeric($filters["price_min"]))
        {
            $price['$gte'] = (int) $filters["price_min"];
        }
        if (is_numeric($filters["price_max"]))
        {
            $price['$lte'] = (int) $filters["price_max"];
        }
        if ($price !== [])
        {
            $match["price_per_night"] = $price;
        }

        if ($filters["stock"] === "many")
        {
            $match["stock"] = ['$gte' => 5];
        }
        elseif ($filters["stock"] === "few")
        {
            $match["stock"] = ['$gte' => 1, '$lte' => 4];
        }

        if ($match !== [])
        {
            $pipeline[] = ['$match' => $match];
        }

        $pipeline[] = ['$sort' => (self::SORTS[$sortKey] ?? self::SORTS["id_asc"])["sort"]];

        return $this->aggregate($pipeline);
    }

    public function amenities(): array
    {
        $amenities = array_filter(
            $this->collection->distinct("amenities"),
            static fn($a) => is_string($a) && $a !== ""
        );
        sort($amenities);
        return array_values($amenities);
    }

    public function countByCategory(string $categoryId): int
    {
        return $this->collection->countDocuments(["category_id" => $categoryId]);
    }
}
