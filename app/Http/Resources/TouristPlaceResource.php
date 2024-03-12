<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TouristPlaceResource extends JsonResource
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
            "categories" => ItemResource::collection($this->categories),
            "attribute" => AttributeResource::collection($this->attributes),
            "lng" => $this->lng,
            "lat" => $this->lat,
            "images" => $this->images ? ImageResource::collection($this->images) : [],
        ];
    }
}
