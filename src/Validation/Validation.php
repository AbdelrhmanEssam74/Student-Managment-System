<?php

namespace PROJECT\Validation;

use PROJECT\Validation\Rules\RequireRule;
use PROJECT\Validation\Rules\AlphaNum;
class Validation
{
    protected array $data = [];          // Holds the data to be validated
    protected array $rules = [];         // Holds the validation rules
    protected ErrorBag $errorBag;        // Instance of ErrorBag for storing errors
    protected array $aliases = [];       // Holds field aliases for better error messages
    protected array $rulesMap = [
        'required' => RequireRule::class,
        'alphaNum' => AlphaNum::class,
    ];      // Holds the map of rules

    // Initializes the validation process with the provided data
    public function make($data): void
    {
        $this->data = $data;              // Set the data to be validated
        $this->errorBag = new ErrorBag();  // Create a new instance of ErrorBag
        $this->validate();                // Start the validation process
    }

    // Validates the data against the defined rules
    protected function validate(): void
    {
        foreach ($this->rules as $field => $rules) {
            foreach ($rules as $rule) {
                if (is_string($rule))
                {
                    $rule = new $this->rulesMap[$rule];
                }
                if (!$rule->apply($field, $this->getFieldValue($field), $this->data)) {
                    $this->errorBag->add($field, Massage::generator($rule, $field));
                }
            }
        }
    }

    public function getFieldValue($field): mixed
    {
        return $this->data[$field] ?? null;
    }

    // Sets the validation rules
    public function rules($rules): void
    {
        $this->rules = $rules;            // Assign the provided rules to the rules property
    }

    // Checks if the validation passes (i.e., no errors)
    public function passes(): bool
    {
        return empty($this->errors());     // Returns true if there are no errors
    }

    // Retrieves validation errors, optionally for a specific field
    public function errors($key = null)
    {
        return $key ? $this->errorBag->errors[$key] : $this->errorBag->errors; // Return errors for the specified key or all errors
    }

    // Gets the alias for a given field, or returns the field name if no alias exists
    public function alias($field)
    {
        return $this->aliases[$field] ?? $field; // Return the alias if it exists, otherwise return the field name
    }

    // Sets aliases for fields to improve error messaging
    public function setAliases(array $aliases): void
    {
        $this->aliases = $aliases;          // Assign the provided aliases to the aliases property
    }
}
