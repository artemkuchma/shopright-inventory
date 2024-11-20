<?php

namespace models;

use core\Model;

/**
 * Logs class represents a log entry in the system, extending the base Model class.
 * It defines the structure and validation rules for log entries, including fields like
 * timestamp, type, message, file, and line.
 *
 * @package models
 */
class Logs extends Model
{
    protected static $filePath = __DIR__ . '/../../data/logs.json';
    protected static array $columns = ['timestamp', 'type', 'message', 'file', 'line'];
    protected static array $validationRules = [
        'timestamp' => ['type' => 'integer'],
        'type' => ['type' => 'integer'],
        'message' => ['type' => 'string'],
        'file' => ['type' => 'string'],
        'line' => ['type' => 'integer'],
    ];
}
