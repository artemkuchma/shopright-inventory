<?php

namespace controllers;

use core\Controller;
use helpers\InventoryManager;
use helpers\FormValidator;
use models\Orders;
use models\Products;

/**
 * Class OrderController
 * Handles operations related to orders, including creating, listing, and storing orders.
 */
class OrderController extends Controller
{
    /**
     * Displays the order creation page.
     *
     * @return void
     */
    public function create(): void
    {
        $this->render('create', ['products' => Products::getAll()]);
    }

    /**
     * Displays a list of all orders.
     *
     * @return void
     */
    public function index(): void
    {
        $this->render('index', ['orders' => Orders::getAll()]);
    }

    /**
     * Processes and stores a new order if valid data is provided.
     * Redirects to the order page after processing.
     *
     * @return void
     */
    public function store(): void
    {
        $validator = new FormValidator();
        $validationRules = [
            'product_id' => ['type' => 'int'],
            'quantity' => ['type' => 'int', 'min' => 1],
        ];
        $validator->setRules($validationRules);
        $productId = $validator->sanitize((int)($_POST['product_id'] ?? 0));
        $quantity = $validator->sanitize((int)($_POST['quantity'] ?? 0));

        if (
            $validator->validateField('product_id', $productId) &&
            $validator->validateField('quantity', $quantity)
        ) {
            InventoryManager::processOrder($productId, $quantity);
        }
        $this->redirect('/order');
    }
}
