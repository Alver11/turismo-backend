<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlaceResource;
use App\Models\Image;
use App\Models\TouristPlace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
                "street_view" => $data['street_view'],
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

    public function update(Request $request, $id): JsonResponse
    {
        $data = json_decode($request->input('data'), true);
        $validator = Validator::make($data, [
            'name' => 'required',
            // Puedes agregar más reglas de validación según sea necesario
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $tourist = TouristPlace::findOrFail($id);

        DB::transaction(function () use ($request, $data, $tourist) {
            $tourist->update([
                "name" => $data['name'],
                "address" => $data['address'],
                "district_id" => $data['district_id'],
                "description" => $data['description'],
                "street_view" => $data['street_view'],
                "lat" => $data['lat'],
                "lng" => $data['lng'],
                "status" => $data['status'],
            ]);

            // Actualizar categorías
            if (isset($data['categories'])) {
                $tourist->categories()->sync($data['categories']);
            }

            // Actualizar atributos
            $tourist->attributes()->detach();
            if (isset($data['attributes'])) {
                foreach ($data['attributes'] as $attributeData) {
                    $tourist->attributes()->attach($attributeData['id'], ['info' => $attributeData['info']]);
                }
            }

            // Recopilar los paths de las imágenes que se conservarán
            $keepImages = collect($request->input('images', []))
                ->where('path', '<>', '')
                ->pluck('path')
                ->all();

            //Log::info('keepImages= ', array($keepImages));

            // Eliminar imágenes que no están en la lista de conservación
            foreach ($tourist->images as $existingImage) {
                if (!in_array($existingImage->file_path, $keepImages)) {
                    Storage::delete($existingImage->file_path);
                    $existingImage->delete();
                }
            }

            // Procesar las nuevas imágenes
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    $pathInput = $request->input("images.$index.path");
                    Log::info('image_url', array($pathInput));
                    if ($pathInput === null || $pathInput != '') {
                        $path = $file->store('images/tourists');
                        $tourist->images()->create([
                            'file_path' => $path,
                            'front_page' => $request->input("images.$index.profile"),
                        ]);
                    }elseif (!empty($pathInput)) {
                        // Esto maneja la actualización de las imágenes existentes.
                        $image = Image::where('file_path', $pathInput)->first();
                        Log::info('image', array($image));
                        if ($image) {
                            $image->front_page = $request->input("images.$index.profile");
                            $image->save();
                        }
                    }
                }
            }else {
                for ($index = 0; $index < 10; $index++) {
                    $pathInput = $request->input("images.$index.path");
                    if (!empty($pathInput)) {
                        // Esto maneja la actualización de las imágenes existentes.
                        $image = Image::where('file_path', $pathInput)->first();
                        Log::info('image', array($image));
                        if ($image) {
                            $image->front_page = $request->input("images.$index.profile");
                            $image->save();
                        }
                    }
                }
            }
        });

        return response()->json(['message' => 'Lugar turístico actualizado con éxito'], 200);
    }

    public function show($id): Model|Collection|Builder|array|null
    {
        return TouristPlace::with(['district','categories', 'attributes', 'images'])->findOrFail($id);
    }

    public function destroy(TouristPlace $tourist): JsonResponse
    {
        DB::transaction(function () use ($tourist) {
            // Elimina todas las relaciones antes de eliminar el lugar turístico
            // Desvincula las categorías
            $tourist->categories()->detach();
            // Desvincula los atributos
            $tourist->attributes()->detach();
            // Elimina las imágenes asociadas
            foreach ($tourist->images as $image) {
                $image->delete(); // Elimina el registro de la imagen
            }
            // Finalmente, elimina el lugar turístico
            $tourist->delete();
        });

        return response()->json(['message' => 'Eliminado con éxito']);
    }
}
