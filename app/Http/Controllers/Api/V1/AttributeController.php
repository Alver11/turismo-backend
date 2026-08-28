<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class AttributeController extends Controller
{
    public function index(): JsonResponse
    {
        $query = Attribute::query();

        return DataTables::of($query)->toJson();
    }

    public function getAttribute(): Collection|array
    {
        return Attribute::get();
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('attributes', 'name')],
        ]);
        Attribute::create(['name' => $validatedData['name'], 'type_input' => 'text']);

        return response()->json(['message' => 'Atriburo creado exitosamente']);
    }

    public function show($id)
    {
        return Attribute::findOrFail($id);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $attribute = Attribute::findOrFail($id);
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('attributes', 'name')->ignore($attribute->id)],
        ]);
        $attribute->name = $validatedData['name'];
        $attribute->save();

        return response()->json(['message' => 'Atributo actualizado exitosamente']);
    }

    public function destroy($id): JsonResponse
    {
        try {
            $attribute = Attribute::findOrFail($id);
            $attribute->delete();

            return response()->json(['message' => 'Eliminado con éxito']);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Error al Eliminar el Atributo']);
        }
    }
}
