<?php

use MongoDB\Collection;

/**
 * Lớp cha cho các model: findAll, findById, create (tự sinh _id dạng B001, P001...),
 * update, delete. Các model con khai báo $prefix và viết thêm hàm search().
 */
abstract class BaseModel
{
    // So sánh / sắp xếp không phân biệt hoa thường, theo tiếng Việt.
    protected const COLLATION = ["locale" => "vi", "strength" => 2];

    protected Collection $collection;

    // Tiền tố của _id: BK, R, C, U
    protected string $prefix = "";

    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    public function findAll()
    {
        return iterator_to_array
        (
            $this->collection->find
            (
                [],
                ["sort" => ["_id" => 1]]
            ),
            false
        );
    }

    public function findById(string $id): ?object
    {
        return $this->collection->findOne(["_id" => $id]);
    }

    public function nextId(): string
    {
        $last = $this->collection->findOne
        (
            [],
            [
                "sort" => ["_id" => -1],
                "collation" => ["locale" => "en", "numericOrdering" => true]
            ]
        );
        $number = 0;
        if ($last !== null)
        {
            $number = (int) substr((string) $last["_id"], strlen($this->prefix));
        }
        return $this->prefix . str_pad((string) ($number + 1), 3, "0", STR_PAD_LEFT);
    }

    public function create(array $data): string
    {
        $id = $this->nextId();
        $this->collection->insertOne(["_id" => $id] + $data);
        return $id;
    }

    public function update(string $id, array $data): bool
    {
        if ($this->findById($id) === null)
        {
            return false;
        }
        $this->collection->updateOne
        (
            ["_id" => $id],
            ['$set' => $data]
        );
        return true;
    }

    public function delete(string $id): bool
    {
        if ($this->findById($id) === null)
        {
            return false;
        }
        $this->collection->deleteOne
        (
            ["_id" => $id]
        );
        return true;
    }

    protected function aggregate(array $pipeline): array
    {
        return iterator_to_array
        (
            $this->collection->aggregate
            (
                $pipeline,
                ["collation" => self::COLLATION]
            ),
            false
        );
    }
}
