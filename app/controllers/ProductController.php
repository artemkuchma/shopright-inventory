<?php

namespace controllers;

use core\Controller;
use models\Products;

/**
 * Class ProductController
 * Handles operations related to products, such as displaying a list of all products.
 */
class ProductController extends Controller
{
    /**
     * Displays a list of all products.
     * Retrieves all products from the model and renders the 'index' view.
     *
     * @return void
     */
    public function index()
    {
        $this->render('index', ['products' => Products::getAll()]);
    }
}
