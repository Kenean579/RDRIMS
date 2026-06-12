<?php

namespace App\Traits;

trait CastBooleanFields
{
    /**
     * Cast string booleans from FormData to actual boolean types.
     */
    protected function castBooleans(array $fields): void
    {
        foreach ($fields as $field) {
            if ($this->has($field)) {
                $this->merge([
                    $field => filter_var($this->{$field}, FILTER_VALIDATE_BOOLEAN)
                ]);
            }
        }
    }
}
