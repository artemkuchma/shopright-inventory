<?php

namespace core;

use helpers\LogHelper;
use helpers\NotificationService;

/**
 * The Model class provides methods for loading, validating, saving, updating,
 * and retrieving data from a file-based storage system. It supports basic CRUD
 * operations, including:
 * - **load()**: Loading data into the model's attributes.
 * - **getAll()**: Retrieving all records stored in a file.
 * - **add()**: Adding new records, including automatic ID assignment.
 * - **update()**: Updating existing records based on their ID.
 * - **save()**: Saving records either by updating existing ones or adding new ones.
 *
 * The class also supports validation of attributes using predefined rules:
 * - Type validation to ensure that attribute values match the expected type.
 * - Minimum and maximum value validation to ensure attribute values are within allowed ranges.
 *
 * This class uses static properties for defining the file path, columns, and validation rules
 * that apply to the model's attributes. The data is stored in a JSON file, and the file
 * contents are read and written with methods that handle error logging.
 *
 * It is designed to be extended by other models, allowing customization for different
 * entities while reusing the core functionality provided here.
 */
class Model
{
     // Path to the file that stores data.
    protected static $filePath;
    // Array of columns that the model will use.
    protected static array $columns = [];
    // Validation rules for the model's attributes.
    protected static array $validationRules = [];
    // Array to hold the current model's attributes.
    public array $attributes = [];

    /**
     * Loads data into the model's attributes and validates them.
     *
     * @param array $data The data to load into the model.
     *
     * @return bool Returns true if the data is valid, false otherwise.
     */
    public function load(array $data): bool
    {
        foreach ($data as $key => $value) {
            if (in_array($key, static::$columns)) {
                $this->attributes[$key] = $value;
            }
        }
        return $this->validate();
    }

    /**
     * Retrieves all records from the data file.
     *
     * @return array An array of all records, or an empty array if an error occurs.
     */
    public static function getAll(): array
    {
        clearstatcache();
        $data = file_get_contents(static::$filePath);
        if ($data === false) {
            LogHelper::logError();
            return [];
        }
        return json_decode($data, true) ?? [];
    }

    /**
     * Adds a new record to the data file.
     *
     * @return bool Returns true if the record was added successfully, false otherwise.
     * @throws \Exception Throws an exception if a required column is missing.
     */
    public function add(): bool
    {
        $this->attributes['id'] = static::getLastId() + 1;
        foreach (static::$columns as $column) {
            if (!array_key_exists($column, $this->attributes)) {
                throw new \Exception("Missing required column: $column");
            }
        }

        $data = static::getAll();
        $data[] = $this->attributes;

        return static::saveAll($data);
    }

    /**
     * Updates an existing record in the data file.
     *
     * @param int $id The ID of the record to update.
     *
     * @return bool Returns true if the record was updated, false if the record was not found.
     */
    public function update($id): bool
    {
        $data = static::getAll();
        foreach ($data as $key => $record) {
            if ($record['id'] === $id) {
                $data[$key] = array_merge($record, $this->attributes);
                return static::saveAll($data);
            }
        }
        NotificationService::addFlashMessage("Record with ID $id not found in ");
        return false;
    }

    /**
     * Retrieves a record by its ID.
     *
     * @param int $id The ID of the record to retrieve.
     *
     * @return array|null The record as an associative array, or null if not found.
     */
    public function getById(int $id): ?array
    {
        $data = static::getAll();
        foreach ($data as $record) {
            if ($record['id'] === $id) {
                $this->attributes = $record;
                return $record;
            }
        }
        return null;
    }

    /**
     * Saves the current record to the data file.
     *
     * @return bool Returns true if the record was saved, false otherwise.
     * @throws \Exception Throws an exception if no attributes exist or a required column is missing.
     */
    public function save(): bool
    {
        if (empty($this->attributes)) {
            throw new \Exception("No attributes to save.");
        }

        if(!$this->validate()){
            return false;
        }

        $data = static::getAll();

        $found = false;
        if (isset($this->attributes['id'])) {
            foreach ($data as $key => $record) {
                if ($record['id'] === $this->attributes['id']) {
                    $data[$key] = array_merge($record, $this->attributes);
                    $found = true;
                    break;
                }
            }
        }
        if (!$found) {
            foreach (static::$columns as $column) {
                if (!array_key_exists($column, $this->attributes)) {
                    throw new \Exception("For adding new record missing required column: $column");
                }
            }
            $data[] = $this->attributes;
        }

        return static::saveAll($data);
    }

    /**
     * Validates the model's attributes according to the defined validation rules.
     *
     * @return bool Returns true if all attributes are valid, false otherwise.
     */
    protected function validate(): bool
    {
        foreach ($this->attributes as $key => $value) {
            $rules = static::$validationRules[$key];

            if (isset($rules)) {
                if(isset($rules['type'])){
                    if (!$this->validateType($key, $value, $rules)) {
                        return false;
                    }
                }
                if(isset($rules['min'])){
                    if (!$this->validateMin($key, $value, $rules)) {
                        return false;
                    }
                }
                if(isset($rules['max'])){
                    if (!$this->validateMax($key, $value, $rules)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Saves all data to the file.
     *
     * @param array $data The data to save.
     *
     * @return bool Returns true if the data was saved, false otherwise.
     */
    protected static function saveAll(array $data): bool
    {
        $jsonData = json_encode($data, JSON_PRETTY_PRINT);
        if (file_put_contents(static::$filePath, $jsonData) === false) {
            LogHelper::logError();
            return false;
        }
        return true;
    }

    /**
     * Retrieves the last used ID from the data file.
     *
     * @return int The last used ID, or 0 if no records exist.
     */
    protected static function getLastId(): int
    {
        $data = static::getAll();
        if (empty($data)) {
            return 0;
        }
        return max(array_column($data, 'id'));
    }

    /**
     * Validates the type of the attribute value.
     *
     * @param string $key The attribute key.
     * @param mixed $value The value to validate.
     * @param array $rules Validation rules for the attribute.
     *
     * @return bool True if validation passes, false otherwise.
     */
    private function validateType(string $key, mixed $value, array $rules): bool
    {
        if (isset($rules['type']) && gettype($value) !== $rules['type']) {
            NotificationService::addFlashMessage(
                "Validation failed for $key: expected type " . $rules['type'] . ", got " . gettype($value)
            );
            return false;
        }
        return true;
    }

    /**
     * Validates that the value is not less than the minimum allowed value.
     *
     * @param string $key The attribute key.
     * @param mixed $value The value to validate.
     * @param array $rules Validation rules for the attribute.
     *
     * @return bool True if validation passes, false otherwise.
     */
    private function validateMin(string $key, mixed $value, array $rules): bool
    {
        if (isset($rules['min']) && $value < $rules['min']) {
            NotificationService::addFlashMessage(
                "Validation failed for $key: value less than minimum " . $rules['min']
            );
            echo 'test';
            return false;
        }


        return true;
    }

    /**
     * Validates that the value is not greater than the maximum allowed value.
     *
     * @param string $key The attribute key.
     * @param mixed $value The value to validate.
     * @param array $rules Validation rules for the attribute.
     *
     * @return bool True if validation passes, false otherwise.
     */
    private function validateMax(string $key, mixed $value, array $rules): bool
    {
        if (isset($rules['max']) && $value > $rules['max']) {
            NotificationService::addFlashMessage(
                "Validation failed for $key: value greater than maximum " . $rules['max']
            );
            return false;
        }
        return true;
    }
}
