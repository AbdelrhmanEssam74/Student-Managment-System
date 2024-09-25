<?php

namespace PROJECT\Validation;

use PROJECT\Validation\Rules\RequireRule;
use PROJECT\Validation\Rules\AlphaNum;
use PROJECT\Validation\Rules\MaxRule;
use PROJECT\Validation\Rules\BetweenRule;

class Validation
{
    protected array $data = [];          // Holds the data to be validated
    protected array $rules = [];         // Holds the validation rules
    protected ErrorBag $errorBag;        // Instance of ErrorBag for storing errors
    protected array $aliases = [];       // Holds field aliases for better error messages
    protected array $rulesMap = [
        'required' => RequireRule::class,
        'alphaNum' => AlphaNum::class,
        'max' => MaxRule::class,
        'between' => BetweenRule::class,
    ];      // Holds the map of rules

    /**
     * Initializes the validation process with the provided data.
     *
     * @param mixed $data The data to be validated.
     */
    public function make(mixed $data): void
    {
        $this->data = $data;              // Set the data to be validated
        $this->errorBag = new ErrorBag();  // Create a new instance of ErrorBag
        $this->validate();                // Start the validation process
    }

    /**
     * Validates the data against the defined rules.
     */
    protected function validate(): void
    {
        foreach ($this->rules as $field => $rules) {
            foreach ($this->resolveRule($rules) as $rule) {
                $this->applyRule($field, $rule);
            }
        }
    }

    /**
     * Applies a specific validation rule to a field.
     *
     * @param string $field The field to validate.
     * @param mixed $rule The rule to apply.
     */
    protected function applyRule(string $field, mixed $rule): void
    {
        if (!$rule->apply($field, $this->getFieldValue($field), $this->data)) {
            $this->errorBag->add($field, Massage::generator($rule, $this->alias($field)));
        }
    }

    /**
     * Resolves the validation rules provided for a field.
     *
     * @param array $rules The rules to resolve.
     * @return array The resolved rules.
     */
    protected function resolveRule(array $rules): array
    {
        return array_map(function ($rule) {
            if (is_string($rule)) {
                return $this->getRuleFromString($rule);
            }
        }, $rules);
    }

    /**
     * Retrieves the rule object based on the rule name provided.
     *
     * @param string $rule The rule name.
     * @return mixed The rule object.
     */
    protected function getRuleFromString(string $rule): mixed
    {
        $exploded = explode(':', $rule);
        $rule = $exploded[0];
        $options = explode(",", end($exploded));
        return new $this->rulesMap[$rule](...$options);
    }

    /**
     * Retrieves the value of a field from the data array.
     *
     * @param string $field The field to retrieve the value for.
     * @return mixed|null The field value or null if not found.
     */
    public function getFieldValue(string $field): mixed
    {
        return $this->data[$field] ?? null;
    }

    /**
     * Sets the validation rules.
     *
     * @param array $rules The validation rules to set.
     */
    public function rules(array $rules): void
    {
        $this->rules = $rules;            // Assign the provided rules to the rules property
    }

    /**
     * Checks if the validation passes (i.e., no errors).
     *
     * @return bool Returns true if there are no errors.
     */
    public function passes(): bool
    {
        return empty($this->errors());     // Returns true if there are no errors
    }

    /**
     * Retrieves validation errors, optionally for a specific field.
     *
     * @param string|null $key The field to retrieve errors for.
     * @return mixed The errors for the specified key or all errors.
     */
    public function errors(string $key = null): mixed
    {
        return $key ? $this->errorBag->errors[$key] : $this->errorBag->errors; // Return errors for the specified key or all errors
    }

    /**
     * Gets the alias for a given field, or returns the field name if no alias exists.
     *
     * @param string $field The field to get the alias for.
     * @return string The alias if it exists, otherwise the field name.
     */
    public function alias(string $field): string
    {
        return $this->aliases[$field] ?? $field; // Return the alias if it exists, otherwise return the field name
    }

    /**
     * Sets aliases for fields to improve error messaging.
     *
     * @param array $aliases The aliases to set.
     */
    public function setAliases(array $aliases): void
    {
        $this->aliases = $aliases;          // Assign the provided aliases to the aliases property
    }
}