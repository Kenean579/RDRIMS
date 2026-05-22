<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasDynamicStatus
{
    /**
     * Get the ID of a status by its name dynamically.
     * Assumes the status model follows the pattern [ModelName]Status.
     */
    public static function getStatusId(string $name): ?int
    {
        $modelName = class_basename(static::class);
        $modelInstance = new static;
        
        $statusModel = property_exists($modelInstance, 'statusModelMapping') 
            ? $modelInstance->statusModelMapping 
            : "App\\Models\\" . str_replace('Request', '', $modelName) . "Status";

        if (class_exists($statusModel)) {
            return $statusModel::where('name', $name)->first()?->id;
        }

        return null;
    }
}
