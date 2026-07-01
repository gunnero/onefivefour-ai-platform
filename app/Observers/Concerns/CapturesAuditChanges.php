<?php

namespace App\Observers\Concerns;

use Illuminate\Database\Eloquent\Model;

trait CapturesAuditChanges
{
    /**
     * @param  array<int, string>|null  $only
     * @return array<string, mixed>
     */
    private function auditState(Model $model, ?array $only = null): array
    {
        $attributes = $model->attributesToArray();

        if ($only !== null) {
            $attributes = array_intersect_key($attributes, array_flip($only));
        }

        return $this->removeAuditNoise($attributes);
    }

    /**
     * @return array<string, mixed>
     */
    private function changedAuditState(Model $model): array
    {
        return $this->removeAuditNoise($model->getChanges());
    }

    /**
     * @param  array<string, mixed>  $changes
     * @return array<string, mixed>
     */
    private function originalAuditState(Model $model, array $changes): array
    {
        $original = [];

        foreach (array_keys($changes) as $attribute) {
            $original[$attribute] = $model->getOriginal($attribute);
        }

        return $original;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function removeAuditNoise(array $attributes): array
    {
        unset($attributes['created_at'], $attributes['updated_at']);

        return $attributes;
    }
}
