<?php

namespace models;

use core\Model;

/**
 * Sse class represents an entry for Server-Sent Events (SSE) in the system.
 * This class extends the base Model class and defines the structure of data
 * related to real-time communication via SSE, including fields for products and messages.
 *
 *
 * @package models
 */
class Sse extends Model
{
    protected static $filePath = __DIR__ . '/../../data/sse.json';
    protected static array $columns = ['products', 'messages'];
}
