<?php

namespace App\Traits;

use App\Models\File;

trait HasFiles
{
    public static function bootHasFiles(): void
    {
        static::deleting(function ($model) {
            $model->files()->each(fn(File $file) => $file->delete());
        });
    }

    public function files(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }
}
