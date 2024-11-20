<?php

namespace models;

use core\Model;

/**
 * Orders class represents an order entry in the system, extending the base Model class.
 * It defines the structure and validation rules for order entries, including fields like
 * id, product_id, quantity, and timestamp.
 *
 * @package models
 */
class Orders extends Model
{
    protected static $filePath = __DIR__ . '/../../data/orders.json';
    protected static array $columns = ['id', 'product_id', 'quantity', 'timestamp'];
    protected static array $validationRules = [
        'id' => ['type' => 'integer'],
        'product_ide' => ['type' => 'integer'],
        'quantity' => ['type' => 'integer', 'min' => 1],
    ];
}
