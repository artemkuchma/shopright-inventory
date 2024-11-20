<?php

namespace models;

use core\Model;

/**
 * Products class represents a product entry in the system, extending the base Model class.
 * It defines the structure and validation rules for product entries, including fields like
 * id, name, price, and stock.
 *
 * @package models
 */
class Products extends Model
{
    protected static $filePath = __DIR__ . '/../../data/products.json';
    protected static array $columns = ['id', 'name', 'price', 'stock'];
    protected static array $validationRules = [
        'id' => ['type' => 'integer'],
        'name' => ['type' => 'string'],
        'stock' => ['type' => 'integer', 'min' => 0],
        'price' => ['type' => 'double', 'min' => 0],

    ];
}
