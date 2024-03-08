<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlaceResource;
use App\Models\TouristPlace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class TouristPlaceController extends Controller
{
    public function getPlace(): JsonResponse
    {
        return response()->json(PlaceResource::collection(TouristPlace::all()));
    }

    public function index(): JsonResponse
    {
        $queryTourist =  TouristPlace::query();
        $queryTourist->with(['district','categories', 'attributes', 'images' => function ($query) {
            $query->where('front_page', true);
        }]);
        return DataTables::eloquent($queryTourist)->toJson();
    }

    public function store(Request $request): JsonResponse
    {
        $data = json_decode($request->input('data'), true);
        $validator = Validator::make($data, [
            'name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        DB::transaction(function () use ($request, $data) {
            $tourist = TouristPlace::create([
                "name" => $data['name'],
                "address" => $data['address'],
                "district_id" => $data['district_id'],
                "description" => $data['description'],
                "lat" => $data['lat'],
                "lng" => $data['lng'],
                "user_id" => Auth::id()
            ]);

            $tourist->categories()->sync($data['categories']);
            $tourist->attributes()->detach();
            foreach ($data['attributes'] as $attributeData) {
                $tourist->attributes()->attach($attributeData['id'], ['info' => $attributeData['info']]);
            }


            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    $path = $file->store('images/tourists');
                    $tourist->images()->create([
                        'file_path' => $path,
                        'front_page' => $request->input("images.$index.profile"),
                    ]);
                }
            }

        });
        return response()->json(['message' => 'Lugar agregado con éxito'], 201);
    }

    public function show($id): Model|Collection|Builder|array|null
    {
        return TouristPlace::with(['district','categories', 'attributes', 'images'])->findOrFail($id);
    }

    public function destroy(TouristPlace $tourist): JsonResponse
    {
        DB::transaction(function () use ($tourist) {
            $tourist->delete();
        });

        return response()->json(['message' => 'Usuario eliminado con éxito']);
    }
}
