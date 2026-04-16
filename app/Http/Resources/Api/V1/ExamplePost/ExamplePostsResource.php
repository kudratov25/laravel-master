<?php

namespace App\Http\Resources\Api\V1\ExamplePost;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Used in index (list) — returns translated value for current locale.
 */
class ExamplePostsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'title'      => $this->title,        // translated for current locale
            'status'     => $this->status,
            'user_id'    => $this->user_id,
            'cover'      => $this->whenLoaded('cover', fn() => $this->cover->first()?->url),
            'created_at' => $this->created_at?->format('d.m.Y'),
        ];
    }
}
