<?php

namespace helpers;

use helpers\NotificationService;

/**
 * Class FormValidator
 *
 * This class provides functionality to validate and sanitize form input data.
 * It allows the definition of validation rules for various fields, checks if the field values meet those rules,
 * and sanitizes string inputs to prevent potential security issues like XSS attacks.
 *
 */
class FormValidator
{

    private array $rules = [];

    /**
     * Sets the validation rules for form fields.
     *
     * @param array $rules Associative array of validation rules, where each field has its own set of rules.
     */
    public function setRules(array $rules): void
    {
        $this->rules = $rules;
    }

    /**
     * Validates a specific field's value based on the set rules.
     *
     * @param string $field The name of the field to validate.
     * @param mixed $value The value to be validated.
     * @return bool Returns true if the field passes validation, false otherwise.
     */
    public function validateField(string $field, mixed $value): bool
    {
        if (!isset($this->rules[$field])) {
            NotificationService::addFlashMessage("No validation rules set for '$field'.\n");
            return false;
        }

        $rule = $this->rules[$field];

        if(isset($rule['type'])){
            if (!$this->checkType($value, $rule['type'])) {
                return false;
            }
        }
        if(isset($rule['min'])){
            if (!$this->checkMin($value, $rule['min'])) {
                return false;
            }
        }
        if(isset($rule['max'])){
            if ( !$this->checkMax($value, $rule['max'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Sanitizes the given value by trimming and encoding special characters for strings.
     *
     * @param mixed $value The value to be sanitized.
     * @return mixed The sanitized value (if string, HTML special chars encoded, if not, returns the original value).
     */
    public function sanitize(mixed $value): mixed
    {
        if (is_string($value)) {
            return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        }
        return $value;
    }

    /**
     * Checks if the value matches the expected type.
     *
     * @param mixed $value The value to check.
     * @param string $rule The expected type (e.g., 'int', 'string').
     * @return bool Returns true if the value matches the expected type, false otherwise.
     */
    private function checkType(mixed $value, string $rule): bool
    {
        if (!$this->validateType($value, $rule)) {
            NotificationService::addFlashMessage("Invalid type. Expected {$rule}.\n");
            return false;
        }
        return true;
    }

    /**
     * Checks if the value is greater than or equal to the minimum allowed value.
     *
     * @param mixed $value The value to check.
     * @param string $rule The minimum allowed value (as a string).
     * @return bool Returns true if the value meets the minimum requirement, false otherwise.
     */
    private function checkMin(mixed $value, string $rule): bool
    {
        if ($value < (int)$rule) {
            NotificationService::addFlashMessage("Value must be at least {$rule}.\n");
            return false;
        }
        return true;
    }

    /**
     * Checks if the value is less than or equal to the maximum allowed value.
     *
     * @param mixed $value The value to check.
     * @param string $rule The maximum allowed value (as a string).
     * @return bool Returns true if the value meets the maximum requirement, false otherwise.
     */
    private function checkMax(mixed $value, string $rule): bool
    {
        if ($value > (int)$rule) {
            NotificationService::addFlashMessage("Value must not exceed {$rule}.\n");
            return false;
        }
        return true;
    }

    /**
     * Validates the type of a value against a specified type.
     *
     * @param mixed $value The value to check.
     * @param string $type The expected type ('int', 'float', 'string', 'bool').
     * @return bool Returns true if the value matches the expected type, false otherwise.
     */
    private function validateType(mixed $value, string $type): bool
    {
        return match ($type) {
            'int' => is_int($value),
            'float' => is_float($value) || (is_numeric($value) && str_contains((string)$value, '.')),
            'string' => is_string($value),
            'bool' => is_bool($value),
            default => false,
        };
    }
}