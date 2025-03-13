<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            "id" => $this->id,
            "title" => $this->title,
            "slug" => $this->slug,
            "user_id" => $this->user_id,
            "content" => $this->content,
            "feature_image" => $this->feature_image,
            "is_published" => $this->is_published,
            "status" => $this->status,
        ];
    }
}
