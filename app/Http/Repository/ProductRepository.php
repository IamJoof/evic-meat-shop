<?php

namespace App\Http\Repository;

use App\Http\Repository\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ProductRepository implements ProductRepositoryInterface
{

    public function getAllProducts(int $currentPage, string $filter = 'all', int $take = 10)
    {

        $skip = ($currentPage - 1) * $take;

        $countQuery = 'SELECT COUNT(p.id) as total FROM products p JOIN categories c ON p.category_id = c.id';
        $countBindings = [];
        if ($filter !== 'all') {
            $countQuery .= ' WHERE c.name = ?';
            $countBindings[] = $filter;
        }
        $totalRecords = DB::selectOne($countQuery, $countBindings)->total;
        $totalPages = (int) ceil($totalRecords / $take);

        $query = 'SELECT 
                    p.*,
                    c.name AS "category_name"
                    from products p 
                    JOIN
                    categories c
                    ON p.category_id = c.id
        ';
        $bindings = [];

        if ($filter !== 'all') {
            $query .= ' WHERE c.name = ?';
            $bindings[] = $filter;
        }

        $query .= " LIMIT {$take} OFFSET {$skip}";

        return [
            'data' => DB::select($query, $bindings),
            'total_pages' => $totalPages
        ];
    }

    public function storeProduct(array $data) {
        $data['created_at'] = now()->toDateTimeString();
        $data['updated_at'] = now()->toDateTimeString();
        
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $insertQuery = "INSERT INTO products ({$columns}) VALUES ({$placeholders})";

        DB::insert($insertQuery, array_values($data));

        $newProductId = DB::getPdo()->lastInsertId();

        return $this->findSpecificProduct($newProductId);
    }

    public function updateProduct(int $id ,array $data) {

        if(!empty($data)) {
            $data['updated_at'] = now()->toDateTimeString();

            $setClauses = [];
            foreach (array_keys($data) as $column) {
                $setClauses[] = "{$column} = ?";
            }
            $setString = implode(', ', $setClauses);

            $bindings = array_values($data);
            $bindings[] = $id;

            $updateQuery = "UPDATE products SET {$setString} WHERE id = ?";

            DB::update($updateQuery, $bindings);
        }

        return $this->findSpecificProduct($id);
    }

    /**
     * HELPERS
     */

    private function findSpecificProduct(int $id) {
        $selectQuery = '
            SELECT
                p.*,
                c.name AS category_name
            FROM products p
            JOIN categories c on p.category_id = c.id
            WHERE p.id = ?
        ';

        return DB::selectOne($selectQuery, [$id]);
    }
}