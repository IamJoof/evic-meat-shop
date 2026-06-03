<?php

namespace App\Http\Repository\Contracts;

interface ProductRepositoryInterface {

        public function getAllProducts(int $currentPage, string $filter);

        public function storeProduct(array $data);

        public function updateProduct(int $id, array $data);

}