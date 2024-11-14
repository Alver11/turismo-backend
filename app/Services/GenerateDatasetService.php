<?php
namespace App\Services;

use Illuminate\Support\Facades\Storage;

class GenerateDatasetService
{
    public function generateDataset(array $touristPlaces): string
    {
        // Mensaje del sistema
        $systemMessage = "Eres un asistente turístico que responde preguntas solo sobre los lugares turísticos proporcionados. Proporcionando toda la ID del sitio o alguna informacion adicional.";

        // Estructura del dataset
        $dataset = [];

        foreach ($touristPlaces as $place) {
            // Construcción de la descripción detallada
            $details = [
                "ID: {$place['id']}",
                "Nombre: {$place['name']}",
                "Dirección: {$place['address']}",
                "Distrito: " . ($place['district']['name'] ?? 'No especificado'),
                "Descripción: " . ($place['description'] ?: 'Sin descripción disponible.'),
                "Estado: " . ($place['status'] ? 'Activo' : 'Inactivo'),
                "Creado en: {$place['created_at']}",
                "Actualizado en: {$place['updated_at']}",
                "Categorías: " . implode(', ', array_map(fn($categories) => $categories['name'], $place['categories'] ?? [])),
            ];

            $fullDetails = implode("\n", $details);

            // Preguntas adicionales para entrenamiento
            $questions = [
                [
                    'role' => 'user',
                    'content' => "¿Qué actividades hay en el distrito \"{$place['district']['name']}\"?",
                ],
                [
                    'role' => 'user',
                    'content' => "¿Cuál es la dirección del sitio \"{$place['name']}\"?",
                ],
                [
                    'role' => 'user',
                    'content' => "Describe el sitio turístico \"{$place['name']}\".",
                ],
                [
                    'role' => 'user',
                    'content' => "Que lugares tengo al aire libre en  \"{$place['district']['name']}\".",
                ],
                [
                    'role' => 'user',
                    'content' => "Que lugares tengo al aire libre.",
                ],
            ];

            // Respuestas correspondientes
            $responses = [
                [
                    'role' => 'assistant',
                    'content' => $place['id'] ?? 'No hay actividades especificadas para este lugar.',
                ],
                [
                    'role' => 'assistant',
                    'content' => $place['address'] ?? 'La dirección no está disponible.',
                ],
                [
                    'role' => 'assistant',
                    'content' => $place['description'] ?? 'No hay descripción disponible para este lugar.',
                ],
                [
                    'role' => 'assistant',
                    'content' => $place['id'] ?? 'No hay lugares para este distrito.',
                ],
                [
                    'role' => 'assistant',
                    'content' => $place['id'] ?? 'No hay lugares con esas indicaciones.',
                ]
            ];

            // Generar mensajes para cada pregunta-respuesta
            foreach ($questions as $index => $question) {
                $dataset[] = [
                    'messages' => [
                        ['role' => 'system', 'content' => $systemMessage],
                        $question,
                        $responses[$index]
                    ]
                ];
            }

            // Agregar entrada general al dataset
            $dataset[] = [
                'messages' => [
                    ['role' => 'system', 'content' => $systemMessage],
                    ['role' => 'user', 'content' => "¿Qué información tienes sobre el lugar \"{$place['name']}\"?"],
                    ['role' => 'assistant', 'content' => $fullDetails]
                ]
            ];
        }

        // Genera el archivo JSONL
        $filePath = 'datasets/dataset_chat.jsonl';
        Storage::put($filePath, collect($dataset)->map(fn($item) => json_encode($item))->implode("\n"));

        return Storage::path($filePath); // Ruta completa del archivo
    }

}
