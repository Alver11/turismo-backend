<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'district_id' => $this->district_id,
            //"districtName" => $this->district ? $this->district->name : null,
            'description' => $this->description,
            'lng' => $this->lng,
            'lat' => $this->lat,
            'user_id' => $this->user_id,
            'status' => $this->status,
            "date" => $this->updated_at ? $this->updated_at->diffForHumans() : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'event_date' => $this->event_date,
            'publication_end_date' => $this->publication_end_date,

            // Relaciones
            'categories' => $this->whenLoaded('categories', function () {
                return $this->categories->isEmpty() ? null : $this->categories->map(function ($category) {
                    return [
                        'name' => $category->name,
                        // Si no querés incluir 'pivot' ni 'event_id', no los pongas
                        // Si quieres incluir event_id, ponelo aquí:
                        'event_id' => $category->event_id,
                        //'pivot' => $category->pivot, // <- Omitido para limpiar
                    ];
                });
            }),

            'images' => $this->whenLoaded('images', function () {
                return $this->images->isEmpty() ? null : ImageResource::collection($this->images);
            }),

            'district' => $this->whenLoaded('district', function () {
                return $this->district ? [
                    'id' => $this->district->id,
                    'name' => $this->district->name,
                ] : null;
            }),
        ];
    }
}
