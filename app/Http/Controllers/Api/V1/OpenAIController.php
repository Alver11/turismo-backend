<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OpenAIController extends Controller
{
    public function askQuestion(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        if (! config('services.openai.api_key') || ! config('services.openai.chat_model')) {
            return response()->json(['message' => 'El servicio de IA no está configurado.'], 503);
        }

        $systemMessage = 'Eres un asistente turístico que responde únicamente con la información proporcionada durante el entrenamiento. Si no conoces la respuesta, indícalo claramente.';
        $response = Http::baseUrl(config('services.openai.base_url'))
            ->withToken(config('services.openai.api_key'))
            ->acceptJson()
            ->timeout(30)
            ->retry(2, 250)
            ->post('chat/completions', [
                'model' => config('services.openai.chat_model'),
                'messages' => [
                    ['role' => 'system', 'content' => $systemMessage],
                    ['role' => 'user', 'content' => $validated['question']],
                ],
            ]);

        if ($response->successful()) {
            return response()->json([
                'answer' => $response->json('choices.0.message.content'),
            ]);
        }

        report(new \RuntimeException('OpenAI request failed with status '.$response->status()));

        return response()->json(['message' => 'No fue posible consultar el asistente.'], 502);
    }
}
