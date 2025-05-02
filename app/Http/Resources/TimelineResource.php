<?php

namespace App\Http\Resources;

use App\Models\Timeline;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Timeline */
class TimelineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'picture' => $this->picture ? Storage::url($this->picture) : null,
            'created_at' => $this->created_at,
            'created_by' => $this->user->username,
        ];
    }
}
