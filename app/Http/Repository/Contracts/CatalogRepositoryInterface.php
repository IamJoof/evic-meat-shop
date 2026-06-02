<?php

namespace App\Http\Repository\Contracts;

interface CatalogRepositoryInterface
{
    public function getAllProducts(bool $isAvailable);
}