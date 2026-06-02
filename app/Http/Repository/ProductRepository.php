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
}