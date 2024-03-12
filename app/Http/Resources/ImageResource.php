<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource
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
          "filePath" => env('APP_URL', 'http://localhost'). '/storage/' . $this->file_path,
            //"filePath" => "http://apiturismo.walabi.com.py/storage/images/tourists/b3GKI3WVPzsiQB6PVIkdvZ5AKNilcJgcDIpMqIrn.jpg",
          "frontPage" => $this->front_page,
        ];
    }
}
