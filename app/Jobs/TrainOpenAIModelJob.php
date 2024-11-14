<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\TouristPlace;
use App\Services\GenerateDatasetService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class TrainOpenAIModelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     * @throws Exception
     */
    public function handle(GenerateDatasetService $datasetService)
    {
        // Obtener los lugares turísticos con categoría asignada y estado activo
        $touristPlaces = TouristPlace::whereHas('categories')
            ->where('status', true)
            ->get()
            ->map(function ($place) {
                return [
                    'id' => $place->id,
                    'name' => $place->name,
                    'description' => $place->description,
                    'category' => $place->category->name,
                    'location' => [
                        'latitude' => $place->latitude,
                        'longitude' => $place->longitude,
                    ],
                    'status' => $place->status,
                ];
            })->toArray();

        // Obtener los eventos
        $events = Event::where('status', true)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'date' => $event->date,
                    'location' => [
                        'venue' => $event->venue,
                        'city' => $event->city,
                    ],
                    'status' => $event->status,
                ];
            })->toArray();

        // Combinar los datos de lugares turísticos y eventos
        $dataset = array_merge($touristPlaces, $events);

        // Generar el archivo JSONL
        $filePath = $datasetService->generateDataset($dataset);

        // Subir el archivo JSONL a OpenAI
        $uploadResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
        ])->attach('file', file_get_contents($filePath), 'dataset_chat.jsonl')->post('https://api.openai.com/v1/files', [
            'purpose' => 'fine-tune',
        ]);

        $fileId = $uploadResponse->json()['id'] ?? null;

        if (!$fileId) {
            throw new Exception('Error al subir el archivo JSONL a OpenAI.');
        }

        // Entrenar el modelo
        $trainResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            'Content-Type' => 'application/json'
        ])->post('https://api.openai.com/v1/fine-tunes', [
            'training_file' => $fileId,
            'model' => 'gpt-4o-mini-2024-07-18',
        ]);

        if (!$trainResponse->successful()) {
            throw new Exception('Error al entrenar el modelo: ' . $trainResponse->body());
        }

        // Retorna el ID del modelo entrenado
        return $trainResponse->json();
    }

}
