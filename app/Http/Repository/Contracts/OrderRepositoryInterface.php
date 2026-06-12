<?php

namespace App\Http\Repository\Contracts;

interface OrderRepositoryInterface {

    public function getAllOrders();

    public function createOrder();

    public function updateOrder();

    public function softDeleteOrder();

    public function getSpecificOrder();
}