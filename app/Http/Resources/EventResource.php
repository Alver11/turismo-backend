<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use mysql_xdevapi\Collection;

class EventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "address" => $this->address,
            "districtName" => $this->district ? $this->district->name : null,
            "description" => $this->description,
            "lng" => $this->lng,
            "lat" => $this->lat,
            "images" => $this->images ? ImageResource::collection($this->images) : [],
        ];
    }
}
