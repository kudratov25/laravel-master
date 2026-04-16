<?php

namespace App\Traits;

use App\Models\File;

trait HasFile
{
    public static function bootHasFile(): void
    {
        static::deleting(function ($model) {
            $model->file()->delete();
        });
    }

    public function file(): \Illuminate\Database\Eloquent\Relations\MorphOne
    {
        return $this->morphOne(File::class, 'fileable');
    }
}
