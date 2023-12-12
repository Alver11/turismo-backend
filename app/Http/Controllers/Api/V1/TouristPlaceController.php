<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlaceResource;
use App\Models\TouristPlace;
use Illuminate\Http\JsonResponse;

class TouristPlaceController extends Controller
{
    public function getPlace(): JsonResponse
    {
        return response()->json(PlaceResource::collection(TouristPlace::all()));
    }
}
