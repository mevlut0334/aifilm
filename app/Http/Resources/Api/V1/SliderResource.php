<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SliderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'button_text' => $this->button_text,
            'button_link' => $this->button_link,
            'image_url'   => $this->image_url ? asset($this->image_url) : null,
            'order'       => $this->order,
        ];
    }
}
