<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

trait ModelTrait
{
    protected $slugNum = 0;

    public function toFill(array $payload, array $exceptColumns = [])
    {
        $acceptColumns = array_diff($this->getColumns(), $exceptColumns);
        $this->fill(
            collect($payload)
                ->only($acceptColumns)
                ->all()
        );
        return $this;
    }

    public function getColumns()
    {
        // check model property fillable
        if (property_exists($this, 'fillable')) {
            return $this->getFillable();
        } elseif (property_exists($this, 'guarded')) {
            $guardedColumns = $this->getGuarded();

            if (in_array('*', $guardedColumns)) {
                return Schema::getColumnListing($this->getTable());
            }
            return array_diff(Schema::getColumnListing($this->getTable()), $guardedColumns);
        } else {
            return Schema::getColumnListing($this->getTable());
        }
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    protected static function boot()
    {
        parent::boot();
        if (Schema::hasColumn((new self)->getTable(), 'uuid')) {
            static::creating(function ($model) {
                $model->uuid = Str::uuid();
            });
        }
    }
}
