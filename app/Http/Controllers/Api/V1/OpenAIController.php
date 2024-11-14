<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OpenAIController extends Controller
{
    public function askQuestion(Request $request)
    {
        $question = $request->input('question');
        $systemMessage = "Eres un asistente turístico que lista los datos unicamente de las informaciones proporcionadas, si no hay los datos solo responde con otra pregunta, en caso que si haya una respuesta proporciona la informacion solo de los datos recibidos en el entrenamiento";
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
        ])->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'ft:gpt-4o-mini-2024-07-18:personal::ATL7LKvl', // Reemplaza con el ID de tu modelo ajustado
            'messages' => [
                ['role' => 'system', 'content' => $systemMessage],
                ['role' => 'user', 'content' => $question],
            ],
        ]);

        if ($response->successful()) {
            return $response->json()['choices'][0]['message']['content'];
        }

        return response()->json(['error' => 'Error al consultar el modelo.'], 500);
    }
}
