<?php

namespace App\Http\Repository\Contracts;

interface ProductRepositoryInterface {

        public function getAllProducts(int $currentPage, string $filter);
}