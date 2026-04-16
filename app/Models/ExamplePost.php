<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasFiles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Translatable\HasTranslations;

class ExamplePost extends Model
{
    use HasTranslations, HasFiles, Auditable;

    public array $translatable = ['title', 'body'];

    protected $fillable = [
        'title',
        'body',
        'user_id',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * @param array<string, mixed> $filters
     */
    public function scopeFilter($query, array $filters): void
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->whereRaw("CAST(title AS TEXT) ILIKE ?", ["%{$search}%"]);
        });

        $query->when(isset($filters['status']), function ($query) use ($filters) {
            $query->where('status', filter_var($filters['status'], FILTER_VALIDATE_BOOLEAN));
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Single cover image */
    public function cover(): MorphMany
    {
        return $this->files()->where('field', 'cover');
    }

    /** Multiple attachments */
    public function attachments(): MorphMany
    {
        return $this->files()->where('field', 'attachments');
    }
}
