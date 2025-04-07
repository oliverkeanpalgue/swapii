<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserReportResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user' => [
                'name' => $this->user->name ?? 'Unknown',
                'avatar' => $this->user->avatar ?? null,
                'level' => $this->whenLoaded('user', fn () => $this->user->level, 'level 0'),
            ],
            'reporter' => [
                'name' => $this->reporter->name ?? 'Unknown',
            ],
            'created_at' => $this->created_at,
            'concern' => $this->concern,
            'description' => $this->description,
            'image' => $this->whenLoaded('images', fn () => $this->images->first()?->image),
        ];
    }
}
