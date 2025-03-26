<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\EventResource;
use App\Http\Resources\TouristPlaceResource;
use App\Models\Category;
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

    // PHP
    public function store(Request $request): JsonResponse
    {
        // Decodificar los datos enviados en la llave 'data'
        $data = json_decode($request->input('data'), true);

        $validator = Validator::make($data, [
            'name' => 'required|string',
            'description' => 'required|string',
            'categories' => 'array',
            'categories.*' => 'exists:event_categories,id',
            'event_date' => 'nullable|date',
            'publication_end_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        DB::transaction(function () use ($data, $request) {
            // Crear el registro del evento
            $event = Event::create([
                "name" => $data['name'],
                "address" => $data['address'] ?? null,
                "district_id" => $data['district_id'] ?? null,
                "description" => $data['description'],
                "lat" => $data['lat'] ?? null,
                "lng" => $data['lng'] ?? null,
                "user_id" => Auth::id(),
                "event_date" => $data['event_date'] ?? null,
                "publication_end_date" => $data['publication_end_date'] ?? null,
            ]);

            // Sincronizar las categorías si están definidas y no vacías
            if (!empty($data['categories'])) {
                $event->categories()->sync($data['categories']);
            }

            // Procesar y guardar las imágenes asociadas en el índice "images"
            if ($request->has('images')) {
                foreach ($request->file('images') as $key => $file) {
                    // Procesar los datos adicionales enviados con cada imagen
                    $isProfile = filter_var($request->input("images.{$key}.profile"), FILTER_VALIDATE_BOOLEAN);
                    $imagePath = $request->input("images.{$key}.path");

                    // Guardar la imagen en almacenamiento público (carpeta 'events')
                    $filePath = $file->store('events', 'public');

                    // Crear el registro de la imagen asociada al evento
                    $event->images()->create([
                        'name' => $file->getClientOriginalName(), // Nombre original del archivo
                        'file_path' => $filePath, // Ruta del archivo en el almacenamiento
                        'front_page' => $isProfile, // Dato recibido con el índice 'profile'
                    ]);
                }
            }
        });

        return response()->json(['message' => 'Evento agregado con éxito'], 201);
    }
    public function update(Request $request, Event $event): JsonResponse
    {
        // Decodificar los datos enviados en la llave 'data'
        $data = json_decode($request->input('data'), true);

        $validator = Validator::make($data, [
            'name' => 'required|string',
            'description' => 'required|string',
            'categories' => 'array',
            'categories.*' => 'exists:event_categories,id',
            'event_date' => 'nullable|date',
            'publication_end_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        DB::transaction(function () use ($data, $request, $event) {
            // Actualizar los datos del evento
            $event->update([
                'name' => $data['name'],
                'address' => $data['address'] ?? null,
                'district_id' => $data['district_id'] ?? null,
                'description' => $data['description'],
                'lat' => $data['lat'] ?? null,
                'lng' => $data['lng'] ?? null,
                'event_date' => $data['event_date'] ?? null,
                'publication_end_date' => $data['publication_end_date'] ?? null,
            ]);

            // Sincronizar las categorías si están definidas
            if (!empty($data['categories'])) {
                $event->categories()->sync($data['categories']);
            }

            if ($request->has('images')) {
                // Obtener los `paths` de las imágenes del request
                $uploadedImagePaths = collect($request->input('images', []))
                    ->filter(function ($imageData) {
                        return !empty($imageData['path']);
                    })
                    ->pluck('path')
                    ->toArray();
                // Obtener los `paths` de las imágenes existentes en el evento
                $existingImagePaths = $event->images()->pluck('file_path')->toArray();
                // Determinar qué imágenes eliminar (ya no están en el request)
                $imagesToDelete = array_diff($existingImagePaths, $uploadedImagePaths);
                // Eliminar las imágenes que ya no están en el request
                foreach ($imagesToDelete as $filePath) {
                    // Eliminar la imagen del sistema de archivos
                    Storage::disk('public')->delete($filePath);

                    // Eliminar la imagen de la base de datos
                    $event->images()->where('file_path', $filePath)->delete();
                }

                // Procesar las imágenes enviadas en el request
                foreach ($request->input('images', []) as $key => $imageData) {
                    if (!empty($imageData['path'])) {
                        // Si la imagen ya existe, actualizamos "profile" si es necesario
                        $event->images()->updateOrCreate(
                            ['file_path' => $imageData['path']],
                            [
                                'front_page' => filter_var($imageData['profile'] ?? false, FILTER_VALIDATE_BOOLEAN),
                                'name' => basename($imageData['path']),
                            ]
                        );
                    } elseif ($request->hasFile("images.{$key}")) {
                        // Si hay un archivo nuevo subido
                        $file = $request->file("images.{$key}");

                        // Guardar la imagen en almacenamiento público (carpeta 'events')
                        $filePath = $file->store('events', 'public');

                        Log::info("Archivo de imagen cargado con éxito: {$filePath}");

                        // Crear la nueva imagen asociada al evento
                        $event->images()->create([
                            'name' => $file->getClientOriginalName(),
                            'file_path' => $filePath,
                            'front_page' => filter_var($imageData['profile'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        ]);
                    }
                }
            } else {
                Log::info('No se enviaron imágenes en el request');
            }
        });

        return response()->json(['message' => 'Evento actualizado con éxito'], 200);
    }
    public function show($id): Model|Collection|Builder|array|null
    {
        return Event::with(['district','images','categories'])->findOrFail($id);
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

    //-------------------- Funciones para la APP -----------------------
    public function getEvents(): JsonResponse
    {
        $events = Event::with([
            'categories' => function ($query) {
                $query->select('event_id', 'name');
            },
            'images' => function ($query) {
                $query->select('id', 'imageable_id', 'file_path', 'front_page'); // Selecciona los campos necesarios
            },
            'district' => function ($query) {
                $query->select('id', 'name'); // Selecciona los campos necesarios
            }
        ])->get();

        // Modificar la respuesta para que las relaciones vacías sean null
        $events->transform(function ($event) {
            $event->categories = $event->categories->isEmpty() ? null : $event->categories;
            $event->images = $event->images->isEmpty() ? null : $event->images;
            $event->district = $event->district ?: null; // `district` no es una colección, es un objeto o null
            return $event;
        });

        return response()->json($events, 200);
    }

    public function getCategories(): JsonResponse
    {
        $events = Category::with(['touristPlaces.district',
            'touristPlaces.attributes',
            'touristPlaces' => function ($query) {
                $query->where('status', true)
                    ->orderBy('id', 'desc');
            }])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(CategoryResource::collection($events));
    }

    public function getTourist(Request $request): JsonResponse
    {
        $tourists = TouristPlace::where('status', true);
        /*if(isset($request->category_di)){
            $id = $request->category_di;
            $tourists->with(['categories' => function ($query, $id)
            {
                $query->id = $id;
            }]);
        }*/
        $tourists = $tourists->get();
        return response()->json(TouristPlaceResource::collection($tourists));
    }
}
