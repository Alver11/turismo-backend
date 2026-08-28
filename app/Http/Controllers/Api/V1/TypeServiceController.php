<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TypeService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class TypeServiceController extends Controller
{
    public function index(): JsonResponse
    {
        $query = TypeService::query();

        return DataTables::of($query)->toJson();
    }

    public function getTypeServices(): Collection|array
    {
        return TypeService::get();
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('type_services', 'name')],
        ]);
        TypeService::create(
            ['name' => $validatedData['name']]
        );

        return response()->json(['message' => 'Tipo de Servicio creado exitosamente']);
    }

    public function show($id)
    {
        return TypeService::findOrFail($id);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $attribute = TypeService::findOrFail($id);
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('type_services', 'name')->ignore($attribute->id)],
        ]);
        $attribute->name = $validatedData['name'];
        $attribute->save();

        return response()->json(['message' => 'Tipo de Servicio actualizado exitosamente']);
    }

    public function destroy($id): JsonResponse
    {
        try {
            $attribute = TypeService::findOrFail($id);
            $attribute->delete();

            return response()->json(['message' => 'Eliminado con éxito']);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Error al Eliminar el Atributo']);
        }
    }
}
