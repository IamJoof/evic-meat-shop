<?php

namespace App\Http\Services;

use App\Http\Repository\Contracts\CatalogRepositoryInterface;
class CatalogService
{
    public function __construct
    (
        private CatalogRepositoryInterface $catalogRepository
    ) {}

    public function getAvailableProducts()
    {
        return $this->catalogRepository->getAllProducts(true);
    }

    public function getUnavailableProducts()
    {
        return $this->catalogRepository->getAllProducts(false);
    }
}