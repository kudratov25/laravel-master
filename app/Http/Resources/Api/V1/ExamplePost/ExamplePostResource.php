<?php

namespace App\Http\Resources\Api\V1\ExamplePost;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Used in show — returns all translations for all locales.
 */
class ExamplePostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->getTranslations('title'),
            'body'        => $this->getTranslations('body'),
            'status'      => $this->status,
            'user_id'     => $this->user_id,
            'cover'       => $this->whenLoaded('cover', fn() => $this->cover->first()?->only(['id', 'url', 'original_name'])),
            'attachments' => $this->whenLoaded('attachments', fn() => $this->attachments->map->only(['id', 'url', 'original_name'])),
            'created_at'  => $this->created_at?->format('d.m.Y'),
            'updated_at'  => $this->updated_at?->format('d.m.Y'),
        ];
    }
}
