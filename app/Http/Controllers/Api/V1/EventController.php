<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Image;
use App\Models\TouristPlace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class EventController extends Controller
{
    public function index(): JsonResponse
    {
        $queryEvent = Event::query();
        $queryEvent->with(['district', 'images' => function ($query) {
            $query->where('front_page', true);
        }]);
        return DataTables::eloquent($queryEvent)->toJson();
    }

    public function store(Request $request): JsonResponse
    {
        $data = json_decode($request->input('data'), true);
        $validator = Validator::make($data, [
            'name' => 'required',
            'description' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        DB::transaction(function () use ($request, $data) {
            $event = Event::create([
                "name" => $data['name'],
                "address" => $data['address'] ?? null,
                "district_id" => $data['district_id'] ?? null,
                "description" => $data['description'],
                "lat" => $data['lat'],
                "lng" => $data['lng'],
                "user_id" => Auth::id()
            ]);


            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    $path = $file->store('images/events');
                    $event->images()->create([
                        'file_path' => $path,
                        'front_page' => $request->input("images.$index.profile"),
                    ]);
                }
            }

        });
        return response()->json(['message' => 'Datos gregado con éxito'], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $data = json_decode($request->input('data'), true);
        $validator = Validator::make($data, [
            'name' => 'required',
            'description' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $event = Event::findOrFail($id);

        DB::transaction(function () use ($request, $data, $event) {
            $event->update([
                "name" => $data['name'],
                "address" => $data['address'] ?? null,
                "district_id" => $data['district_id'] ?? null,
                "description" => $data['description'],
                "lat" => $data['lat'],
                "lng" => $data['lng'],
                "status" => $data['status'],
            ]);

            // Recopilar los paths de las imágenes que se conservarán
            $keepImages = collect($request->input('images', []))
                ->where('path', '<>', '')
                ->pluck('path')
                ->all();

            // Eliminar imágenes que no están en la lista de conservación
            foreach ($event->images as $existingImage) {
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
                        $path = $file->store('images/events');
                        $event->images()->create([
                            'file_path' => $path,
                            'front_page' => $request->input("images.$index.profile"),
                        ]);
                    }elseif (!empty($pathInput)) {
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

        return response()->json(['message' => 'Actualizado con éxito'], 200);
    }

    public function show($id): Model|Collection|Builder|array|null
    {
        return Event::with(['district','images'])->findOrFail($id);
    }

    public function destroy(Event $event): JsonResponse
    {
        DB::transaction(function () use ($event) {
            // Elimina las imágenes asociadas
            foreach ($event->images as $image) {
                $image->delete();
            }
            $event->delete();
        });
        return response()->json(['message' => 'Eliminado con éxito']);
    }
}
