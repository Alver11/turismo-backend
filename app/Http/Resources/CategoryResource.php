<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            "touristPlaces" => TouristPlaceResource::collection($this->touristPlaces),
            "image" => $this->images ? env('APP_URL', 'http://192.168.100.184'). '/storage/' . $this->images[0]->file_path : '',
        ];
    }
}
